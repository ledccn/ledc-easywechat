<?php

namespace Ledc\EasyWechat\Enums;

/**
 * 终端支付渠道枚举类
 */
enum TerminalEnum: int
{
    /**
     * 微信公众号
     */
    case wechat = 1;
    /**
     * 微信小程序
     */
    case routine = 2;
    /**
     * 手机H5
     */
    case h5 = 3;
    /**
     * 电脑PC
     */
    case pc = 4;
    /**
     * 苹果APP
     */
    case apple = 5;
    /**
     * 安卓APP
     */
    case android = 6;

    /**
     * 名值选择
     * @return array
     */
    public static function select(): array
    {
        $rs = [];
        foreach (self::cases() as $enum) {
            $rs[self::text($enum)] = $enum->value;
        }
        return $rs;
    }

    /**
     * @param self $enum
     * @return string
     */
    public static function text(self $enum): string
    {
        return match ($enum) {
            self::wechat => '微信公众号',
            self::routine => '微信小程序',
            self::h5 => '手机H5',
            self::pc => '电脑PC',
            self::apple => '苹果APP',
            self::android => '安卓APP',
        };
    }

    /**
     * 枚举条目转为数组
     * - 名 => 值
     * @return array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
