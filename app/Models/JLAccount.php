<?php

namespace App\Models;

use App\Clients\JuliangClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * @method static JLAccount updateOrCreate(array $array, array $item)
 * @method static JLAccount|null find($id)
 */
class JLAccount extends Model
{
    protected $fillable = [
        'refresh_token_expires_in',
        'expires_in',
        'access_token',
        'refresh_token',
        'advertiser_id',
        'advertiser_name',
        'status',
        'account_type',
        'hospital_type',
        'hospital_id',
        'comment',
        'app_id',
    ];

    public static $statusList = [
        'enable'  => '激活中',
        'disable' => '禁止中,请重新授权',
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
     * 判断方法 : 判断 广告主 Token是否过期
     * @return bool
     */
    public function tokenIsExpires()
    {
        $time = Carbon::parse($this->expires_in)->addHours(-2);
        $now  = Carbon::now();

        return $now->gte($time);
    }

    /**
     * 刷新 广告主 账户 Token
     */
    public function refreshToken()
    {
        $response = JuliangClient::refreshToken($this);

        if ($response && $response['code'] == 0) {
            $data     = $response['data'];
            $baseData = static::baseDataParser($data);
            $this->fill(array_merge($data, $baseData));
            $this->save();
        } else {
            $this->fill(['status' => 'disable']);
            $this->save();
        }
    }

    /**
     * 查看 广告主 账户 Token 是否过期.
     * 如果过期,尝试刷新
     */
    public function checkToken()
    {
        $test = $this->tokenIsExpires();
        if ($test) {
            $this->refreshToken();
        }
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
     * 获取当前账户Model 的 广告计划数据
     * @param string $start    开始时间
     * @param string $end      结束时间
     * @param int    $page     页数
     * @param int    $pageSize 每页数据
     * @return bool
     * @throws \Exception
     */
    public function getAdvertiserPlanData($start, $end, $page = 1, $pageSize = 1000)
    {
        $this->checkToken();

        $response = JuliangClient::getAdvertiserPlanData([
            'advertiser_id' => $this->advertiser_id,
            'start_date'    => $start,
            'end_date'      => $end,
            'page_size'     => $pageSize,
            'page'          => $page,
            'group_by'      => '["STAT_GROUP_BY_FIELD_ID","STAT_GROUP_BY_FIELD_STAT_TIME"]'
        ], $this->access_token);

        return $this->mapToResponsePageData('advertiserPlanData', $response, $start, $end, $page);
    }


    /**
     * 保存 广告计划数据 列表,
     * @param $list array 广告计划数据 数组
     */
    public function saveListAdvertiserPlanData($list)
    {
        $advertiser_id = $this->advertiser_id;

        foreach ($list as $item) {
            $data = array_merge($item, ['advertiser_id' => $advertiser_id]);
            $adId = $data['ad_id'];
            $date = $data['stat_datetime'];

            JLAdvertiserPlanData::updateOrCreate([
                'ad_id'         => $adId,
                'stat_datetime' => $date,
            ], $data);
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
     * 解析 获取AccessToken 返回数据. 解析过期时间等
     * @param array $data  response数据
     * @param array $state 附加数据
     * @return array
     */
    public static function baseDataParser($data, $state = [])
    {
        $expires_in               = Carbon::now()->addSeconds($data['expires_in']);
        $refresh_token_expires_in = Carbon::now()->addSeconds($data['refresh_token_expires_in']);
        return array_merge([
            'expires_in'               => $expires_in,
            'refresh_token_expires_in' => $refresh_token_expires_in,
            'status'                   => 'enable',
        ], $state);
    }


    /**
     * 广告主授权成功 - 创建广告主账户
     * @param $data  array 授权返回的数组
     * @param $state array 附加数据
     */
    public static function makeAccount($data, $state)
    {
        foreach ($data['advertiser_ids'] as $advertiser_id) {
            $baseData = static::baseDataParser($data, $state);

            $item = array_merge($data, $baseData, [
                'advertiser_id' => $advertiser_id,
            ]);

            static::updateOrCreate(['advertiser_id' => $advertiser_id], $item);
        }


    }


    /**
     *  拉取昨天的 广告计划数据
     */
    public static function pullYesterdayAdvertiserPlanData()
    {
        $dateString = Carbon::yesterday()->toDateString();
        $accounts   = static::query()->where('status', 'enable')->get();
        foreach ($accounts as $account) {
            $account->getAdvertiserPlanData($dateString, $dateString);
        }
    }

}
