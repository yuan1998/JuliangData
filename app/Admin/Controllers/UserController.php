<?php

namespace App\Admin\Controllers;

use App\Models\HospitalType;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;

class UserController extends \Encore\Admin\Controllers\UserController
{
    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel       = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel       = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable  = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));
        $form->multipleSelect('hospital', "医院分类")
            ->placeholder('选择该账户能查看到的医院数据,不选表示全部能查看')
            ->options(HospitalType::all()->pluck('hospital_name', 'id'));


        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }
        });

        return $form;
    }

}
