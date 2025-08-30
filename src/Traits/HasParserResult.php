<?php

namespace Ledc\EasyWechat\Traits;

use EasyWeChat\Kernel\HttpClient\Response;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as ResponseInterfaceAlias;

/**
 * 解析结果
 */
trait HasParserResult
{
    /**
     * 解析结果
     * @param Response|ResponseInterfaceAlias $response
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    final protected function parserResult(Response|ResponseInterfaceAlias $response): array
    {
        if ($response->isFailed()) {
            throw new RuntimeException($response->getContent());
        }

        $result = json_decode($response->getContent(), true);
        $errcode = $result['errcode'] ?? -1;
        $errmsg = $result['errmsg'] ?? '';
        if (0 === $errcode) {
            return $result;
        }
        throw new RuntimeException($errmsg, $errcode);
    }
}