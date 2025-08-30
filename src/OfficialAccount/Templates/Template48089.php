<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 收到客户新订单通知
 * - 订单类型:{{short_thing6.DATA}}
 * - 订单编号:{{character_string1.DATA}}
 * - 订单时间:{{time2.DATA}}
 */
class Template48089 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'short_thing6',
            'character_string1',
            'time2'
        ];
    }
}
