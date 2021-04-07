<?php

namespace App\Admin\Actions\Account;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class PullData extends RowAction
{
    public $name = '拉取账户数据';

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }

    public function form()
    {
        $this->textarea('reason', '原因')->rules('required');

    }
}
