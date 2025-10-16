<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 排队叫号通知
 * - 店铺名称:{{thing21.DATA}}
 * - 项目名称:{{thing13.DATA}}
 * - 排队号码:{{character_string10.DATA}}
 * - 等待人数:{{short_thing4.DATA}}
 * - 预计等待时间:{{short_thing5.DATA}}
 */
class Template43523 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        // 店铺名称 西红门养生馆
        // 项目名称 伤口清洗项目
        // 排队号码 05
        // 等待人数 3人
        // 预计等待时间 16分钟
        return [
            'thing21',
            'thing13',
            'character_string10',
            'short_thing4',
            'short_thing5'
        ];
    }
}
