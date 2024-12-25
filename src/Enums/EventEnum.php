<?php

namespace Ledc\EasyWechat\Enums;

/**
 * webman事件枚举
 */
enum EventEnum: string
{
    /**
     * 微信支付回调通知：所有事件消息
     */
    case wechat_pay_any = 'wechat.pay.any';

    /**
     * 微信支付回调通知：支付成功事件
     */
    case wechat_pay_success = 'wechat.pay.success';

    /**
     * 微信支付回调通知：退款成功事件
     */
    case wechat_pay_refunded = 'wechat.pay.refunded';

    /**
     * 微信公众号：服务器收到回调消息
     */
    case wechat_account_callback = 'wechat.account.callback';

    /**
     * 微信公众号：用户关注
     */
    case wechat_account_subscribe = 'wechat.account.subscribe';

    /**
     * 微信公众号：用户扫描带参数二维码关注
     */
    case wechat_account_subscribe_qrscene = 'wechat.account.subscribe_qrscene';

    /**
     * 微信公众号：用户取消关注
     */
    case wechat_account_unsubscribe = 'wechat.account.unsubscribe';
}
