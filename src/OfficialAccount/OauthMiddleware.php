<?php

namespace Ledc\EasyWechat\OfficialAccount;

use Exception;
use Ledc\EasyWechat\Enums\EventEnum;
use Ledc\EasyWechat\WechatService;
use Overtrue\Socialite\Providers\WeChat;
use Overtrue\Socialite\User;
use support\Log;
use support\Request;
use support\Response;
use Throwable;
use Webman\Event\Event;
use Webman\MiddlewareInterface;

/**
 * 微信公众号网页授权登录中间件
 */
class OauthMiddleware implements MiddlewareInterface
{
    /**
     * 微信网页授权登录成功后，重定向到目标页面
     */
    protected const string OAUTH_SUCCESSFUL_REDIRECT = 'OAUTH_SUCCESSFUL_REDIRECT';

    /**
     * 构造函数
     * @param array $excludedApps 排除的应用
     */
    public function __construct(protected array $excludedApps = [])
    {
    }

    /**
     * @param \Webman\Http\Request|Request $request
     * @param callable $handler
     * @return Response
     * @throws Exception
     */
    public function process(\Webman\Http\Request|Request $request, callable $handler): Response
    {
        // 当前请求的应用属于排除列表，则忽略
        if (in_array($request->app, $this->excludedApps)) {
            return $handler($request);
        }

        if (static::isWechat() && empty(session('user.id'))) {
            try {
                $uri = $request->uri();

                /** @var WeChat $oauth */
                $oauth = WechatService::instance($request)->getOAuth();
                $redirectUrl = $oauth->withState(md5($request->sessionId()))->redirect();

                OauthMiddleware::setOauthSuccessfulRedirectUri($uri);

                return redirect($redirectUrl);
            } catch (Throwable $throwable) {
                Log::error('[微信网页授权中间件]异常：' . $throwable->getMessage());
            }
        }

        return $handler($request);
    }

    /**
     * 判断是否通过微信客户端访问
     * @return bool
     */
    public static function isWechat(): bool
    {
        return str_contains(request()->header('user-agent', ''), 'MicroMessenger');
    }

    /**
     * 【获取】微信网页授权登录成功后，重定向到目标页面
     * @return string|null 返回uri，包括path和queryString部分。
     * @throws Exception
     */
    public static function getOauthSuccessfulRedirectUri(): ?string
    {
        return request()->session()->get(static::OAUTH_SUCCESSFUL_REDIRECT);
    }

    /**
     * 【设置】微信网页授权登录成功后，重定向到目标页面
     * @param string $uri uri，包括path和queryString部分。
     * @return void
     * @throws Exception
     */
    public static function setOauthSuccessfulRedirectUri(string $uri): void
    {
        request()->session()->set(static::OAUTH_SUCCESSFUL_REDIRECT, $uri);
    }

    /**
     * 微信公众号OAuth授权完成后的回调页地址
     * @param Request $request
     * @return Response
     */
    public static function redirect(Request $request): Response
    {
        try {
            $state = $request->get('state');
            $code = $request->get('code');
            if (!hash_equals(md5($request->sessionId()), $state)) {
                return \response('Oauth2的State参数无效！');
            }

            $target = $request->get('target', static::getOauthSuccessfulRedirectUri());
            $target = empty($target) || str_starts_with($target, '/app/user/login') ? '/app/user' : $target;

            $app = WechatService::instance($request);
            /** @var WeChat $oauth */
            $oauth = $app->getOAuth();
            /** @var User $user */
            $user = $oauth->userFromCode($code);

            Event::dispatch(EventEnum::wechat_account_oauth_successful->value, [$user, $app]);

            return redirect($target);
        } catch (Throwable $throwable) {
            return \response('FAIL:' . $throwable->getMessage());
        }
    }
}
