<?php

namespace Ledc\EasyWechat\OfficialAccount\Templates;

use Ledc\EasyWechat\OfficialAccount\TemplateBody;

/**
 * 排队进度通知
 * - 门店名称:{{thing10.DATA}}
 * - 排队项目:{{thing16.DATA}}
 * - 排队号码:{{short_thing3.DATA}}
 * - 等待人数:{{short_thing4.DATA}}
 * - 预计等待时间:{{short_thing5.DATA}}
 */
class Template43521 extends TemplateBody
{
    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    protected function getRequiredParams(): array
    {
        // 门店名称 舞东风便利店
        // 排队项目 摩天轮
        // 排队号码 10号
        // 等待人数 5人
        // 预计等待时间 15分钟
        return [
            'thing10',
            'thing16',
            'short_thing3',
            'short_thing4',
            'short_thing5'
        ];
    }
}
