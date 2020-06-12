<?php

namespace App\Admin\Controllers;

use App\Models\JLAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\HospitalType;

class HospitalTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '医院类型管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HospitalType());

        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $create->text('hospital_name', __('Hospital name'));
            $create->select('hospital_type', __('Hospital type'))
                ->options(JLAccount::$hospitalTypeList);
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->disableCreateButton();

        
        $grid->column('hospital_name', __('Hospital name'));
        $grid->column('hospital_type', __('Hospital type'))->using(JLAccount::$hospitalTypeList);
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
        $show = new Show(HospitalType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('hospital_name', __('Hospital name'));
        $show->field('hospital_type', __('Hospital type'));
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
        $form = new Form(new HospitalType());

        $form->text('hospital_name', __('Hospital name'));
        $form->text('hospital_type', __('Hospital type'));

        return $form;
    }
}
