<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 排队叫号通知
 * - 门店名称:{{thing20.DATA}}
 * - 排队项目:{{thing15.DATA}}
 * - 排队号码:{{character_string12.DATA}}
 * - 呼叫时间:{{time5.DATA}}
 */
class Template43522 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        // 门店名称 新天地店
        // 排队项目 验光
        // 排队号码 10
        // 呼叫时间 2022-12-18 12：00
        return [
            'thing20',
            'thing15',
            'character_string12',
            'time5'
        ];
    }
}
