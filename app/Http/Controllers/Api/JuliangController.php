<?php

namespace App\Http\Controllers\Api;

use App\Clients\JuliangClient;
use App\Exports\JLAdvertiserPlanDataExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\JLAdvertiserPlanDataRequest;
use App\Jobs\pullAccountDataOfHospitalId;
use App\Models\AccountDataLog;
use App\Models\HospitalType;
use App\Models\JLAdvertiserPlanData;
use App\Models\JLAccount;
use App\Models\JLApp;                             
use Carbon\Carbon;
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

    public function juliangAuth(Request $request)
    {
        $authCode = $request->get('auth_code', null);

        if (!$authCode)
            return view('juliang.auth', ['msg' => '授权错误.请正确操作']);

        $state = json_decode(request()->get('state'), true);
        var_dump($state);

        if (is_array($state) && (!Arr::exists($state, 'app_id') || !Arr::exists($state, 'hospital_id')))
            return view('juliang.auth', ['msg' => '授权错误.确实正确的APP ID,请检查您的授权链接']);

        if (!$appModel = JLApp::find($state['app_id'])) {
            return view('juliang.auth', ['msg' => '授权错误.不存在的APP ID,请检查您的授权链接']);
        }

        if (!HospitalType::query()->where('id', $state['hospital_id'])->exists())
            return view('juliang.auth', ['msg' => '授权错误.不存在的Hospital ID,请检查您的授权链接']);


        $json = JuliangClient::getAccessToken($authCode, $appModel);
        var_dump($json);

        if ($json['code'] === 0) {
            JLAccount::makeAccount($json['data'], $state);
            return view('juliang.auth', ['msg' => '授权成功!']);
        } else {
            return view('juliang.auth', ['msg' => $json['message']]);
        }
    }

    public function pullAdvertiserPlanData(JLAdvertiserPlanDataRequest $request)
    {
        $dates      = $request->get('dates');
        $hospitalId = $request->get('hospital_id');
        $data = JLAdvertiserPlanData::allAccountGetData($hospitalId, $dates);

        return response()->json([
            'code' => 0,
            'logs' => $data,
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
    }

    public function fieyuClueTest(Request $request)
    {
        JuliangClient::testGetFeiyuClueData();
        $account = JLAccount::query()->where('hospital_id', 2)
            ->first();

        $result = $account->getFeiyuClue('2020-06-24', '2020-06-24', 1);
        dd($result);
    }

    public function test()
    {
//        $data = JLAccount::pullTodayAdvertiserPlanData();

        $dateString = Carbon::today()->toDateString();

        AccountDataLog::doToday();
        dd(123);
        AccountDataLog::sendAccountToRobot($dateString, 3);

//        AccountDataLog::makeLogData($dateString);
        dd(123);
        $accounts = JLAccount::query()
            ->with('token')
            ->has('token')
            ->where('id', 225)
//            ->limit(1)
//            ->offset(1)
            ->get();

//        dd($accounts);
        $accountList = JLAccount::parserAccountsToQuery($accounts);

        dd($accountList);

        foreach ($accountList as $account)
            var_dump([
                'name'   => $account['advertiser_name'],
                'result' => JLAdvertiserPlanData::getOneDayOfAccount($account, $dateString)
            ]);
        dd(123);
    }

}
