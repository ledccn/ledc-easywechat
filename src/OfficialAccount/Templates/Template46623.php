<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 退款驳回通知
 * - 订单编号:{{character_string1.DATA}}
 * - 商品名称:{{thing2.DATA}}
 * - 退款金额:{{amount3.DATA}}
 * - 驳回原因:{{thing4.DATA}}
 */
class Template46623 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string1',
            'thing2',
            'amount3',
            'thing4'
        ];
    }
}
