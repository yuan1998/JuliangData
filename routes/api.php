<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')
    ->name('api.v1.')
    ->namespace('Api')
    ->group(function () {
        Route::prefix('juliang')->name('JL.')->group(function () {
            Route::get('test', 'JuliangController@test')->name('test');
            Route::get('auth_code', 'JuliangController@juliangAuth')->name('authCode');
            Route::get('account_info', 'JuliangController@accountInfo')->name('accountInfo');
            Route::get('advertiser_plan_data_pull', 'JuliangController@pullAdvertiserPlanData')->name('pullAdvertiserPlanData');
            Route::get('advertiser_plan_data_export', 'JuliangController@exportAdvertiserPlanData')->name('exportAdvertiserPlanData');


            Route::prefix('feiyuClue')->name('feiyu.')->group(function () {

                Route::get('test', 'JuliangController@fieyuClueTest')->name('test');

            });
        });

        Route::prefix('club')->name('club.')->group(function () {
            Route::post('post', 'ClubController@post');
            Route::post('baiduPost', 'ClubController@baiduPost');
            Route::post('customerPost', 'ClubController@customerPost');
        });


    });
