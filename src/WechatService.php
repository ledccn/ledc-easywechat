<?php

namespace Ledc\EasyWechat;

use Closure;
use EasyWeChat\Kernel\Contracts\Config as ConfigInterface;
use EasyWeChat\Kernel\Contracts\Server as ServerInterface;
use EasyWeChat\OfficialAccount\Application;
use EasyWeChat\OfficialAccount\Message;
use EasyWeChat\OfficialAccount\Server;
use InvalidArgumentException;
use Ledc\EasyWechat\Contracts\WechatConfig;
use Ledc\EasyWechat\Enums\EventEnum;
use Ledc\EasyWechat\OfficialAccount\MsgTypeEnum;
use Ledc\EasyWechat\OfficialAccount\MsgTypeEventEnum;
use Ledc\EasyWechat\OfficialAccount\QrSceneRocket;
use Ledc\EasyWechat\OfficialAccount\Rocket;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use support\Cache;
use support\Redis;
use support\Request;
use support\Response;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

/**
 * 微信公众号服务
 */
class WechatService
{
    /**
     * 获取微信公众号配置的接口实现
     * @var WechatConfig|null
     */
    public static ?WechatConfig $wechatConfig = null;

    /**
     * 【获取】微信公众号配置
     * @param int|string|\Webman\Http\Request|Request|null $key 配置标识
     * @return array|ConfigInterface
     */
    final public static function getWechatConfig(int|string|\Webman\Http\Request|Request|null $key = null): array|ConfigInterface
    {
        if (is_null(self::$wechatConfig)) {
            throw new InvalidArgumentException('缺少获取微信公众号配置的实例');
        }

        return self::$wechatConfig->get($key);
    }

    /**
     * 【设置】微信公众号配置
     * @param WechatConfig $config
     * @return void
     */
    final public static function setWechatConfig(WechatConfig $config): void
    {
        self::$wechatConfig = $config;
    }

    /**
     * 获取Application实例
     * @param int|string|\Webman\Http\Request|Request|null $key 配置标识
     * @param string|null $name 缓存驱动标识
     * @return Application
     */
    final public static function instance(int|string|\Webman\Http\Request|Request|null $key = null, ?string $name = null): Application
    {
        try {
            $app = new Application(static::getWechatConfig($key));
            $app->setCache(Cache::store($name));
            return $app;
        } catch (Throwable $throwable) {
            throw new RuntimeException($throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * 创建自定义菜单
     * @param Application $app
     * @param array $menu 菜单
     * @param array $match_rule 个性化菜单匹配规则
     * @return \EasyWeChat\Kernel\HttpClient\Response|\Symfony\Contracts\HttpClient\ResponseInterface
     * @throws TransportExceptionInterface
     */
    final public static function menuCreate(Application $app, array $menu, array $match_rule = []): \Symfony\Contracts\HttpClient\ResponseInterface|\EasyWeChat\Kernel\HttpClient\Response
    {
        $api = $app->getClient();
        if (empty($match_rule)) {
            return $api->postJson('/cgi-bin/menu/create', ['button' => $menu]);
        }

        return $api->postJson('/cgi-bin/menu/addconditional', ['button' => $menu, 'matchrule' => $match_rule]);
    }

    /**
     * 获取用户基本信息
     * - 认证服务号才有权限调用此接口
     * @param Application $app
     * @param string $openid 普通用户的标识，对当前公众号唯一
     * @return array
     */
    final public static function getUserInfo(Application $app, string $openid): array
    {
        try {
            $api = $app->getClient();
            $options = [
                'query' => [
                    'openid' => $openid,
                ]
            ];
            $response = $api->get('/cgi-bin/user/info', $options)->throw(false);
            if ($response->isSuccessful()) {
                return $response->toArray();
            }

            $resp = json_decode($response->toJson());
            throw new InvalidArgumentException($resp->errmsg ?? '获取用户信息失败', $resp->errcode ?? 400);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException($throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * 微信公众号回调
     * @param Request $request
     * @return Response
     */
    final public static function handle(Request $request): Response
    {
        try {
            $app = static::instance($request);
            $symfony_request = new SymfonyRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->rawBody());
            $symfony_request->headers = new HeaderBag($request->header());
            $app->setRequestFromSymfonyRequest($symfony_request);

            $server = $app->getServer();

            static::hookServerListener($server, $app);

            /** @var \Nyholm\Psr7\Response|ResponseInterface $response */
            $response = $server->serve();

            return new Response(
                $response->getStatusCode(),
                $response->getHeaders(),
                $response->getBody()->getContents()
            );
        } catch (Throwable $throwable) {
            return new Response(200, [], 'success');
        }
    }

    /**
     * @param Server|ServerInterface $server
     * @param Application $app
     * @return void
     * @throws Throwable
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected static function hookServerListener(Server|ServerInterface $server, Application $app): void
    {
        EventEnum::wechat_account_callback->emit(new Rocket($server, $app));

        /**
         * 【处理普通消息】注册指定消息类型的消息处理器
         * - 参数 1 为消息类型，也就是 message 中的 MsgType 字段，例如：text
         * - 参数 2 是中间件
         */
        $server->addMessageListener(MsgTypeEnum::text->value, function (Message $message, Closure $next) {
            // 将消息转发到客服
            $cacheKey = MsgTypeEnum::getTransferCustomerServiceKey($message->FromUserName);
            if (Redis::exists($cacheKey)) {
                Redis::del($cacheKey);
                return MsgTypeEnum::transferCustomerService();
            }

            return $next($message);
        });

        // 【处理普通消息】事件推送
        $server->addMessageListener(MsgTypeEnum::event->value, function (Message $message, Closure $next) {
            return $next($message);
        });

        /**
         * 【处理事件消息】注册指定消息类型的消息处理器
         * - 参数 1 为事件类型，也就是 message 中的 Event 字段，例如：subscribe
         * - 参数 2 是中间件
         */
        $server->addEventListener(MsgTypeEventEnum::subscribe->value, [static::class, 'handlerSubscribeEventListener']);
        $server->addEventListener(MsgTypeEventEnum::SCAN->value, [static::class, 'handlerSubscribeEventListener']);

        // 处理 自定义菜单事件
        $server->addEventListener(MsgTypeEventEnum::CLICK->value, function (Message $message, Closure $next) {
            if (in_array($message['EventKey'], ['service', 'help'], true)) {
                Redis::setEx(MsgTypeEnum::getTransferCustomerServiceKey($message->FromUserName), 120, time());
                return "请在公众号的输入框内，详细描述您要咨询的问题，您的问题将转发至人工客服。";
            } else {
                return $next($message);
            }
        });

        // 处理 取消关注事件
        $server->addEventListener(MsgTypeEventEnum::unsubscribe->value, function (Message $message, Closure $next) {
            EventEnum::wechat_account_unsubscribe->emit($message);
            return $next($message);
        });

        /**
         * 注册通用中间件
         */
        $server->with(function (Message $message, Closure $next) {
            return $next($message);
        });
    }

    /**
     * 处理关注事件
     * @param Message $message
     * @param Closure $next
     * @return mixed|string
     */
    public static function handlerSubscribeEventListener(Message $message, Closure $next): mixed
    {
        try {
            EventEnum::wechat_account_subscribe->emit($message);

            // 用户扫码关注公众号时，二维码携带的场景信息
            $rocketQrScene = static::subscribeScene($message);
            if ($rocketQrScene) {
                $response = EventEnum::wechat_account_subscribe_qrscene->dispatch($rocketQrScene, true);
                if (is_string($response)) {
                    return $response;
                }
            }
        } catch (Throwable $throwable) {
            return '处理关注事件异常，请联系开发者';
        }

        return $next($message);
    }

    /**
     * 用户扫码关注公众号时，解析二维码携带的场景信息
     * @param Message $message
     * @return QrSceneRocket|null
     */
    final public static function subscribeScene(Message $message): ?QrSceneRocket
    {
        $rocketQrScene = null;
        //事件KEY值
        $event_key = $message['EventKey'] ?? null;
        //二维码的ticket
        $ticket = $message['Ticket'] ?? null;
        if ($event_key && $ticket) {
            /**
             * 扫描带参数二维码关注
             * - 事件KEY值，qrscene_为前缀,后面为二维码scene_id
             */
            if (str_starts_with($event_key, 'qrscene_')) {
                $scene_id = substr($event_key, strlen('qrscene_'));
            } else {
                $scene_id = $event_key;
            }

            $rocketQrScene = new QrSceneRocket($scene_id, $ticket, $message);
        }
        return $rocketQrScene;
    }
}
