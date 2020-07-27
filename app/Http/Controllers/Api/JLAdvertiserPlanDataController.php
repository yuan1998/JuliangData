<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JLAccount;
use App\Models\JLAdvertiserPlanData;
use Illuminate\Http\Request;

class JLAdvertiserPlanDataController extends Controller
{

    public function pagination(Request $request)
    {
        $dates    = $request->get('dates');
        $pageSize = $request->get('page_size', 20);
        $keys     = array_keys(JLAdvertiserPlanData::$displayFields);

        $query = JLAdvertiserPlanData::query()->select(array_merge($keys, ['advertiser_id']))->with(['accountData']);

        if ($dates) {
            $query->whereBetween('stat_datetime', $dates);
        }

        $query->paginate($pageSize);
    }

}
