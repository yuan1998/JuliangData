<?php

namespace App\Http\Controllers\Api;

use App\Clients\JuliangClient;
use App\Http\Controllers\Controller;
use App\Models\JLAdvertiserPlanData;
use App\Models\JLAccount;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class JuliangController extends Controller
{

    /**
     * 迭代 日期范围, 在回调中传入当前日期
     * @param array   $dates    日期范围
     * @param Closure $callBack 回调
     */
    public static function dateRangeForEach($dates, Closure $callBack)
    {
        $date = CarbonPeriod::create($dates[0], $dates[1]);

        foreach ($date as $item) {
            $callBack($item);
        }
    }

    public function juliangAuth(Request $request)
    {
        $authCode = $request->get('auth_code', null);

        if (!$authCode)
            return view('juliang.auth', ['msg' => '授权错误.请正确操作']);

        $json = JuliangClient::getAccessToken($authCode);

        if ($json['code'] === 0) {
            JLAccount::makeAccount($json['data']);
            return view('juliang.auth', ['msg' => '授权成功!']);
        } else {
            return view('juliang.auth', ['msg' => $json['message']]);
        }
    }

    public function advertiserPlanData(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $id        = $request->get('id');

        $model = JLAccount::find($id);

        if ($model) {
            $result = $model->getAdvertiserPlanData($startDate, $endDate);
            if ($result)
                return response()->json([
                    'code'    => 0,
                    'message' => '操作成功'
                ]);
            else return response()->json([
                'code'    => 1001,
                'message' => '发生错误,请联系管理员'
            ]);

        } else {
            return response()->json([
                'code'    => 1000,
                'message' => '错误的ID.'
            ]);
        }
    }

    public function pullAdvertiserPlanData(Request $request)
    {
        $dates = $request->get('dates');
        $type  = $request->get('account_type');

        $accountData = JLAccount::query()->where('account_type', $type)->get();

        foreach ($accountData as $account) {
            static::dateRangeForEach($dates, function ($str) use ($account) {
                $string = $str->toDateString();
                $account->getAdvertiserPlanData($string, $string);
            });
        }

        return response()->json([
            'code'    => 0,
            'message' => '成功',
        ]);
    }


    public function accountInfo(Request $request)
    {
//        $test = JuliangClient::getAccountInfo(static::$testJson['data']['access_token']);

        $result = [];
        foreach (JLAdvertiserPlanData::$test as $key => $value) {
            if (!Arr::exists(JLAdvertiserPlanData::$fields, $key)) array_push($result, $key);
        }
        dd($result);


    }

}
