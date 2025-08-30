<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 配送订单审核通知
 * - 订单编号:{{character_string1.DATA}}
 * - 订单金额:{{amount6.DATA}}
 * - 审核时间:{{time5.DATA}}
 */
class Template55303 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string1',
            'amount6',
            'time5'
        ];
    }
}
