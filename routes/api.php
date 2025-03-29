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
        Route::post('import-stock-outside', 'StockMasterController@import_stock_outside');
        Route::post('import-challan-outside', 'ChallanController@import_challan_outside');
        Route::post('import-challan-outside-nested', 'ChallanController@import_challan_outside_nested');
        Route::post('import-outstanding-outside', 'OutstandingController@import_outstanding_outside');
        Route::post('import-outstanding-outside-nested', 'OutstandingController@import_outstanding_outside_nested');

        Route::get('business_categories_fulllist', 'BusinessCategoryController@full_list');
        Route::get('delete-all-stock', 'StockMasterController@deleteAllStock');
        Route::get('delete-all-outstandings', 'OutstandingController@deleteAllOutstanding');
        Route::get('delete-all-challan', 'ChallanController@deleteAllChallan');

        Route::post('delete-challan', 'ChallanController@deleteChallan');
    });
    
    Route::middleware(['jwt.verify'])->group(function() {
        Route::namespace('Operations')->group(function() {

            Route::get('user-levels', 'UserLevelController@show');

            Route::get('user-master', 'UserMasterController@index');
            Route::get('user-master/{id}', 'UserMasterController@show');
            Route::post('user-master', 'UserMasterController@store');
            Route::put('user-master/{id}', 'UserMasterController@update');
            Route::get('user-details', 'UserMasterController@details');
            Route::get('all-vendors', 'UserMasterController@getAllVendors');

            Route::get('customer-master', 'CustomerMasterController@index');
            Route::get('customer-master/{id}', 'CustomerMasterController@show');
            Route::post('customer-master', 'CustomerMasterController@store');
            Route::put('customer-master/{id}', 'CustomerMasterController@update');

            Route::get('order-master', 'OrderMasterController@index');
            Route::get('order-master/{id}', 'OrderMasterController@show');
            Route::put('order-master/{id}', 'OrderMasterController@update');
            Route::post('add_challan', 'OrderMasterController@addChallan');
            
            Route::get('challan-list', 'ChallanController@index');

            Route::get('stock-master', 'StockMasterController@index');
            Route::post('stocks-import', 'StockMasterController@import');
            Route::get('clear_vendor_stock', 'StockMasterController@ClearVendorStock');
            Route::get('last-uploaded-time', 'VendorHistoryController@lastUploaded');
            Route::get('total-stock-weight', 'StockMasterController@totalWeight');

            Route::get('outstanding', 'OutstandingController@index');
            Route::get('outstanding/{id}', 'OutstandingController@show');

            Route::get('option-master', 'OptionMasterController@index');
            Route::get('option-master/{id}', 'OptionMasterController@show');
            Route::put('option-master/{id}', 'OptionMasterController@update');

            Route::get('delete-ptsc-stocks', 'OptionMasterController@deletePtscStock');
            Route::get('copy-ptsc-stocks', 'OptionMasterController@copyPtscStock');
            Route::get('option_masters', 'OptionMasterController@allItems');

            Route::get('search-history', 'SearchHistoryMasterController@index');
            
            Route::post('search-report', 'SearchReportController@index');

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
            
            Route::get('sales_tile', 'DashboardController@sales_tile');
            Route::get('fastest_selling_tile', 'DashboardController@fastest_selling_tile');
            Route::get('outstanding_tile', 'DashboardController@outstanding_tile');
            Route::get('customer_search_history_tile', 'DashboardController@customer_search_history_tile');
            
        });
    });

    
    Route::namespace('Authentication')->group(function() {
        Route::prefix("API_login")->group(function () {
            Route::get('get_details', 'LoginController@get_details');
            Route::post('login_new', 'LoginController@login_new');
            Route::get('verify_no_new', 'LoginController@verify_no_new');
            Route::get('SendSMS/{mobile_no}', 'LoginController@SendSMS');
            Route::get('verify_otp', 'LoginController@verify_otp');
            Route::get('set_password', 'LoginController@set_password');
        });
    });

    
    Route::namespace('Operations')->group(function() {
        Route::prefix("API_labels")->group(function () {
            Route::get('labels', 'LabelController@index');
        });
        Route::prefix("API_outstand")->group(function () {
            Route::get('company', 'OutstandingController@company');
        });
        Route::prefix("API_search")->group(function () {
            Route::get('getMasters', 'SearchSizeController@getMasters');
            Route::post('search_dynamic_column_wise', 'SearchSizeController@search_dynamic_column_wise');
            Route::get('GetStockdetail', 'StockMasterController@GetStockdetail');
            Route::post('full_stock', 'StockMasterController@full_stock');
            Route::get('all-unique-qualities', 'StockMasterController@unique_quality');
        });
        Route::prefix("API_bank")->group(function () {
            Route::get('bank_details', 'LabelController@bank_details');
        });
        Route::prefix("API_notification")->group(function () {
            Route::get('push_notification_list', 'PushNotificationController@push_notification_list');
        });
        Route::prefix("API_status")->group(function () {
            Route::get('onactioncall', 'OptionMasterController@onactioncall');
            Route::get('get_challan_list', 'ChallanController@get_challan_list');
            Route::get('detials', 'ChallanController@detials');
            Route::get('android_version', 'OptionMasterController@android_version');
            Route::get('ios_version', 'OptionMasterController@ios_version');
            Route::post('get_challan_url', 'OptionMasterController@get_challan_url');
            Route::get('option_masters', 'OptionMasterController@allItems');
            Route::get('get_orders_list', 'OrderMasterController@get_orders_list');
            Route::get('get_order', 'OrderMasterController@get_order');
            Route::get('get_search_history', 'OrderMasterController@get_search_history');
        });
        Route::prefix("API_addorder")->group(function () {
            Route::get('find_company_list', 'OrderMasterController@find_company_list');
            Route::post('add_order', 'OrderMasterController@add_order');
            Route::post('update_quantity', 'OrderMasterController@update_quantity');
            Route::post('cancel_order', 'OrderMasterController@cancel_order');
        });
    });
    
});
    
Route::post('get_data_from_database', 'Controller@get_data_from_database');