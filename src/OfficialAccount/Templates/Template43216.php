<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 订单支付成功通知
 * - 订单号:{{character_string2.DATA}}
 * - 下单时间:{{time4.DATA}}
 * - 商品名称:{{thing3.DATA}}
 * - 支付金额:{{amount5.DATA}}
 */
class Template43216 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string2',
            'time4',
            'thing3',
            'amount5'
        ];
    }
}
