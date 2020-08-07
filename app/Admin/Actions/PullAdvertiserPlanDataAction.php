<?php

namespace App\Admin\Actions;

use App\Models\HospitalType;
use App\Models\JLAccount;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
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
        $user = Admin::user();

        $hospitalTypeList =
            ($user && $user->hospital()->exists())
                ? $user->hospital()->pluck('hospital_name', 'id')
                : HospitalType::all()->pluck('hospital_name', 'id')->toArray();
        return view('juliang.action.pullAdvertiserPlanData', [
            'hospitalTypeList' => $hospitalTypeList,
        ]);
    }

}
