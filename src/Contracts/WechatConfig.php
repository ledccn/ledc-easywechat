<?php

namespace Ledc\EasyWechat\Contracts;

use EasyWeChat\Kernel\Contracts\Config as ConfigInterface;
use Webman\Http\Request;

/**
 * 获取微信公众号配置
 */
interface WechatConfig
{
    /**
     * 获取微信公众号配置
     * @param int|string|Request|\support\Request|null $key 终端支付渠道
     * @return array|ConfigInterface
     */
    public function get(int|string|Request|\support\Request $key = null): array|ConfigInterface;
}
