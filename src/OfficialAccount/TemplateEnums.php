<?php

namespace Ledc\EasyWechat\OfficialAccount;

/**
 * 微信服务号模板编号枚举类
 */
enum TemplateEnums: string
{
    /**
     * 订单支付成功通知
     */
    case ORDER_PAID = '43216';
    /**
     * 订单发货通知
     */
    case ORDER_DELIVERED = '42984';
    /**
     * 订单开始配送通知
     */
    case ORDER_DELIVERY_START = '50079';
    /**
     * 订单已签收通知
     */
    case ORDER_SIGNED = '42985';
    /**
     * 退款成功通知
     */
    case REFUND_SUCCESS = '46622';
    /**
     * 退款驳回通知
     */
    case REFUND_REJECTED = '46623';
    /**
     * 充值成功通知
     */
    case RECHARGE_SUCCESS = '52552';
    /**
     * 提现成功通知
     */
    case WITHDRAW_SUCCESS = '52381';
    /**
     * 收到客户新订单通知
     */
    case NEW_ORDER = '48089';
    /**
     * 订单待发货提醒
     */
    case ORDER_PENDING_SHIPPING = '46045';
    /**
     * 异常订单处理通知
     */
    case ORDER_EXCEPTION = '54868';
    /**
     * 配送订单审核通知
     */
    case DELIVERY_ORDER_AUDIT = '55303';
    /**
     * 排队进度通知
     * @doc 所属类目：工具 -- 预约/报名
     */
    case QUEUE_PROGRESS = '43521';
    /**
     * 排队即将到号提醒
     * @doc 所属类目：工具 -- 预约/报名
     */
    case QUEUE_COMING_UP = '43523';
    /**
     * 排队叫号通知
     * @doc 所属类目：工具 -- 预约/报名
     */
    case QUEUE_CALLING = '43522';

    /**
     * 创建模板消息对象
     * @param string $template_id
     * @return TemplateBody
     */
    public function create(string $template_id): TemplateBody
    {
        return TemplateFactory::create($this->value, $template_id);
    }
}
