<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 订单已签收通知
 * - 订单编号:{{character_string2.DATA}}
 * - 签收时间:{{character_string7.DATA}}
 * - 商品名称:{{thing4.DATA}}
 * - 订单金额:{{amount9.DATA}}
 */
class Template42985 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string2',
            'character_string7',
            'thing4',
            'amount9'
        ];
    }
}
