<?php

namespace App\Models;

use App\Clients\DingDingRobotClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
                'id', 'account_id', 'show',
                'click',
                'attribution_convert',
                'cost',
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
