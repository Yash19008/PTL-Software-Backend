<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Routes which does not for specific tenant
Route::namespace('V1')->middleware(['cors'])->group(function() {
    Route::prefix("auth")->group(function () {
        Route::namespace('Authentication')->group(function() {
            Route::post('login', 'LoginController@login');
            Route::post('register', 'RegisterController@register');
        });
    });

    /* Open API */
    Route::namespace('Operations')->group(function() {
        Route::get('business_categories_fulllist', 'BusinessCategoryController@full_list');
    });
    
    Route::middleware(['jwt.verify'])->group(function() {
        Route::namespace('Operations')->group(function() {

            Route::get('user-levels', 'UserLevelController@show');

            Route::get('user-master', 'UserMasterController@index');
            Route::get('user-master/{id}', 'UserMasterController@show');
            Route::post('user-master', 'UserMasterController@store');
            Route::put('user-master/{id}', 'UserMasterController@update');

            Route::get('customer-master', 'CustomerMasterController@index');
            Route::get('customer-master/{id}', 'CustomerMasterController@show');
            Route::post('customer-master', 'CustomerMasterController@store');
            Route::put('customer-master/{id}', 'CustomerMasterController@update');

            Route::get('order-master', 'OrderMasterController@index');
            Route::get('order-master/{id}', 'OrderMasterController@show');
            Route::post('order-master', 'OrderMasterController@store');
            Route::put('order-master/{id}', 'OrderMasterController@update');
            
            Route::get('challan-list', 'ChallanController@index');

            Route::get('stock-master', 'StockMasterController@index');

            Route::get('outstanding', 'OutstandingController@index');
            Route::get('outstanding/{id}', 'OutstandingController@show');

            Route::get('option-master', 'OptionMasterController@index');
            Route::get('option-master/{id}', 'OptionMasterController@show');
            Route::put('option-master/{id}', 'OptionMasterController@update');

            Route::get('delete-ptsc-stocks', 'OptionMasterController@deletePtscStock');
            Route::get('copy-ptsc-stocks', 'OptionMasterController@copyPtscStock');

            Route::get('search-history', 'SearchHistoryMasterController@index');

            Route::get('push-notification', 'PushNotificationController@index');
            Route::post('push-notification-size', 'PushNotificationController@sendsize');
            Route::post('push-notification-message', 'PushNotificationController@sendmessage');

            Route::post('search-size', 'SearchSizeController@searchSize');

            Route::get('all-product-group', 'ProductGroupController@all_list');
            Route::get('product-group', 'ProductGroupController@index');
            Route::get('product-group/{id}', 'ProductGroupController@show');
            Route::post('product-group', 'ProductGroupController@store');
            Route::put('product-group/{id}', 'ProductGroupController@update');
            Route::delete('product-group/{id}', 'ProductGroupController@destroy');
            
        });
    });
    
});