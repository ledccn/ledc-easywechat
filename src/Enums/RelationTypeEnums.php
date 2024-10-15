<?php

namespace Ledc\EasyWechat\Enums;

/**
 * 【与分账方的关系类型】 子商户与接收方的关系
 */
enum RelationTypeEnums
{
    /**
     * 服务商
     */
    case SERVICE_PROVIDER;
    /**
     * 门店
     */
    case STORE;
    /**
     * 员工
     */
    case STAFF;
    /**
     * 店主
     */
    case STORE_OWNER;
    /**
     * 合作伙伴
     */
    case PARTNER;
    /**
     * 总部
     */
    case HEADQUARTER;
    /**
     * 品牌方
     */
    case BRAND;
    /**
     * 分销商
     */
    case DISTRIBUTOR;
    /**
     * 用户
     */
    case USER;
    /**
     * 供应商
     */
    case SUPPLIER;
    /**
     * 自定义
     */
    case CUSTOM;
}
