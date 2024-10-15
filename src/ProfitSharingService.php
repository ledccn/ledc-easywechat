<?php

namespace Ledc\EasyWechat;

use EasyWeChat\Kernel\Exceptions\BadResponseException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\Pay\Application;
use ErrorException;
use Ledc\EasyWechat\Enums\ReceiverTypeEnums;
use Ledc\EasyWechat\Enums\RelationTypeEnums;
use Ledc\EasyWechat\ProfitSharing\CreateOrderReceiver;
use Ledc\EasyWechat\Traits\InteractCommon;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * 普通商户分账
 * - 主要用于商户将交易成功的资金，按照一定的周期，分账给其他方，可以是合作伙伴、员工、用户或者其他分润方。
 */
readonly class ProfitSharingService
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
     * 请求分账
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/orders/create-order.html
     * @param string $transaction_id 微信订单号
     * @param string $out_order_no 商户分账单号
     * @param CreateOrderReceiver[] $receivers 分账接收方列表
     * @param bool $unfreeze_unsplit 是否解冻剩余未分资金
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createOrder(string $transaction_id, string $out_order_no, array $receivers, bool $unfreeze_unsplit = true): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/profitsharing/orders', array_filter([
            'appid' => $this->config->get('app_id'),
            'transaction_id' => $transaction_id,
            'out_order_no' => $out_order_no,
            'receivers' => array_map(fn(CreateOrderReceiver $receiver): array => array_filter($receiver->all()), $receivers),
            'unfreeze_unsplit' => $unfreeze_unsplit
        ]));

        return $this->getResultByResponse($response);
    }

    /**
     * 查询分账结果
     * - 发起分账请求后，可调用此接口查询分账结果
     * - 发起解冻剩余资金请求后，可调用此接口查询解冻剩余资金的结果
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/orders/query-order.html
     * @param string $transaction_id 微信订单号[查询参数]
     * @param string $out_order_no 商户分账单号[路径参数]
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function queryOrder(string $transaction_id, string $out_order_no): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->get("v3/profitsharing/orders/{$out_order_no}", [
            'query' => ['transaction_id' => $transaction_id]
        ]);

        return $this->getResultByResponse($response);
    }

    /**
     * 请求分账回退
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/return-orders/create-return-order.html
     * @param array $params
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createReturnOrder(array $params): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/profitsharing/return-orders', array_filter($params));

        return $this->getResultByResponse($response);
    }

    /**
     * 查询分账回退结果
     * - 商户需要核实回退结果，可调用此接口查询回退结果。
     * - 如果分账回退接口返回状态为处理中，可调用此接口查询回退结果
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/return-orders/query-return-order.html
     * @param string $out_return_no 商户回退单号[路径参数]
     * @param string $out_order_no 商户分账单号[查询参数]
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function queryReturnOrder(string $out_return_no, string $out_order_no): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->get("v3/profitsharing/orders/{$out_return_no}", [
            'query' => ['out_order_no' => $out_order_no]
        ]);

        return $this->getResultByResponse($response);
    }

    /**
     * 解冻剩余资金
     * - 调用分账接口后，需要解冻剩余资金时，调用本接口将剩余的分账金额全部解冻给本商户
     * - 此接口采用异步处理模式，即在接收到商户请求后，优先受理请求再异步处理，最终的分账结果可以通过查询分账接口获取
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/orders/unfreeze-order.html
     * @param string $transaction_id 微信订单号
     * @param string $out_order_no 商户分账单号
     * @param string $description 分账描述，分账账单中需要体现
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function unfreezeOrder(string $transaction_id, string $out_order_no, string $description): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/profitsharing/orders/unfreeze', [
            'transaction_id' => $transaction_id,
            'out_order_no' => $out_order_no,
            'description' => $description
        ]);

        return $this->getResultByResponse($response);
    }

    /**
     * 查询剩余待分金额
     * - 可调用此接口查询订单剩余待分金额
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/transactions/query-order-amount.html
     * @param string $transaction_id 微信订单号[路径参数]
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function queryOrderAmount(string $transaction_id): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->get("v3/profitsharing/transactions/{$transaction_id}/amounts");

        return $this->getResultByResponse($response);
    }

    /**
     * 添加分账接收方
     * - 商户发起添加分账接收方请求，建立分账接收方列表。后续可通过发起分账请求，将分账方商户结算后的资金，分到该分账接收方
     * @link  https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/receivers/add-receiver.html
     * @param ReceiverTypeEnums $typeEnums 接收方类型
     * @param string $account 接收方账号
     * @param RelationTypeEnums $relationTypeEnums 与分账方的关系类型
     * @param string $name 分账接收方全称
     * @param string $custom_relation 自定义的分账关系
     * @return array
     * @throws TransportExceptionInterface
     * @throws BadResponseException
     * @throws ErrorException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function addReceiver(ReceiverTypeEnums $typeEnums, string $account, RelationTypeEnums $relationTypeEnums, string $name = '', string $custom_relation = ''): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/profitsharing/receivers/add', array_filter([
            'appid' => $this->getConfig()->get('app_id'),
            'type' => $typeEnums->name,
            'account' => $account,
            'name' => $name,
            'relation_type' => $relationTypeEnums->name,
            'custom_relation' => $custom_relation,
        ]));

        return $this->getResultByResponse($response);
    }

    /**
     * 删除分账接收方
     * - 删除后，不支持将分账方商户结算后的资金，分到该分账接收方
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/receivers/delete-receiver.html
     * @param ReceiverTypeEnums $typeEnums
     * @param string $account
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deleteReceiver(ReceiverTypeEnums $typeEnums, string $account): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/profitsharing/receivers/delete', array_filter([
            'appid' => $this->getConfig()->get('app_id'),
            'type' => $typeEnums->name,
            'account' => $account,
        ]));

        return $this->getResultByResponse($response);
    }

    /**
     * 申请分账账单
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/bill-shipment/split-bill.html
     * @param string $bill_date 【账单日期】格式YYYY-MM-DD。仅支持三个月内的账单下载申请。
     * @param string $tar_type 【压缩类型】不填则以不压缩的方式返回数据流，可选值 GZIP
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function bills(string $bill_date, string $tar_type = ''): array
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->get('v3/profitsharing/bills', [
            'query' => array_filter([
                'bill_date' => $bill_date,
                'tar_type' => $tar_type,
            ]),
        ]);

        return $this->getResultByResponse($response);
    }

    /**
     * 下载账单
     * - 下载账单API为通用接口，交易/资金账单都可以通过该接口获取到对应的账单。
     * @link https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/download-bill.html
     * @param string $download_url
     * @param string $hash_type
     * @param string $hash_value
     * @return string
     * @throws ClientExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function downloadBill(string $download_url, string $hash_type, string $hash_value): string
    {
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->get($download_url);
        if ($response->isFailed()) {
            throw new ErrorException('微信支付请求错误');
        }

        $content = $response->getContent();
        if (!in_array(strtolower($hash_type), ['sha1', 'md5'], true)) {
            throw new \InvalidArgumentException("未适配hash_type的摘要算法{$hash_type}，请联系开发者");
        }

        $hash = call_user_func_array(strtolower($hash_type), [$content]);
        if (!hash_equals($hash_value, $hash)) {
            throw new \InvalidArgumentException('账单文件哈希值校验失败');
        }

        return $content;
    }
}
