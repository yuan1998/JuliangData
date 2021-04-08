<?php

namespace App\Models;

use App\Clients\DingDingRobotClient;
use App\Exports\AccountCostLogExport;
use App\Jobs\pullAccountDataOfHospitalId;
use Barryvdh\Snappy\Facades\SnappyImage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AccountDataLog extends Model
{

    protected $fillable = [
        'account_id',
        'show',
        'click',
        'convert',
        'cost',
        'rebate_cost',
        'log_date',
        'last_date',
        'advertiser_id',
        'advertiser_name',
        'hospital_id',
    ];

    public static $styles = 'table{background:white;border-radius:10px;overflow:hidden;display:inline-block}th{white-space:nowrap}thead tr{height:40px;background:#36304a}thead tr th{font-size:18px;color:#fff;line-height:1.2;font-weight:unset;padding:0 20px}tbody tr{font-size:15px;color:#444;line-height:1.2;font-weight:unset;height:40px}tbody tr td{padding:0 5px}tbody tr:nth-child(even){background-color:#f5f5f5}';

    public function account()
    {
        return $this->belongsTo(JLAccount::class, 'account_id', 'id');
    }

    public function commentName()
    {
        return $this->hasOne(AdvertiserNameList::class, 'advertiser_id', 'advertiser_id');
    }

    public static function logTodayAccountData()
    {
        $today = Carbon::today()->toDateString();
        static::makeLogData($today);
    }


    public static function sendAccountToRobot($date, $hospitalId = null)
    {
        $robotList = JLAccount::getAccountWithLogOfDate($date, $hospitalId);

        foreach ($robotList as $robotName => $accounts) {

            if (!$accounts || !DingDingRobotClient::validateRobotName($robotName)) continue;

            $list = [];
            foreach ($accounts as $account) {
//                dd('ff10' ?? data_get($account, '0.advertiser_name'));

                $name = data_get($account, '0.comment_name.comment') ?? data_get($account, '0.advertiser_name');
                $val  = collect($account);

                $list[$name] = [
                    'sum' => [
                        'show'        => $val->sum('show'),
                        'click'       => $val->sum('click'),
                        'cost'        => $val->sum('cost'),
                        'convert'     => $val->sum('convert'),
                        'rebate_cost' => $val->sum('rebate_cost'),
                    ]
                ];
            }


            $message = static::parserToMessage($list);

            $robot = new DingDingRobotClient($robotName);
            $robot->sendText($message);
        }
    }

    public static function listToHtmlTable($list, $header = [])
    {
        $tHead       = "";
        $bodyContent = collect($list)->map(function ($item) {
            $str = collect($item)->map(function ($value, $key) {
                if ($key === 'limit') return '';
                return "<td>{$value}</td>";
            })->join('');

            return "<tr>{$str}</tr>";
        })->join('');
        $tBody       = "<tbody>{$bodyContent}</tbody>";
        if ($header) {
            $headerContent = collect($header)->map(function ($value) {
                return "<th >{$value}</th>";
            })->join('');
            $tHead         = "<thead><tr>{$headerContent}</tr></thead>";
        }

        return "<table cellspacing='0' cellpadding='0'>{$tHead}{$tBody}</table>";
    }

    public static function parserToArray($list)
    {
        return $list
            ->filter(function ($item) {
                $cost    = $item['sum']['cost'];
                $convert = $item['sum']['convert'];

                return !$cost == 0 && $convert == 0;
            })
            ->map(function ($item) {
                $cost    = $item['sum']['cost'];
                $convert = $item['sum']['convert'];
                if ($cost == 0 && $convert == 0)
                    return '';

                $name = $item['comment'] ?? $item['advertiser_name'];

                $convert_cost = $convert ? floor($cost / $convert) : 0;
                return [
                    'name'         => $name,
                    'cost'         => $cost,
                    'convert'      => $convert,
                    'convert_cost' => $convert_cost,
                    'limit'        => $item['limit_cost'] && $cost > $item['limit_cost'],
                ];
            });
    }


    public static function parserToMessage($list)
    {
        $result       = [];
        $totalCost    = 0;
        $totalConvert = 0;
        foreach ($list as $name => $item) {
            $cost    = data_get($item, 'sum.cost', 0);
            $convert = data_get($item, 'sum.convert', 0);
            if ($cost == 0 && $convert == 0)
                continue;

            $totalCost    += $cost;
            $totalConvert += $convert;
            $convertCost  = $convert ? floor($cost / $convert) : 0;

            array_push($result, "{$name}  :   消费 {$cost} , 表单数 {$convert} " . ($convertCost ? ", 表单成本 {$convertCost}" : ""));
        }
        $totalConvertCost = $totalConvert ? floor($totalCost / $totalConvert) : 0;
        array_push($result, "合计  :  消费 {$totalCost} , 表单数 {$totalConvert}" . ($totalConvertCost ? ", 表单成本 {$totalConvertCost}" : ""));
        return implode("\n", $result);
    }

    public static function makeLogData($date, $queryCall = null)
    {
        $query = JLAdvertiserPlanData::query()
            ->select([
                'id',
                'advertiser_id',
                'advertiser_name',
                'account_id',
                'hospital_id',
                'show',
                'click',
                'attribution_convert',
                'cost',
                'rebate_cost',
                'stat_datetime',
            ])
            ->whereDate('stat_datetime', $date);

        if (is_callable($queryCall)) {
            $queryCall($query);
        }

        $data = $query->get()
            ->groupBy('advertiser_name');
//        dd($data->toArray());

        $now = Carbon::now()->toDateTimeString();
        foreach ($data as $accountId => $planData) {


            $advertiserId = data_get($planData, '0.advertiser_id');
            $model        = [
                'show'            => $planData->sum('show'),
                'click'           => $planData->sum('click'),
                'cost'            => $planData->sum('cost'),
                'convert'         => $planData->sum('attribution_convert'),
                'rebate_cost'     => $planData->sum('rebate_cost'),
                'hospital_id'     => data_get($planData, '0.hospital_id'),
                'advertiser_id'   => $advertiserId,
                'advertiser_name' => data_get($planData, '0.advertiser_name'),
                'account_id'      => data_get($planData, '0.account_id'),
                'last_date'       => $now,
                'log_date'        => $date,
            ];

            static::query()
                ->where('advertiser_id', $advertiserId)
                ->whereDate('log_date', $date)
                ->delete();
            static::create($model);
        }
    }

    public static function sumLogItem($list)
    {
        $result = [
            'show'        => 0,
            'click'       => 0,
            'cost'        => 0,
            'convert'     => 0,
            'rebate_cost' => 0,
        ];
        foreach ($list as $item) {
            $result['show']        += $item['show'];
            $result['click']       += $item['click'];
            $result['cost']        += $item['cost'];
            $result['convert']     += $item['convert'];
            $result['rebate_cost'] += $item['rebate_cost'];
        }

        return $result;
    }

    public static function test()
    {
        $dateString = Carbon::today()->toDateString();

        AccountDataLog::sendAccountToRobot($dateString);

    }

    public static function doToday()
    {
        $date  = Carbon::today()->toDateString();
        $types = HospitalType::query()
            ->select([
                'id',
                'robot'
            ])
            ->get();
        foreach ($types as $type) {
            pullAccountDataOfHospitalId::dispatch($type['id'], $date, !!$type['robot'])
                ->onQueue('test');
        }
    }
}
