<?php

namespace App\Admin\Actions\Account;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchReferershToken extends BatchAction
{
    public $name = '批量刷新Token';

    public function actionScript()
    {
        $result = parent::actionScript();

        return <<<SCIPRT
//<![CDATA[
        {$result}
         Swal.fire({
                title            : '',
                html             : '<div class="save_loading"><svg viewBox="0 0 140 140" width="140" height="140"><g class="outline"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="rgba(0,0,0,0.1)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round">\<\/path>\<\/g><g class="circle"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="#71BBFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="200" stroke-dasharray="300">\<\/path>\<\/g>\<\/svg>\<\/div><div><h4>刷新Token中,请稍等...\<\/h4>\<\/div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
   
//]]>

SCIPRT;
    }

    public function handle(Collection $collection)
    {
        $error   = 0;
        $success = 0;
        foreach ($collection as $model) {
            $token     = $model->token;
            $appConfig = $model->appConfig;

            if (!$token || !$appConfig) {
                $error++;
                continue;
            }


            $response = $token->refreshToken($appConfig);

            if (!$response) {
                $error++;
                continue;
            }

            $code = $response['code'];
            if ($code != 0) {
                $error++;
                continue;
            }
            $success++;
        }

        return $this->response()->swal()->success("刷新Token成功!有{$success}条成功.{$error}条失败")->refresh();

    }

}
