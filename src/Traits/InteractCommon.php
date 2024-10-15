<?php

namespace Ledc\EasyWechat\Traits;

use EasyWeChat\Kernel\Config;
use EasyWeChat\Kernel\Contracts\Config as ConfigInterface;
use EasyWeChat\Kernel\Exceptions\BadResponseException;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\Pay\Application;
use EasyWeChat\Pay\Client;
use EasyWeChat\Pay\Merchant;
use EasyWeChat\Pay\Utils;
use ErrorException;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * 公共属性与方法
 */
trait InteractCommon
{
    /**
     * EasyWeChat微信支付实例
     * @var Application
     */
    protected readonly Application $app;
    /**
     * 配置
     * @var Config|ConfigInterface
     */
    protected readonly Config|ConfigInterface $config;
    /**
     * API客户端（封装了多种模式的 API 调用类）
     * @var Client|HttpClientInterface
     */
    protected readonly Client|HttpClientInterface $client;
    /**
     * 工具（提供各种支付需要的配置生成方法）
     * @var Utils
     */
    protected readonly Utils $utils;
    /**
     * 支付账户（提供一系列 API 获取支付的基本信息）
     * @var Merchant
     */
    protected readonly Merchant $merchant;

    /**
     * 【获取】EasyWeChat微信支付实例
     * @return Application
     */
    public function getApp(): Application
    {
        return $this->app;
    }

    /**
     * 【获取】配置
     * @return Config|ConfigInterface
     */
    public function getConfig(): Config|ConfigInterface
    {
        return $this->config;
    }

    /**
     * 【获取】API客户端
     * @return Client|HttpClientInterface
     */
    public function getClient(): Client|HttpClientInterface
    {
        return $this->client;
    }

    /**
     * 【获取】工具（提供各种支付需要的配置生成方法）
     * @return Utils
     */
    public function getUtils(): Utils
    {
        return $this->utils;
    }

    /**
     * 【获取】支付账户（提供一系列 API 获取支付的基本信息）
     * @return Merchant
     */
    public function getMerchant(): Merchant
    {
        return $this->merchant;
    }

    /**
     * 获取微信支付商户号
     * @return string
     */
    public function getMerchantId(): string
    {
        return (string)$this->getMerchant()->getMerchantId();
    }

    /**
     * 获取响应结果
     * @param Response|ResponseInterface $response
     * @param bool $verifySignature
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ErrorException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function getResultByResponse(Response|ResponseInterface $response, bool $verifySignature = true): array
    {
        if ($response->isFailed()) {
            throw new ErrorException('微信支付请求错误');
        }

        // API返回值的签名验证
        if ($verifySignature) {
            try {
                $this->getApp()->getValidator()->validate($response->toPsrResponse());
                // 验证通过
            } catch (Exception $e) {
                // 验证失败
                throw new ErrorException($e->getMessage(), $e->getCode());
            }
        }

        $result = $response->toArray(true);
        if (!empty($result['code']) || !empty($result['message'])) {
            throw new ErrorException('微信支付错误：' . ($result['code'] ?? '') . '-' . ($result['message'] ?? ''));
        }

        return $result;
    }
}