<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\JLAccount;
use Illuminate\Support\Arr;

class JLAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '巨量广告主账户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new JLAccount());

        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->column('advertiser_id', __('Advertiser id'));
        $grid->column('advertiser_name', __('Advertiser name'));
        $grid->column('status', __('Status'))->using(JLAccount::$statusList);
        $grid->column('account_type', __('Account type'))->display(function ($val) {
            if (!$val) return '未设置';
            return Arr::get(JLAccount::$accountTypeList, $val, '未设置');
        });
        $grid->column('rebate', __('Rebate'))->display(function ($val) {
            return $val . '%';

        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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

//        $form->setTitle('test');
        $form->text('status', __('Status'))->readonly();
        $form->text('advertiser_id', __('Advertiser id'))->readonly();
        $form->text('advertiser_name', __('Advertiser name'))->readonly();
        $form->divider();
        $form->select('account_type', __('Account type'))->options(JLAccount::$accountTypeList);

        $form->currency('rebate', __('Rebate'))->default(0.00)->symbol('%');

        return $form;
    }
}
