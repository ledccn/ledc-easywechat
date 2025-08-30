<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 充值成功通知
 * - 充值时间:{{time1.DATA}}
 * - 充值金额:{{amount3.DATA}}
 * - 赠送金额:{{amount4.DATA}}
 * - 当前余额:{{amount5.DATA}}
 */
class Template52552 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return string[]
     */
    protected function getRequiredParams(): array
    {
        return [
            'time1',
            'amount3',
            'amount4',
            'amount5',
        ];
    }
}
