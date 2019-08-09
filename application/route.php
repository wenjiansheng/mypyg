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

use think\Route;

Route::domain('adminapi', function () {
    Route::get('/', 'adminapi/index/index');

    Route::get('captcha/:id', "\\think\\captcha\\CaptchaController@index"); //访问图片需要
    Route::get('captcha', 'adminapi/login/captcha');

    Route::post('login', 'adminapi/login/login');
    Route::get('logout', 'adminapi/login/logout');
    Route::resource('auths', 'adminapi/auth',[],['id'=>'\d+']);
    Route::get('nav', 'adminapi/auth/nav');
    Route::resource('roles', 'adminapi/role',[],['id'=>'\d+']);
    Route::resource('admins', 'adminapi/admin',[],['id'=>'\d+']);
    Route::resource('categorys', 'adminapi/category',[],['id'=>'\d+']);
    Route::resource('brands', 'adminapi/brand',[],['id'=>'\d+']);
    Route::resource('types', 'adminapi/type',[],['id'=>'\d+']);
    Route::resource('goods', 'adminapi/goods',[],['id'=>'\d+']);
    Route::delete('delpics/:id', 'adminapi/goods/delpics',[],['id'=>'\d+']);
   
    Route::post('logo', 'adminapi/upload/logo');
    Route::post('images', 'adminapi/upload/images');


});
