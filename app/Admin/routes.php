<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
    'as'         => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');


    $router->resource('jl-accounts', "JLAccountController");
    $router->resource('jl-advertiser-plan-datas', JLAdvertiserPlanDataController::class);
    $router->resource('export-logs', ExportLogController::class);
    $router->resource('j-l-apps', JLAppController::class);
    $router->resource('hospital-types', HospitalTypeController::class);
});
