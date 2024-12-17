<?php

namespace Ledc\EasyWechat;

use EasyWeChat\Kernel\Exceptions\BadResponseException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\Pay\Application;
use ErrorException;
use Ledc\EasyWechat\Traits\InteractCommon;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * 退款服务
 */
class RefundService
{
    use InteractCommon;

    /**
     * 构造函数
     * @param Application $app
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $app->getConfig();
        $this->client = $app->getClient();
        $this->utils = $app->getUtils();
        $this->merchant = $app->getMerchant();
    }

    /**
     * 退款申请
     * @link https://pay.weixin.qq.com/doc/v3/merchant/4012556475
     * @param string $transaction_id 微信支付订单号
     * @param string $out_refund_no 商户退款单号（商户系统内部的退款单号，商户系统内部唯一，只能是数字、大小写字母_-|*@ ，同一退款单号多次请求只退一笔）
     * @param array $params 其他退款参数
     * @return array
     * @throws BadResponseException
     * @throws ErrorException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function refund(string $transaction_id, string $out_refund_no, array $params): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/refund/domestic/refunds', array_merge([
            'transaction_id' => $transaction_id,
            'out_refund_no' => $out_refund_no,
            'notify_url' => $this->getConfig()->get('notify_url'),
        ], $params));

        return $this->getResultByResponse($response);
    }

    /**
     * 查询单笔退款（通过商户退款单号）
     * - 提交退款申请后，通过调用该接口查询退款状态。
     * - 退款有一定延时，建议查询退款状态在提交退款申请后1分钟发起；
     * - 一般来说零钱支付的退款5分钟内到账，银行卡支付的退款1-3个工作日到账。
     * @link https://pay.weixin.qq.com/doc/v3/merchant/4012556569
     * @param string $out_refund_no 商户退款单号
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function queryRefund(string $out_refund_no): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->get("v3/refund/domestic/refunds/{$out_refund_no}");

        return $this->getResultByResponse($response);
    }
}
