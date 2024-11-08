<?php

namespace Ledc\EasyWechat;

use Closure;
use Ledc\EasyWechat\Contracts\PayConfig;
use Ledc\EasyWechat\Enums\TerminalEnum;

/**
 * 微信支付配置服务类
 * @deprecated use PayConfigManager
 */
class PayConfigService
{
    /**
     * 获取微信支付配置数组
     * @deprecated use PayConfigManager::get()
     * @param TerminalEnum|null $terminal 终端支付渠道
     * @return array
     */
    public static function getPayConfig(TerminalEnum $terminal = null): array
    {
        return PayConfigManager::get($terminal);
    }

    /**
     * 设置
     * @deprecated use PayConfigManager::set()
     * @param PayConfig|Closure $payConfig
     */
    public static function setPayConfig(PayConfig|Closure $payConfig): void
    {
        PayConfigManager::set($payConfig);
    }
}
