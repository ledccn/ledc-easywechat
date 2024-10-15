<?php

namespace Ledc\EasyWechat\Enums;

/**
 * 【接收方类型】枚举值
 */
enum ReceiverTypeEnums
{
    /**
     * 商户号
     */
    case MERCHANT_ID;

    /**
     * 个人OpenID（普通商户由直连商户AppID转换得到，服务商模式由服务商AppID转换得到）
     */
    case PERSONAL_OPENID;

    /**
     * 个人在子商户应用下的OpenID（由子商户AppID转换得到，直连商户不需要，仅服务商模式需要）
     */
    case PERSONAL_SUB_OPENID;
}
