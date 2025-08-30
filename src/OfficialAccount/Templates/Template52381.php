<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 提现成功通知
 * - 提现时间:{{time3.DATA}}
 * - 提现金额:{{amount2.DATA}}
 */
class Template52381 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'time3',
            'amount2'
        ];
    }
}
