<?php

namespace App\Admin\Actions\JLAccount;

use App\Models\JLAccount;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Admin;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends RowAction
{
    public $name = '刷新Token';

    public function actionScript()
    {
        return <<<SCIPRT
//<![CDATA[
         Swal.fire({
                title            : '',
                html             : '<div class="save_loading"><svg viewBox="0 0 140 140" width="140" height="140"><g class="outline"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="rgba(0,0,0,0.1)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round">\<\/path>\<\/g><g class="circle"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="#71BBFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="200" stroke-dasharray="300">\<\/path>\<\/g>\<\/svg>\<\/div><div><h4>刷新Token中,请稍等...\<\/h4>\<\/div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
   
//]]>

SCIPRT;
    }

    public function handle(JLAccount $model)
    {
        $token = $model->token;

        if (!$token)
            return $this->response()->swal()->error('刷新Token失败.Token不存在,请确认.')->refresh();

        $appConfig = $model->appConfig;
        if (!$appConfig)
            return $this->response()->swal()->error('刷新Token失败.App配置不存在,请确认.')->refresh();

        $response = $token->refreshToken($appConfig);

        if (!$response)
            return $this->response()->swal()->error('刷新Token失败.连接巨量服务器错误,请确认.')->refresh();


        $code = $response['code'];
        if ($code != 0) {
            return $this->response()->swal()->error("刷新Token失败. \n\r Code : {$code} \n\r Message : {$response['message']}")->refresh();
        }
        return $this->response()->swal()->success('刷新Token成功!')->refresh();
    }

}
