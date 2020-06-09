<?php

namespace App\Admin\Actions;

use App\Models\JLAccount;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PullAdvertiserPlanDataAction extends Action
{
    public $name = '拉取数据';

    protected $selector = '.excel-upload';

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function render()
    {
        $data = JLAccount::query()->select(['account_type', 'advertiser_id', 'advertiser_name'])->get();


        return view('juliang.action.pullAdvertiserPlanData', [
            'data'             => $data->toArray(),
            'accountTypeList'  => JLAccount::$accountTypeList,
            'hospitalTypeList' => JLAccount::$hospitalTypeList,
        ]);
    }

}
