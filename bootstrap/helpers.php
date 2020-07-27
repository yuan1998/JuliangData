<?php

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
