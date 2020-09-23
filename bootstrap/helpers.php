<?php

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Encore\Admin\Admin;

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function initVue()
{
    Admin::script(<<<EOF
        const app = new Vue({
            el: '#app'
        });
EOF
    );
}

function disableAutocomplete()
{
    Admin::script(<<<EOT
$(function() {
    'use strict';
    
    $('input.form-control').attr('autocomplete','off');
})
EOT
    );
}


/**
 * 迭代 日期范围, 在回调中传入当前日期
 * @param array   $dates    日期范围
 * @param Closure $callBack 回调
 */
function dateRangeForEach($dates, Closure $callBack)
{
    $date = CarbonPeriod::create($dates[0], $dates[1]);

    foreach ($date as $item) {
        $callBack($item);
    }
}

function validateInTimeRange($start, $end, $testTime = null)
{
    // 获取用于对比的时间
    $now     = $testTime ? Carbon::parse($testTime) : Carbon::now();
    $nowTime = (int)$now->format('His');

    if ($start && $start = Carbon::parse($start)) {
        $startTime = (int)$start->format('His');

        if ($startTime >= $nowTime) return false;
    }

    if ($end && $end = Carbon::parse($end)) {
        $endTime = (int)$end->format('His');

        if ($nowTime >= $endTime) return false;
    }

    return true;
}
