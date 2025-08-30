<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 订单待发货提醒
 * - 订单金额:{{amount3.DATA}}
 * - 生成时间:{{time12.DATA}}
 * - 待发笔数:{{character_string13.DATA}}
 * - 超时笔数:{{character_string14.DATA}}
 * - 门店名称:{{thing17.DATA}}
 */
class Template46045 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'amount3',
            'time12',
            'character_string13',
            'character_string14',
            'thing17',
        ];
    }
}
