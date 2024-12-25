<?php

namespace Ledc\EasyWechat\OfficialAccount;

use EasyWeChat\OfficialAccount\Message;

/**
 * 微信公众号：用户扫描带参数二维码
 */
class QrSceneRocket
{
    /**
     * 构造函数
     * @param string $scene_id 二维码场景值
     * @param string $ticket 二维码的ticket
     * @param Message $message EasyWechat的消息
     */
    public function __construct(
        public readonly string $scene_id,
        public readonly string $ticket,
        public Message         $message
    )
    {
    }
}
