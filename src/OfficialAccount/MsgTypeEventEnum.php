<?php

namespace Ledc\EasyWechat\OfficialAccount;

/**
 * 枚举：微信公众号事件推送
 */
enum MsgTypeEventEnum: string
{
    /**
     * 关注事件
     */
    case subscribe = 'subscribe';

    /**
     * 取消关注事件
     */
    case unsubscribe = 'unsubscribe';

    /**
     * 扫描带参数二维码事件（用户已关注时的事件推送）
     */
    case SCAN = 'SCAN';

    /**
     * 上报地理位置事件
     */
    case LOCATION = 'LOCATION';

    /**
     * 自定义菜单事件
     */
    case CLICK = 'CLICK';

    /**
     * 模板消息发送完成事件
     */
    case TEMPLATESENDJOBFINISH = 'TEMPLATESENDJOBFINISH';

    /**
     * 键值选择列表
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
            self::subscribe => '关注事件',
            self::unsubscribe => '取消关注事件',
            self::SCAN => '扫描带参数二维码事件',
            self::LOCATION => '上报地理位置事件',
            self::CLICK => '自定义菜单事件',
            self::TEMPLATESENDJOBFINISH => '模板消息发送完成事件',
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
