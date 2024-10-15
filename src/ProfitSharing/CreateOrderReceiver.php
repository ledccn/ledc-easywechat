<?php

namespace Ledc\EasyWechat\ProfitSharing;

use EasyWeChat\Kernel\Config;

/**
 * 分账接收方，实际分账时的数据结构
 * @property string $type 分账接收方类型枚举值【ReceiverTypeEnums】
 * @property string $account 分账接收方帐号
 * @property string $name 分账个人接收方姓名
 * @property int $amount 分账金额，单位为分，只能为整数，不能超过原订单支付金额及最大分账比例金额
 * @property string $description 分账描述，分账账单中需要体现
 */
class CreateOrderReceiver extends Config
{
    /**
     * @var array<string>
     */
    protected array $requiredKeys = [
        'type',
        'account',
        //'name',
        'amount',
        'description',
    ];
}
