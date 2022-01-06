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
        });
    });
    
});