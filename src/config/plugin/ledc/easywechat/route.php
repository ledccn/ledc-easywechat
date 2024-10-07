<?php

use Ledc\EasyWechat\PayNotifyService;
use support\Request;
use Webman\Route;

// 微信支付回调
Route::any('/wechat/pay/callback', function (Request $request) {
    return PayNotifyService::handle($request);
});
