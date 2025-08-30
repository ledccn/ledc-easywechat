<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 退款成功通知
 * - 订单编号:{{character_string1.DATA}}
 * - 退款时间:{{time5.DATA}}
 * - 商品名称:{{thing2.DATA}}
 * - 退款金额:{{amount3.DATA}}
 */
class Template46622 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string1',
            'time5',
            'thing2',
            'amount3'
        ];
    }
}
