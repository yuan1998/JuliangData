<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLogStoreRequest;
use App\Models\ExportLog;
use Illuminate\Http\Request;

class ExportLogController extends Controller
{

    public function store(ExportLogStoreRequest $request)
    {
        $data = $request->all();
        $test = ExportLog::checkType($data);

        if ($test) {
            return response()->json([
                'code'    => 0,
                'message' => '开始导出',
            ]);
        } else {
            return response()->json([
                'code'    => 1001,
                'message' => '导出错误,联系管理员'
            ]);
        }
    }

}
