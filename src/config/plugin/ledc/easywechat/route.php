<?php

use Ledc\EasyWechat\OfficialAccount\OauthMiddleware;
use Ledc\EasyWechat\PayNotifyService;
use Ledc\EasyWechat\WechatService;
use support\Request;
use Webman\Route;

// 微信支付回调
Route::any('/wechat/pay/callback', function (Request $request) {
    return PayNotifyService::handle($request);
});

// 微信公众号回调
Route::any('/wechat/account/callback', function (Request $request) {
    return WechatService::handle($request);
});

// 微信公众号网页授权登录授权完成后，重定向URL
Route::any('/wechat/account/oauth/redirect', function (Request $request) {
    return OauthMiddleware::redirect($request);
});
