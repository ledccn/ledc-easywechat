<?php

namespace Ledc\EasyWechat\Contracts;

use Ledc\EasyWechat\Enums\TerminalEnum;

/**
 * 获取微信支付的配置
 */
interface PayConfig
{
    /**
     * 获取微信支付的配置
     * @param TerminalEnum|null $terminal 终端支付渠道
     * @return array
     */
    public function get(?TerminalEnum $terminal = null): array;
}
