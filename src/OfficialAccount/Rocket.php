<?php

namespace Ledc\EasyWechat\OfficialAccount;

use EasyWeChat\Kernel\Contracts\Server as ServerInterface;
use EasyWeChat\OfficialAccount\Application;
use EasyWeChat\OfficialAccount\Server;

/**
 * 微信公众号回调有效载荷
 */
class Rocket
{
    /**
     * @param Server|ServerInterface $server
     * @param Application $app
     */
    public function __construct(public Server|ServerInterface $server, public Application $app)
    {
    }
}
