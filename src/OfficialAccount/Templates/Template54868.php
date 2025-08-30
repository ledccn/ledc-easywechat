<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 异常订单处理通知
 * - 订单号:{{character_string1.DATA}}
 * - 下单金额:{{amount3.DATA}}
 * - 下单时间:{{time4.DATA}}
 * - 异常时间:{{time6.DATA}}
 * - 异常原因:{{const5.DATA}}
 */
class Template54868 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string1',
            'amount3',
            'time4',
            'time6',
            'const5'
        ];
    }
}
