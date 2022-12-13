<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::group( function(){
    Route::post('wlog', 'api/wlog');

    Route::post('create_order', 'api/create_order');
    Route::post('create_payment', 'api/create_payment');
    Route::get('order_status', 'api/order_status');
    Route::any('call_payment', 'api/call_payment');
})->middleware(['rjson']);








