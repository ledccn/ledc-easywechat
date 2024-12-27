<?php

namespace Ledc\EasyWechat\OfficialAccount;

use EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient;
use EasyWeChat\Kernel\HttpClient\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * 微信公众号二维码服务
 */
readonly class QRCodeService
{
    public const int DAY = 86400;
    /**
     * 永久二维码创建上限
     */
    public const int SCENE_MAX_VALUE = 100000;
    /**
     * 临时二维码：整形
     */
    public const string SCENE_QR_TEMPORARY = 'QR_SCENE';
    /**
     * 临时二维码：字符串
     */
    public const string SCENE_QR_TEMPORARY_STR = 'QR_STR_SCENE';
    /**
     * 永久二维码：整形
     */
    public const string SCENE_QR_FOREVER = 'QR_LIMIT_SCENE';
    /**
     * 永久二维码：字符串
     */
    public const string SCENE_QR_FOREVER_STR = 'QR_LIMIT_STR_SCENE';
    /**
     * AccessToken客户端
     * @var AccessTokenAwareClient
     */
    protected AccessTokenAwareClient $client;

    /**
     * 构造函数
     */
    public function __construct(AccessTokenAwareClient $client)
    {
        $this->client = $client;
    }

    /**
     * 创建永久二维码
     * - Create forever QR code.
     *
     * @param int|string $sceneValue
     * @return ResponseInterface|Response
     * @throws TransportExceptionInterface
     */
    public function forever(int|string $sceneValue): ResponseInterface|Response
    {
        if (is_int($sceneValue) && $sceneValue > 0 && $sceneValue < self::SCENE_MAX_VALUE) {
            $type = self::SCENE_QR_FOREVER;
            $sceneKey = 'scene_id';
        } else {
            $type = self::SCENE_QR_FOREVER_STR;
            $sceneKey = 'scene_str';
        }
        $scene = [$sceneKey => $sceneValue];

        return $this->create($type, $scene, false);
    }

    /**
     * 创建临时二维码
     * - Create temporary QR code.
     *
     * @param int|string $sceneValue
     * @param int|null $expireSeconds
     * @return Response|ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function temporary(int|string $sceneValue, int $expireSeconds = null): ResponseInterface|Response
    {
        if (is_int($sceneValue) && $sceneValue > 0) {
            $type = self::SCENE_QR_TEMPORARY;
            $sceneKey = 'scene_id';
        } else {
            $type = self::SCENE_QR_TEMPORARY_STR;
            $sceneKey = 'scene_str';
        }
        $scene = [$sceneKey => $sceneValue];

        return $this->create($type, $scene, true, $expireSeconds);
    }

    /**
     * Return url for ticket.
     * Detail: https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542 .
     *
     * @param string $ticket
     * @return string
     */
    public function url(string $ticket): string
    {
        return sprintf('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s', urlencode($ticket));
    }

    /**
     * 创建二维码
     * - Create a QrCode.
     *
     * @param string $actionName
     * @param array $actionInfo
     * @param bool $temporary
     * @param int|null $expireSeconds
     * @return Response|ResponseInterface
     * @throws TransportExceptionInterface
     */
    protected function create(string $actionName, array $actionInfo, bool $temporary = true, int $expireSeconds = null): ResponseInterface|Response
    {
        null !== $expireSeconds || $expireSeconds = 7 * self::DAY;

        $params = [
            'action_name' => $actionName,
            'action_info' => ['scene' => $actionInfo],
        ];

        if ($temporary) {
            $params['expire_seconds'] = min($expireSeconds, 30 * self::DAY);
        }

        return $this->client->postJson('cgi-bin/qrcode/create', $params)->throw(false);
    }
}
