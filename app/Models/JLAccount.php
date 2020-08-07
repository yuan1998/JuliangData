<?php

namespace App\Models;

use App\Clients\JuliangClient;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * @method static JLAccount updateOrCreate(array $array, array $item)
 * @method static JLAccount|null find($id)
 * @property mixed|TokenList token
 */
class JLAccount extends Model
{
    protected $fillable = [
        'advertiser_id',
        'advertiser_name',
        'account_type',
        'hospital_type',
        'hospital_id',
        'comment',
        'app_id',
        'token_id',
        'advertiser_role',
    ];

    public static $statusList = [
        '1' => '授权正常',
        '0' => '授权错误,请重新授权',
    ];

    public static $accountTypeList = [
        'beijing' => '北京账户',
        'xian'    => '西安账户',
    ];

    public static $hospitalTypeList = [
        'zx' => '整形医院',
        'kq' => '口腔医院',
    ];

    /**
     * Fix方法: 批量设置广告主的 APP_ID
     * @param $id
     */
    public static function setAppId($id)
    {
        static::query()
            ->update([
                'app_id' => $id,
            ]);
    }

    public function scopeAdminUserHospital($query)
    {
        $user = Admin::user();
        if ($user && $user->hospital()->exists()) {
            $hospitalId = $user->hospital()->pluck('id');
            return $query->whereIn('hospital_id', $hospitalId);
        }

        return $query;
    }

    public function token()
    {
        return $this->belongsTo(TokenList::class, 'token_id', 'id');
    }

    /**
     * 广告主 关联 的 APP管理
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function app()
    {
        return $this->belongsTo(JLApp::class, 'app_id', 'id');
    }

    /**
     * 获取 当前广告主 关联的 APP 配置
     * @return array|bool
     */
    public function getAppConfigAttribute()
    {
        $app = $this->app;
        if (!$app) return false;

        return [
            'app_id'     => $app->app_id,
            'app_secret' => $app->app_secret,
        ];
    }

    /**
     * 关联 广告主 的 广告计划数据
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function advertiserPlanData()
    {
        return $this->hasMany(JLAdvertiserPlanData::class, 'advertiser_id', 'advertiser_id');
    }

    /**
     * 查看 广告主 账户 Token 是否过期.
     * 如果过期,尝试刷新
     * @param bool $do
     */
    public function checkToken($do = false)
    {
        $token = $this->token;

        if ($token) {
            if ($do || TokenList::tokenIsExpires($token)) {
                $token->refreshToken($this->appConfig);
            }
        }

    }

    public function getMajordomoAccount()
    {
        $token  = $this->token;
        $result = JuliangClient::getMajordomoAccount($this->advertiser_id, $token->access_token);

        if ($result['code'] === 0) {
            return $result['data']['list'];
        }
        return false;
    }

    /**
     * 获取数据方法 : 获取当前账户Model 的 飞鱼线索.
     * @param string $start    开始时间
     * @param string $end      结束时间
     * @param int    $page     页数
     * @param int    $pageSize 每页数量
     * @return bool
     * @throws \Exception
     */
    public function getFeiyuClue($start, $end, $page = 1, $pageSize = 100)
    {
        $this->checkToken();

        $str = collect([$this->advertiser_id])->map(function ($item) {
            return (string)$item;
        });
//        dd($str);

        // 例:
        $advertiser_ids = '["1667928143039496"]';
        $start          = '2020-06-25';
        $end            = '2020-06-25';
        $pageSize       = 100;
        $page           = 1;

        $response = JuliangClient::getFeiyuClueData([
            'advertiser_ids' => $advertiser_ids,
            'start_time'     => $start,
            'end_time'       => $end,
            'page_size'      => $pageSize,
            'page'           => $page
        ], $this->access_token);
        dd($response);

        return $this->mapToResponsePageData('feiyuClue', $response, $start, $end, $page);
    }


    /**
     * 处理方法 : 集中处理 返回的带页数的数据 .
     *  目前有  : [  飞鱼线索  , 广告计划数据 ]
     * @param $type
     * @param $response
     * @param $start
     * @param $end
     * @param $page
     * @return bool
     * @throws \Exception
     */
    public function mapToResponsePageData($type, $response, $start, $end, $page)
    {
        $code = Arr::get($response, 'code', null);
        if ($code === null) return false;


        $ucType = ucfirst($type);
        switch ($code) {
            case 0 :
                $data = $response['data'];
                $list = $data['list'];
                if (method_exists($this, 'saveList' . $ucType)) {
                    $this->{'saveList' . $ucType}($list);
                } else {
                    throw new \Exception("找不到 {$ucType} 的保存方法,请重新确认.");
                }

                $pageInfo = $data['page_info'];
                if ($pageInfo['total_page'] > $page) {
                    if (method_exists($this, 'get' . $ucType))
                        $this->{'get' . $ucType}($start, $end, $page + 1);
                }
                return $response;
            case 40100:
            case 40101:
            case 40102:
            case 40103:
            case 40104:
            case 40105:
            case 40106:
            case 40107:
            case 40108:
            case 40109:
                $this->fill(['status' => 'disable']);
                $this->update();
                return $response;
            default:
                Log::info('无法处理的CODE码', ['code' => $code]);
                return $response;
        }
    }

    /**
     * save方法: 保存请求回来的 飞鱼线索
     * @param $list array 飞鱼线索数组
     */
    public function saveListFieyuClue($list)
    {
        dd($list);
    }

    /**
     * 广告主授权成功 - 创建广告主账户
     * @param $data  array 授权返回的数组
     * @param $state array 附加数据
     */
    public static function makeAccount($data, $state)
    {
        $token = TokenList::makeToken($data);
        $app   = JLApp::find($state['app_id']);

        $info = JuliangClient::getAccountAuth($token['access_token'], $app['app_id'], $app['app_secret']);

        if ($info['code'] === 0) {
            foreach ($info['data']['list'] as $item) {
                $advertiser = array_merge($item, $state, [
                    'token_id' => $token->id,
                ]);

                static::updateOrCreate([
                    'advertiser_id' => $advertiser['advertiser_id'],
                ], $advertiser);
            }
        }
    }


    /**
     *  拉取昨天的 广告计划数据
     */
    public static function pullYesterdayAdvertiserPlanData()
    {
        $dateString = Carbon::yesterday()->toDateString();
        $accounts   = static::query()
            ->with('token')
            ->whereHas('token', function ($query) {
                $query->where('status', 1);
            })->get();

        $accountList = static::parserAccountsToQuery($accounts);

        foreach ($accountList as $account) JLAdvertiserPlanData::getOneDayOfAccount($account, $dateString);
    }

    public static function parserAccountsToQuery($accounts)
    {
        $accountList = [];
        foreach ($accounts as $account) {
            $token = $account->token;
            if (!$token || !$token->checkToken($account->appConfig)) continue;

            switch ($account['advertiser_role']) {
                case 1 :
                    $accountList[] = [
                        'advertiser_id'   => $account->advertiser_id,
                        'hospital_id'     => $account->hospital_id,
                        'id'              => $account->id,
                        'advertiser_name' => $account->advertiser_name,
                        'access_token'    => $token->access_token,
                        'token'           => $token,
                    ];
                    break;
                case 2:
                    $majordomoChild = $account->getMajordomoAccount($account->advertiser_id, $token->access_token);

                    if ($majordomoChild) {
                        foreach ($majordomoChild as $item) {
                            $accountList[] = [
                                'advertiser_id'   => $item['advertiser_id'],
                                'hospital_id'     => $account->hospital_id,
                                'id'              => $account->id,
                                'advertiser_name' => $account->advertiser_name,
                                'access_token'    => $token->access_token,
                                'token'           => $token,
                            ];
                        }
                    }
                    break;
            }

        }

        return $accountList;
    }

    public static function getHospitalAccount($id, $toList = false)
    {
        $accounts = JLAccount::query()
            ->with('token')
            ->whereHas('token', function ($query) {
                $query->where('status', 1);
            })->where('hospital_id', $id)->get();

        return $toList ? static::parserAccountsToQuery($accounts) : $accounts;

    }
}
