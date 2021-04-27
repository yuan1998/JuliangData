<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClubController extends Controller
{
    public function post(Request $request)
    {
        $data = $request->all();
        Log::info('测试 post 接受数据', $data);
    }
}
