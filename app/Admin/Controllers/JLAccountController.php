<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Account\BatchReferershToken;
use App\Admin\Actions\Account\PullData;
use App\Admin\Actions\JLAccount\RefreshToken;
use App\Admin\Models\Administrator;
use App\Models\HospitalType;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use \App\Models\JLAccount;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class JLAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '巨量广告主账户';

    public function accountSumIndex(Content $content)
    {
        initVue();

        $accounts = JLAccount::query()
            ->adminUserHospital()
            ->get();
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body("<page-account-plan-sum :accounts='{$accounts}'></page-account-plan-sum>");
    }

    public function accountSumList()
    {
        $accountId = request()->get('id');
        $dates     = request()->get('dates');
        $pageSize  = request()->get('page_size', 20);

        $data = JLAccount::query()
            ->select([
                'advertiser_name',
                'comment',
                'id',
            ])
            ->with([
                'accountLog' => function ($query) use ($dates) {
                    $query->select([
                        'log_date',
                    ]);
//                        ->whereBetween('log_date', [
//                            Carbon::parse($dates[0])->toDateString(),
//                            Carbon::parse($dates[1])->toDateString(),
//                        ]);
                }
            ])
            ->whereIn('id', $accountId)
            ->adminUserHospital()
            ->paginate($pageSize);

        dd($accountId, $data->toArray());
        return $data;

        $result         = $data->toArray();
        $result['data'] = $data->map(function ($account) {
            $adPlanData                     = $account['adPlanData'];
            $account['show']                = $adPlanData->sum('show');
            $account['click']               = $adPlanData->sum('click');
            $account['cost']                = round($adPlanData->sum('cost'), 3);
            $account['cost_off']            = round($adPlanData->sum('cost_off'), 3);
            $account['attribution_convert'] = $adPlanData->sum('attribution_convert');

            $account['ctr']           = $account['show'] ? round($account['click'] / $account['show'] * 100, 3) . '%' : 0;
            $account['avg_show_cost'] = $account['show'] ? round($account['cost'] / $account['show'] * 1000, 3) . '元' : 0;


            $account['avg_click_cost']           = $account['click'] ? round($account['cost'] / $account['click'], 3) . '元' : 0;
            $account['attribution_convert_cost'] = $account['attribution_convert'] ? round($account['cost'] / $account['attribution_convert'], 3) . '元' : 0;
            $account['comment_name']             = $account->comment_name;

            unset($account['adPlanData']);

            return $account;
        });

        return $data;
    }

    protected function accountSumGrid()
    {
        $grid  = new Grid(new JLAccount());
        $model = $grid->model();
        $model->with(['adPlanData'])->adminUserHospital();

        $grid->disableCreateButton();
        $grid->disableExport();

        if (!request()->get('id')) {
            $model->whereIn('id', []);
        }

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $accounts = JLAccount::query()
                    ->adminUserHospital()
                    ->get()
                    ->pluck('comment_name', 'id');

                $filter->in('id', '账户列表')->multipleSelect($accounts);
            });
        });


        $grid->fixColumns(1);
        $options = HospitalType::all()->pluck('hospital_name', 'id')->toArray();

        $grid->column('hospital_id', __('Hospital name'))->display(function ($val) use ($options) {
            if (!$val) return '未设置';
            return Arr::get($options, $val, '未设置');
        });
        $grid->column('comment_name', __('Advertiser name'))->display(function () {
            return $this->comment_name;
        });

        $grid->column('show', "展现")->display(function () {
            return $this->adPlanData->sum('show');
        });
        $grid->column('click', "点击量")->display(function () {
            return $this->adPlanData->sum('click');
        });
        $grid->column('cost', "消费(虚)")->display(function () {
            return $this->adPlanData->sum('cost');
        });
        $grid->column('cost_off', "消费(实)")->display(function () {
            return $this->adPlanData->sum('cost_off');
        });
        $grid->column('ctr', "点击率")->display(function () {
            $show  = $this->adPlanData->sum('show');
            $click = $this->adPlanData->sum('click');

            return round($click / $show * 100, 3) . '%';
        });
        $grid->column('avg_click_cost', "平均点击单价")->display(function () {
            $cost  = $this->adPlanData->sum('cost');
            $click = $this->adPlanData->sum('click');

            return round($cost / $click, 3) . '元';
        });
        $grid->column('avg_show_cost', "平均千次展现费用")->display(function () {
            $cost = $this->adPlanData->sum('cost');
            $show = $this->adPlanData->sum('show');

            return round($cost / $show * 1000, 3) . '元';
        });
        $grid->column('attribution_convert', "转化数")->display(function () {
            return $this->adPlanData->sum('attribution_convert');
        });
        $grid->column('attribution_convert_cost', "转化成本")->display(function () {
            $cost    = $this->adPlanData->sum('cost');
            $convert = $this->adPlanData->sum('attribution_convert');

            return round($cost / $convert, 3) . '元';

        });


        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid  = new Grid(new JLAccount());
        $model = $grid->model();
        $model->with(['token'])->adminUserHospital();

        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $options = \Encore\Admin\Facades\Admin::user()->hospital_list->pluck('hospital_name', 'id');

                $filter->equal('hospital_id', '医院类型')
                    ->select($options);
            });
        });

        $grid->batchActions(function ($batch) {
//            $batch->disableDelete();
            $batch->add(new BatchReferershToken());
        });
        $grid->actions(function ($actions) {
            $actions->add(new RefreshToken());
            $actions->add(new PullData());
        });

        $grid->fixColumns(1);
        $options = HospitalType::all()->pluck('hospital_name', 'id')->toArray();

        $grid->column('id', __('ID'));

        $grid->column('hospital_id', __('Hospital name'))->display(function ($val) use ($options) {
            if (!$val) return '未设置';
            return Arr::get($options, $val, '未设置');
        });
        $grid->column('advertiser_name', __('Advertiser name'));
        $grid->column('advertiser_id', __('Advertiser id'));
        $grid->column('token.status', __('Status'))->using(JLAccount::$statusList);
        $grid->column('rebate', __('Rebate'))->display(function ($val) {
            return $val . '%';
        });
        $grid->column('comment', __('Comment'))->editable();
        $grid->column('created_at', __('Created at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(JLAccount::findOrFail($id));

        $show->field('status', __('Status'))->using(JLAccount::$statusList);
        $show->field('advertiser_id', __('Advertiser id'));
        $show->field('advertiser_name', __('Advertiser name'));
        $show->divider();
        $show->field('account_type', __('Account type'))->using(JLAccount::$accountTypeList);
        $show->field('rebate', __('Rebate'))->as(function ($vla) {
            return $vla . '%';
        });
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new JLAccount());

        $form->text('advertiser_id', __('Advertiser id'))->readonly();
        $form->text('advertiser_name', __('Advertiser name'))->readonly();
        $form->divider();

        $options = HospitalType::all()->pluck('hospital_name', 'id')->toArray();
        $form->select('hospital_id', __('Hospital name'))->options($options);
        $form->currency('rebate', __('Rebate'))->default(0.00)->symbol('%');
        $form->text('comment', __('Comment'));
        $form->switch('enable_robot', '消费通知')->default(1);
        $form->number('limit_cost', '消费预警')->default(0)->min(0);


        return $form;
    }
}
