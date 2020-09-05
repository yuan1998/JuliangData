<?php

namespace App\Admin\Controllers;

use App\Models\HospitalType;
use App\Models\JLAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use \App\Models\JLApp;

class JLAppController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '巨量 APPID 管理';


    public function index(Content $content)
    {
        $user = Admin::user();
        initVue();

        $hospitalTypeList =
            ($user && $user->hospital()->exists())
                ? $user->hospital()->pluck('hospital_name', 'id')->toJson()
                : HospitalType::all()->pluck('hospital_name', 'id')->toJson();
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body("<modal-generate-auth-url :hospital-type-list='{$hospitalTypeList}'>")
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new JLApp());
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $create->text('name', __('Name'));
            $create->text('app_id', __('App id'));
            $create->text('app_secret', __('App secret'));
        });
        $grid->column('id', __('Id'))->display(function () {
            $data = $this->toJson();
            return "<button-generate-auth-url :data='$data'></button-generate-auth-url>";
        });
        $grid->column('name', __('Name'));
        $grid->column('app_id', __('App id'));
        $grid->column('app_secret', __('App secret'));
        $grid->column('use_count', __('Use count'));
        $grid->column('limit_count', __('Limit count'));
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
        $show = new Show(JLApp::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('app_id', __('App id'));
        $show->field('app_secret', __('App secret'));
        $show->field('use_count', __('Use count'));
        $show->field('limit_count', __('Limit count'));
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
        $form = new Form(new JLApp());

        $form->text('name', __('Name'));
        $form->text('app_id', __('App id'));
        $form->text('app_secret', __('App secret'));
        $form->number('use_count', __('Use count'));
        $form->number('limit_count', __('Limit count'))->default(50);

        return $form;
    }
}
