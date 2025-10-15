<?php

namespace Ledc\EasyWechat\Enums;

use Webman\Event\Event;

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

    /**
     * 微信公众号：微信网页授权登录成功后
     */
    case wechat_account_oauth_successful = 'wechat.account.oauth_successful';

    /**
     * 绑定事件
     * @param callable $fn
     * @return int
     */
    public function on(callable $fn): int
    {
        return Event::on($this->value, $fn);
    }

    /**
     * 移除事件
     * @param int $id
     * @return int
     */
    public function off(int $id): int
    {
        return Event::off($this->value, $id);
    }

    /**
     * 触发事件
     * @param mixed $data
     * @param bool $halt
     * @return array|null|mixed
     */
    public function emit(mixed $data, bool $halt = false): mixed
    {
        return Event::emit($this->value, $data, $halt);
    }

    /**
     * 触发事件
     * @param mixed $data
     * @param bool $halt
     * @return array|null|mixed
     */
    public function dispatch(mixed $data, bool $halt = false): mixed
    {
        return Event::dispatch($this->value, $data, $halt);
    }
}
