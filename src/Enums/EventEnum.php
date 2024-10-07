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
}
