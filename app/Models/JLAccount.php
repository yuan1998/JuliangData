<?php

namespace App\Models;

use App\Clients\JuliangClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
        'comment',
    ];

    public static $statusList = [
        'enable'  => '激活中',
        'disable' => '禁止中',
    ];

    public static $accountTypeList = [
        'beijing' => '北京账户',
        'xian'    => '西安账户',
    ];

    public static $hospitalTypeList = [
        'zx' => '整形医院',
        'kq' => '口腔医院',
    ];

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
        $token    = $this->refresh_token;
        $response = JuliangClient::refreshToken($token);

        if (Arr::exists($response, 'data')) {
            $baseData = static::baseDataParser($response['data']);
            $this->fill($baseData);
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

        if ($response['code'] == 0) {
            $data = $response['data'];
            $list = $data['list'];
            $this->saveResponseList($list);

            $pageInfo = $data['page_info'];
            if ($pageInfo['total_page'] > $page) {
                $this->getAdvertiserPlanData($start, $end, $page + 1);
            }
            return true;
        } else {
            return false;
        }
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
            'status'                   => 'enable'
        ], $state);
    }

    public static function makeAccount($data)
    {
        $state = request()->get('state');
        $state = ($state && isJson($state)) ? json_decode($state, true) : [];

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
