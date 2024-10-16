<?php

namespace Ledc\EasyWechat;

use Closure;
use InvalidArgumentException;
use Ledc\EasyWechat\Contracts\PayConfig;
use Ledc\EasyWechat\Enums\TerminalEnum;

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
     * 获取微信支付配置数组
     * @param TerminalEnum|null $terminal 终端支付渠道
     * @return array
     */
    public static function getPayConfig(TerminalEnum $terminal = null): array
    {
        if (is_null(self::$payConfig)) {
            throw new InvalidArgumentException('缺少获取微信支付配置的实例');
        }

        $instance = static::$payConfig;
        return match (true) {
            $instance instanceof PayConfig => $instance->get($terminal),
            $instance instanceof Closure => call_user_func($instance, $terminal),
            default => throw new InvalidArgumentException('无法获取微信支付配置')
        };
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
