<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 订单开始配送通知
 * - 订单号:{{character_string1.DATA}}
 * - 配送时间:{{time8.DATA}}
 * - 商品名称:{{thing5.DATA}}
 * - 配送员:{{thing9.DATA}}
 * - 配送员电话:{{phone_number10.DATA}}
 */
class Template50079 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'character_string1',
            'time8',
            'thing5',
            'thing9',
            'phone_number10',
        ];
    }
}
