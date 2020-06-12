<?php

namespace App\Http\Controllers\Api;

use App\Clients\JuliangClient;
use App\Exports\JLAdvertiserPlanDataExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\JLAdvertiserPlanDataRequest;
use App\Models\HospitalType;
use App\Models\JLAdvertiserPlanData;
use App\Models\JLAccount;
use App\Models\JLApp;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

class JuliangController extends Controller
{

    /*
https://ad.oceanengine.com/openapi/audit/oauth.html?app_id=1668736156326939&state={"hospital_type":"zx","account_type":"xian"}&scope=%5B4%5D&redirect_uri=http%3A%2F%2Fjuliang.xahmyk.cn%2Fapi%2Fv1%2Fjuliang%2Fauth_code%2F&rid=pjud7dsb11p
     */

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

        $state = json_decode(request()->get('state'), true);

        if (is_array($state) && (!Arr::exists($state, 'app_id') || !Arr::exists($state, 'hospital_id')))
            return view('juliang.auth', ['msg' => '授权错误.确实正确的APP ID,请检查您的授权链接']);

        if (!$appModel = JLApp::find($state['app_id'])) {
            return view('juliang.auth', ['msg' => '授权错误.不存在的APP ID,请检查您的授权链接']);
        }
        
        if (!HospitalType::query()->where('id', $state['hospital_id'])->exists())
            return view('juliang.auth', ['msg' => '授权错误.不存在的Hospital ID,请检查您的授权链接']);


        $json = JuliangClient::getAccessToken($authCode, $appModel);

        if ($json['code'] === 0) {
            JLAccount::makeAccount($json['data'], $state);
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
            if ($result === false) {
                return response()->json([
                    'code'    => 1001,
                    'message' => '发生错误,请联系管理员'
                ]);
            } else {
                return response()->json($result);
            }
        } else {
            return response()->json([
                'code'    => 1000,
                'message' => '错误的ID.'
            ]);
        }
    }

    public function pullAdvertiserPlanData(JLAdvertiserPlanDataRequest $request)
    {
        $dates      = $request->get('dates');
        $hospitalId = $request->get('hospital_id');

        $query = JLAccount::query()
            ->where('status', 'enable')
            ->where('hospital_id', $hospitalId);

        $accountData = $query->get();

        $successCount = 0;
        $errorCount   = 0;
        $logs         = [];
        foreach ($accountData as $account) {
            static::dateRangeForEach($dates, function ($str) use ($account, &$successCount, &$errorCount, &$logs) {
                $dateString = $str->toDateString();
                $result     = $account->getAdvertiserPlanData($dateString, $dateString);

                if (Arr::get($result, 'code') == 0) {
                    $successCount++;
                } else {
                    $errorCount++;
                    array_push($logs, $result);
                }
            });
        }

        return response()->json([
            'code'    => 0,
            'message' => "成功拉取账户{$successCount}个,失败账户{$errorCount}个",
            'logs'    => $logs
        ]);
    }

    public function exportAdvertiserPlanData(JLAdvertiserPlanDataRequest $request)
    {
        $data = $request->all(['dates', 'hospital_id']);

        $export = new JLAdvertiserPlanDataExport($data);
        return Excel::download($export, $export->makeName());
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
