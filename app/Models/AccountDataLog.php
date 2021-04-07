<?php

namespace App\Models;

use App\Clients\DingDingRobotClient;
use App\Exports\AccountCostLogExport;
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
    ];

    public static $styles = 'table{background:white;border-radius:10px;overflow:hidden;display:inline-block}th{white-space:nowrap}thead tr{height:40px;background:#36304a}thead tr th{font-size:18px;color:#fff;line-height:1.2;font-weight:unset;padding:0 20px}tbody tr{font-size:15px;color:#444;line-height:1.2;font-weight:unset;height:40px}tbody tr td{padding:0 5px}tbody tr:nth-child(even){background-color:#f5f5f5}';

    public function account()
    {
        return $this->belongsTo(JLAccount::class, 'account_id', 'id');
    }

    public static function logTodayAccountData()
    {
        $today = Carbon::today()->toDateString();
        static::makeLogData($today);
    }

    public static function sendAccountToRobot($date)
    {
        $robotList = JLAccount::getAccountWithLogOfDate($date);

        foreach ($robotList as $robotName => $accounts) {

            if (!$accounts || !DingDingRobotClient::validateRobotName($robotName)) continue;

            $list = collect($accounts)->map(function ($account) {
                $account['sum'] = static::sumLogItem($account->accountLog);

                return $account;
            });

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
        $cost    = 0;
        $convert = 0;
        foreach ($list as $item) {
            $cost    += $item['sum']['cost'];
            $convert += $item['sum']['convert'];
        }
        $convert_cost = $convert ? floor($cost / $convert) : 0;

        return $list
            ->map(function ($item) {
                $cost    = $item['sum']['cost'];
                $convert = $item['sum']['convert'];
                if ($cost == 0 && $convert == 0)
                    return '';

                $name = $item['comment'] ?? $item['advertiser_name'];
                if ($item['limit_cost'] && $cost > $item['limit_cost']) {
                    $cost .= "(已超额)";
                }


                $convert_cost = $convert ? floor($cost / $convert) : 0;
                return "{$name}  :   消费 {$cost} , 表单数 {$convert} " . ($convert_cost ? ", 表单成本 {$convert_cost}" : "");
            })
            ->filter(function ($item) {
                return !!$item;
            })
            ->push("合计  :  消费 {$cost} , 表单数 {$convert}" . ($convert_cost ? ", 表单成本 {$convert_cost}" : ""))
            ->join("\n");
    }

    public static function makeLogData($date)
    {
        $data = JLAdvertiserPlanData::query()
            ->select([
                'id',
                'account_id',
                'show',
                'click',
                'attribution_convert',
                'cost',
                'cost_off',
                'stat_datetime',
            ])
            ->with(['accountData'])
            ->whereDate('stat_datetime', $date)
            ->get()
            ->groupBy('account_id');

        $now = Carbon::now()->toDateTimeString();
        foreach ($data as $accountId => $planData) {

            $model = [
                'show'        => $planData->sum('show'),
                'click'       => $planData->sum('click'),
                'cost'        => $planData->sum('cost'),
                'convert'     => $planData->sum('attribution_convert'),
                'rebate_cost' => $planData->sum('cost_off'),
                'account_id'  => $accountId,
                'last_date'   => $now,
                'log_date'    => $date,
            ];

            static::query()
                ->where('account_id', $accountId)
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
}
