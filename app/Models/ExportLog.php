<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ExportLog extends Model
{
    protected $fillable = [
        'log_type',
        'name',
        'path',
        'request_data',
        'status',
    ];

    protected $casts = [
        'question_data' => 'json'
    ];

    public static $statusList = [
        'queue'   => '等待中',
        'fail'    => '导出失败',
        'ing'     => '导出中',
        'success' => '导出成功'
    ];

    public static $logTypeList = [
        'advertiser_plan_data' => '广告计划数据',
    ];

    public static $basePath = '/exports/';

    public static function checkType($data)
    {
        $logType = $data['log_type'];
        if (!Arr::exists(static::$logTypeList, $logType))
            return false;

        $now  = Carbon::now()->toDateString();
        $path = static::$basePath . $logType . '/' . $now . '/';
        $name = static::makeName($data);

        return static::create(array_merge($data, [
            'path'   => $path,
            'name'   => $name,
            'status' => 'queue',
        ]));
    }


    public static function makeAdvertiserPlanDataFileName($data)
    {
        $request_data = $data['request_data'];
        $log_type     = $data['log_type'];
        $type         = JLAccount::$accountTypeList[$request_data['account_type']];
        $dates        = $request_data['dates'];

        return $type . '_' . static::$logTypeList[$log_type] . '_' . $dates[0] . '_' . $dates[1];
    }

    public static function makeName($data)
    {
        switch ($data['log_type']) {
            case 'advertiser_plan_data':
                return static::makeAdvertiserPlanDataFileName($data);
                break;
        }
    }


}
