<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 订单发货通知
 * - 订单编号:{{character_string2.DATA}}
 * - 发货时间:{{time12.DATA}}
 * - 商品名称:{{thing4.DATA}}
 * - 快递公司:{{thing13.DATA}}
 * - 快递单号:{{character_string14.DATA}}
 */
class Template42984 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string2',
            'time12',
            'thing4',
            'thing13',
            'character_string14'
        ];
    }
}
