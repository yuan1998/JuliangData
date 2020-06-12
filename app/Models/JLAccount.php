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

    public static function setAppId($id)
    {
        static::query()
            ->update([
                'app_id' => $id,
            ]);
    }

    public function app()
    {
        return $this->belongsTo(JLApp::class, 'app_id', 'id');
    }

    public function getAppConfigAttribute()
    {
        $app = $this->app;
        if (!$app) return false;

        return [
            'app_id'     => $app->app_id,
            'app_secret' => $app->app_secret,
        ];
    }

    public function advertiserPlanData()
    {
        return $this->hasMany(JLAdvertiserPlanData::class, 'advertiser_id', 'advertiser_id');
    }

    public function tokenIsExpires()
    {
        $time = Carbon::parse($this->expires_in)->addHours(-2);
        $now  = Carbon::now();

        return $now->gte($time);
    }

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

    public function checkToken()
    {
        $test = $this->tokenIsExpires();
        if ($test) {
            $this->refreshToken();
        }
    }

    public function responseCode($response, $start, $end, $page)
    {
        $code = Arr::get($response, 'code', null);
        if ($code === null) return false;

        switch ($code) {
            case 0 :
                $data = $response['data'];
                $list = $data['list'];
                $this->saveResponseList($list);

                $pageInfo = $data['page_info'];
                if ($pageInfo['total_page'] > $page) {
                    $this->getAdvertiserPlanData($start, $end, $page + 1);
                }
                return $response;
            case 40105:
                $this->fill(['status' => 'disable']);
                $this->update();
                return $response;
            default:
                Log::info('无法处理的CODE码', ['code' => $code]);
                return $response;
        }

    }

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

        return $this->responseCode($response, $start, $end, $page);
    }


    /**
     * @param $list array
     */
    public function saveResponseList($list)
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


    public static function yesterdayPull()
    {
        $dateString = Carbon::yesterday()->toDateString();
        $accounts   = static::query()->where('status', 'enable')->get();
        foreach ($accounts as $account) {
            $account->getAdvertiserPlanData($dateString, $dateString);
        }
    }

}
