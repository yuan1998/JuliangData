<?php

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
