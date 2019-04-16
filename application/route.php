<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//引入Route
use think\Route;

Route::rule('/stock/login','api/index/login');

Route::rule('/stock/inbound','api/index/inbound');

Route::rule('/stock/outbound','api/index/outbound');

Route::rule('/stock/report','api/index/report');


Route::rule('/user/signin','api/files/signin');

Route::rule('/product/query','api/files/product_query');

Route::rule('/product/modify','api/files/product_modify');

Route::rule('/product/delete','api/files/product_delete');

Route::rule('/tool/login','api/moble/login');

Route::rule('/tool/add','api/moble/add');

Route::rule('/tool/query','api/moble/query');