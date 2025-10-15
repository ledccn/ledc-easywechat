<?php

namespace Ledc\EasyWechat;

use Closure;
use EasyWeChat\Kernel\Contracts\Server as ServerInterface;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Pay\Application;
use EasyWeChat\Pay\Message;
use EasyWeChat\Pay\Server;
use Exception;
use Ledc\EasyWechat\Enums\EventEnum;
use Psr\Http\Message\ResponseInterface;
use support\Log;
use support\Request;
use support\Response;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Throwable;

/**
 * 支付通知API
 * - 微信支付通过支付通知接口将用户支付成功消息通知给商户
 * - https://easywechat.com/6.x/pay/server.html
 */
class PayNotifyService
{
    /**
     * 调试模式
     * @var bool
     */
    public static bool $debug = true;

    /**
     * 微信支付回调
     * @param Request $request
     * @return Response
     */
    public static function handle(Request $request): Response
    {
        try {
            $app = PayService::application();
            $symfony_request = new SymfonyRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->rawBody());
            $symfony_request->headers = new HeaderBag($request->header());
            $app->setRequestFromSymfonyRequest($symfony_request);
            /** @var Server|ServerInterface $server */
            $server = $app->getServer();

            // 使用微信平台证书，验证签名
            try {
                $app->getValidator()->validate($app->getRequest());
            } catch (Exception|Throwable $e) {
                throw new InvalidArgumentException('验证签名失败');
            }

            // 微信支付：自定义处理所有事件消息
            $responses = EventEnum::wechat_pay_any->dispatch($server->getRequestMessage());
            foreach ($responses as $response) {
                if ($response instanceof Response) {
                    return $response;
                }
            }

            // 处理微信支付的回调通知
            static::notify($server, $app);

            // 默认返回 ['code' => 'SUCCESS', 'message' => '成功']
            /** @var \Nyholm\Psr7\Response|ResponseInterface $response */
            $response = $server->serve();

            return new Response(
                $response->getStatusCode(),
                $response->getHeaders(),
                $response->getBody()->getContents()
            );
        } catch (Throwable $throwable) {
            Log::error('[' . __METHOD__ . '][微信支付回调通知] 异常：' . $throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString());
            $data = ['code' => 'FAIL', 'message' => $throwable->getMessage()];

            return new Response(
                400,
                ['Content-Type' => 'application/json'],
                json_encode($data, JSON_UNESCAPED_UNICODE)
            );
        }
    }

    /**
     * 支付通知API
     * @param Server|ServerInterface $server
     * @param Application $app
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function notify(Server|ServerInterface $server, Application $app): void
    {
        static::handlePaySuccess($server, $app);
        static::handleRefunded($server, $app);

        /**
         * 其它事件处理
         * - 以上便捷方法都只处理了成功状态，其它状态，可以通过自定义事件处理中间件的形式处理
         */
        $server->with(function (Message $message, Closure $next) use ($app) {
            if (static::$debug) {
                Log::debug('[微信支付回调通知][其它事件处理]' . $message->toJson());
            }

            $event_type = $message->getEventType();
            // $message->event_type 事件类型
            return $next($message);
        });
    }

    /**
     * 支付成功事件
     * - https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_5.shtml
     * @param Server|ServerInterface $server
     * @param Application $app
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function handlePaySuccess(Server|ServerInterface $server, Application $app): void
    {
        $server->handlePaid(function (Message $message, Closure $next) use ($app) {
            if (static::$debug) {
                Log::debug('[支付成功事件]' . $message->toJson());
            }
            // 调度事件
            EventEnum::wechat_pay_success->dispatch($message);

            // $message->out_trade_no 获取商户订单号
            // $message->payer['openid'] 获取支付者 openid
            // 建议是拿订单号调用微信支付查询接口，以查询到的订单状态为准

            return $next($message);
        });
    }

    /**
     * 退款成功事件
     * - https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_11.shtml
     * @param Server|ServerInterface $server
     * @param Application $app
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function handleRefunded(Server|ServerInterface $server, Application $app): void
    {
        $server->handleRefunded(function (Message $message, Closure $next) use ($app) {
            if (static::$debug) {
                Log::debug('[退款成功事件]' . $message->toJson());
            }
            // 调度事件
            EventEnum::wechat_pay_refunded->dispatch($message);

            return $next($message);
        });
    }
}
