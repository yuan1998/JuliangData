<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\AdvertiserNameList;

class AdvertiserNameListController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'AdvertiserNameList';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AdvertiserNameList());
        $grid->disableCreateButton();
        $grid->disableActions();


        $grid->model()
            ->with([
                'account',
                'hospital'
            ])
            ->orderBy('created_at', 'desc');
        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Grid\Filter $filter) {

                $filter->like('advertiser_name', __('Advertiser name'));

            });
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $options = \Encore\Admin\Facades\Admin::user()->hospital_list->pluck('hospital_name', 'id');

                $filter->equal('hospital_id', '医院类型')
                    ->select($options);

            });

        });


        $grid->column('id', __('Id'));
        $grid->column('comment', __('Comment'))->editable();

        $grid->column('advertiser_name', __('Advertiser name'));
        $grid->column('advertiser_id', __('Advertiser id'));
        $grid->column('account.advertiser_name', __('Account id'));
        $grid->column('hospital.hospital_name', __('Hospital id'));
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
        $show = new Show(AdvertiserNameList::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('comment', __('Comment'));
        $show->field('advertiser_name', __('Advertiser name'));
        $show->field('advertiser_id', __('Advertiser id'));
        $show->field('account_id', __('Account id'));
        $show->field('hospital_id', __('Hospital id'));
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
        $form = new Form(new AdvertiserNameList());

        $form->text('comment', __('Comment'));
        $form->text('advertiser_name', __('Advertiser name'));
        $form->text('advertiser_id', __('Advertiser id'));
        $form->number('account_id', __('Account id'));
        $form->number('hospital_id', __('Hospital id'));

        return $form;
    }
}
