<?php

namespace Ledc\EasyWechat\Contracts;

use EasyWeChat\Kernel\Contracts\Config as ConfigInterface;

/**
 * 获取微信公众号配置
 */
interface WechatConfig
{
    /**
     * 获取微信公众号配置
     * @param int|string|null $key 终端支付渠道
     * @return array|ConfigInterface
     */
    public function get(int|string $key = null): array|ConfigInterface;
}
