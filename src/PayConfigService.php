<?php

namespace Ledc\EasyWechat;

use Closure;
use InvalidArgumentException;
use Ledc\EasyWechat\Contracts\PayConfig;

/**
 * 微信支付配置服务类
 */
class PayConfigService
{
    /**
     * 微信支付配置
     * @var PayConfig|Closure
     */
    private static PayConfig|Closure $payConfig;

    /**
     * 获取
     * @return PayConfig|Closure
     */
    public static function getPayConfig(): PayConfig|Closure
    {
        if (is_null(self::$payConfig)) {
            throw new InvalidArgumentException('缺少获取微信支付配置的实例');
        }

        return self::$payConfig;
    }

    /**
     * 设置
     * @param PayConfig|Closure $payConfig
     */
    public static function setPayConfig(PayConfig|Closure $payConfig): void
    {
        self::$payConfig = $payConfig;
    }
}
