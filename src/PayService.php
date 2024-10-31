<?php

namespace Ledc\EasyWechat;

use EasyWeChat\Kernel\Exceptions\BadResponseException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException as KernelInvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\Pay\Application;
use Error;
use ErrorException;
use Exception;
use InvalidArgumentException;
use Ledc\EasyWechat\Enums\TerminalEnum;
use Ledc\EasyWechat\Traits\InteractCommon;
use Ledc\EasyWechat\Utils as LedcUtils;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/**
 * 支付服务
 */
readonly class PayService
{
    use InteractCommon;

    /**
     * 支付品牌
     */
    public const string PAY_TYPE = 'wechat';
    /**
     * 终端支付渠道
     * @var TerminalEnum
     */
    protected TerminalEnum $terminal;

    /**
     * 构造函数
     * @param TerminalEnum $terminal 终端支付渠道
     * @throws InvalidConfigException
     * @throws KernelInvalidArgumentException
     */
    public function __construct(TerminalEnum $terminal)
    {
        $this->terminal = $terminal;
        $this->app = static::application($terminal);
        $this->config = $this->app->getConfig();
        $this->client = $this->app->getClient();
        $this->utils = $this->app->getUtils();
        $this->merchant = $this->app->getMerchant();
    }

    /**
     * 创建EasyWeChat微信支付实例（始终创建新实例）
     * @param TerminalEnum|null $terminal 终端支付渠道
     * @return Application EasyWeChat微信支付实例
     * @throws InvalidConfigException
     * @throws KernelInvalidArgumentException
     */
    public static function application(TerminalEnum $terminal = null): Application
    {
        $config = PayConfigService::getPayConfig($terminal);

        $app = new Application($config);
        $app->setValidator(new Validator($app->getMerchant()));
        return $app;
    }

    /**
     * 统一支付入口
     * @param string $attach 业务的附加数据
     * @param array $order 订单数据
     * @return array
     * @throws ErrorException
     */
    public function pay(string $attach, array $order): array
    {
        try {
            $result = match ($this->getTerminal()) {
                TerminalEnum::wechat, TerminalEnum::routine => $this->jsapiPay($attach, $order),
                TerminalEnum::h5 => $this->h5Pay($attach, $order),
                TerminalEnum::pc => $this->nativePay($attach, $order),
                default => $this->appPay($attach, $order)
            };

            return [
                'config' => $result,
                'pay_type' => static::PAY_TYPE,
                'pay_channel' => $this->getTerminal()->name,
            ];
        } catch (Error|Exception|Throwable $exception) {
            throw new ErrorException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * JSAPI下单
     * - 商户系统先调用该接口在微信支付服务后台生成预支付交易单，返回正确的预支付交易会话标识后再按Native、JSAPI、APP等不同场景生成交易串调起支付。
     * @param string $attach
     * @param array $order
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadResponseException
     * @throws DecodingExceptionInterface|ErrorException|Exception
     */
    public function jsapiPay(string $attach, array $order): array
    {
        $app_id = $this->getAppId($order);
        /** @var Response|ResponseInterface $response */
        $response = $this->getClient()->postJson('v3/pay/transactions/jsapi', [
            'appid' => $app_id,
            'mchid' => $this->getMerchantId(),
            'description' => $order['description'],
            'out_trade_no' => $order['out_trade_no'],
            'notify_url' => $this->getConfig()->get('notify_url'),
            'amount' => [
                'total' => (int)bcmul((string)$order['amount'], '100'),
            ],
            'payer' => [
                'openid' => $order['openid']
            ],
            'attach' => $attach,
            'settle_info' => [
                'profit_sharing' => (bool)($order['profit_sharing'] ?? false),
            ]
        ]);

        $result = $this->getResultByResponse($response);

        return $this->getUtils()->buildBridgeConfig($result['prepay_id'], $app_id);
    }

    /**
     * Native下单
     * - 通过本接口来生成支付链接参数code_url，然后将该参数值生成二维码图片展示给用户。用户在使用微信客户端扫描二维码后，可以直接跳转到微信支付页面完成支付操作。
     * @param string $attach
     * @param array $order
     * @return string
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function nativePay(string $attach, array $order): string
    {
        $app_id = $this->getAppId($order);
        $response = $this->getClient()->postJson('v3/pay/transactions/native', [
            'appid' => $app_id,
            'mchid' => $this->getMerchantId(),
            'description' => $order['description'],
            'out_trade_no' => $order['out_trade_no'],
            'notify_url' => $this->getConfig()->get('notify_url'),
            'amount' => [
                'total' => (int)bcmul((string)$order['amount'], '100'),
            ],
            'attach' => $attach,
            'settle_info' => [
                'profit_sharing' => (bool)($order['profit_sharing'] ?? false),
            ]
        ]);

        $result = $this->getResultByResponse($response);

        return $result['code_url'];
    }

    /**
     * H5下单
     * - 商户系统先调用该接口在微信支付服务后台生成预支付交易单，返回正确的预支付交易会话标识后再按Native、JSAPI、APP等不同场景生成交易串调起支付。
     * @param string $attach
     * @param array $order
     * @return string
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function h5Pay(string $attach, array $order): string
    {
        $app_id = $this->getAppId($order);
        $response = $this->getClient()->postJson('v3/pay/transactions/h5', [
            'appid' => $app_id,
            'mchid' => $this->getMerchantId(),
            'description' => $order['description'],
            'out_trade_no' => $order['out_trade_no'],
            'notify_url' => $this->getConfig()->get('notify_url'),
            'amount' => [
                'total' => (int)bcmul((string)$order['amount'], '100'),
            ],
            'attach' => $attach,
            'scene_info' => [
                'payer_client_ip' => request()->getRealIp(),
                'h5_info' => [
                    'type' => 'Wap',
                ]
            ],
            'settle_info' => [
                'profit_sharing' => (bool)($order['profit_sharing'] ?? false),
            ]
        ]);

        $result = $this->getResultByResponse($response);

        if (empty($order['redirect_url'])) {
            return $result['h5_url'];
        } else {
            return $result['h5_url'] . '&redirect_url=' . urlencode($order['redirect_url']);
        }
    }

    /**
     * APP下单
     * @param string $attach
     * @param array $order
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function appPay(string $attach, array $order): array
    {
        $app_id = $this->getAppId($order);
        $response = $this->getClient()->postJson('v3/pay/transactions/app', [
            'appid' => $app_id,
            'mchid' => $this->getMerchantId(),
            'description' => $order['description'],
            'out_trade_no' => $order['out_trade_no'],
            'notify_url' => $this->getConfig()->get('notify_url'),
            'amount' => [
                'total' => (int)bcmul((string)$order['amount'], '100'),
            ],
            'attach' => $attach,
            'settle_info' => [
                'profit_sharing' => (bool)($order['profit_sharing'] ?? false),
            ]
        ]);

        $result = $this->getResultByResponse($response);

        return $this->getUtils()->buildAppConfig($result['prepay_id'], $app_id);
    }

    /**
     * 下载微信支付的平台证书
     * - https://pay.weixin.qq.com/docs/merchant/apis/platform-certificate/api-v3-get-certificates/get.html
     * @param bool $verifySignature 是否验证签名
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws KernelInvalidArgumentException
     */
    public function certificates(bool $verifySignature = true): array
    {
        $response = $this->getClient()->get('v3/certificates');

        $result = $this->getResultByResponse($response, $verifySignature);
        if (empty($result['data'])) {
            throw new ErrorException('【下载微信支付平台证书】响应错误：' . ($result['code'] ?? '') . '-' . ($result['message'] ?? ''));
        }

        // 解密微信支付平台证书
        foreach ($result['data'] as &$cert) {
            $cert['platform_cert_pem'] = LedcUtils::decryptCertificate($cert['encrypt_certificate'], $this->getMerchant()->getSecretKey());
        }

        return $result;
    }

    /**
     * 【获取】终端支付渠道
     * @return TerminalEnum
     */
    public function getTerminal(): TerminalEnum
    {
        return $this->terminal;
    }

    /**
     * 【获取】支付使用的app_id
     * @param array $order
     * @return string|null
     */
    public function getAppId(array $order = []): ?string
    {
        $app_id = $order['app_id'] ?? $this->getConfig()->get('app_id');
        if (empty($app_id)) {
            throw new InvalidArgumentException('配置、订单数据都未找到app_id');
        }
        return $app_id;
    }
}
