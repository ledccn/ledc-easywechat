<?php

namespace Ledc\EasyWechat\OfficialAccount;

use Exception;
use Ledc\EasyWechat\Enums\EventEnum;
use Ledc\EasyWechat\WechatService;
use Overtrue\Socialite\Providers\WeChat;
use Overtrue\Socialite\User;
use ReflectionClass;
use ReflectionException;
use support\Request as SupportRequest;
use support\Response as SupportResponse;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 微信公众号网页授权登录中间件
 */
class OauthMiddleware implements MiddlewareInterface
{
    /**
     * 无需登录的方法
     * - 路由传参数或控制器属性.
     */
    public const string noNeedLogin = 'noNeedLogin';

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
     * @param Request|SupportRequest $request
     * @param callable $handler
     * @return Response|SupportResponse
     */
    public function process(Request|SupportRequest $request, callable $handler): Response|SupportResponse
    {
        // 当前请求的应用属于排除列表，则忽略
        if (in_array($request->app, $this->excludedApps)) {
            return $handler($request);
        }

        // OPTIONS请求，直接返回
        if ('OPTIONS' === $request->method()) {
            return response('');
        }

        try {
            $controller = $request->controller;
            $action = $request->action;
            $route = $request->route;

            // 401是未登录时固定返回码
            $code = 401;
            $msg = '请登录';

            /**
             * 无控制器信息说明是函数调用，函数不属于任何控制器，鉴权操作应该在函数内部完成。
             */
            if ($controller) {
                // 获取控制器鉴权信息
                $class = new ReflectionClass($controller);
                $properties = $class->getDefaultProperties();
                $noNeedLogin = $properties[self::noNeedLogin] ?? [];
                // 判断是否跳过登录验证
                if ('*' === $noNeedLogin || in_array('*', $noNeedLogin, true) || in_array($action, $noNeedLogin, true)) {
                    // 不需要登录
                    return $handler($request);
                }
            } else {
                // 默认路由 $request->route为null，所以需要判断 $request->route 是否为空
                if (!$route) {
                    return $handler($request);
                }

                // 路由参数
                if ($route->param(self::noNeedLogin)) {
                    // 指定路由不用登录
                    return $handler($request);
                }
            }

            // 判断是否已登录
            if (session('user') && session('user.id')) {
                return $handler($request);
            }

            // 支持JSON返回格式
            if ($request->expectsJson()) {
                return json(['code' => $code, 'msg' => $msg, 'data' => []]);
            } else {
                if (static::isWechat()) {
                    $uri = $request->uri();

                    /** @var WeChat $oauth */
                    $oauth = WechatService::instance($request)->getOAuth();
                    $redirectUrl = $oauth->withState(md5($request->sessionId()))->redirect();

                    static::setOauthSuccessfulRedirectUri($uri);

                    return redirect($redirectUrl);
                } else {
                    return redirect('/app/user/login');
                }
            }
        } catch (ReflectionException $exception) {
            $msg = '控制器不存在';
            $code = 404;
        } catch (Throwable $throwable) {
            $msg = $throwable->getMessage();
            $code = 500;
        }

        // 支持JSON返回格式
        if ($request->expectsJson()) {
            $response = json(['code' => $code, 'msg' => $msg, 'data' => []]);
        } else {
            $response = \response($msg, $code);
        }

        return $response;
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
     * 获取微信公众号网页授权地址
     * - 前后分离使用
     * - 前端可以自由定义授权成功后跳转到的目标页面
     * @param Request|SupportRequest $request
     * @return SupportResponse
     */
    public static function getOauth2AuthorizeURL(Request|SupportRequest $request): SupportResponse
    {
        try {
            $target = $request->get('target');
            if (empty($target)) {
                $target = $request->uri();
            }

            /** @var WeChat $oauth */
            $oauth = WechatService::instance($request)->getOAuth();
            $redirect_url = $oauth->getConfig()->get('redirect_url');
            $state = md5($request->sessionId());
            $redirect = $oauth->withState($state)->redirect($redirect_url . '?target=' . urlencode($target));

            static::setOauthSuccessfulRedirectUri($target);

            // 客户端拿到响应数据后，可以构造携带更多参数的重定向链接；也可以直接重定向至redirect的值。
            $data = [
                'appid' => $oauth->getClientId(),
                'redirect_url' => $redirect_url,
                'state' => $state,
                'redirect' => $redirect
            ];

            return json(['code' => 0, 'data' => $data, 'msg' => 'ok']);
        } catch (Throwable $e) {
            return json(['code' => $e->getCode() ?: 1, 'data' => [], 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 微信公众号OAuth授权完成后的回调页地址
     * @param Request|SupportRequest $request
     * @return SupportResponse
     */
    public static function redirect(Request|SupportRequest $request): SupportResponse
    {
        try {
            $state = $request->get('state');
            $code = $request->get('code');
            if (empty($code) || empty($state)) {
                return response('code或state参数必填');
            }

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

            // 触发微信授权成功事件
            EventEnum::wechat_account_oauth_successful->dispatch([$user, $app]);

            // 前后端分离：支持JSON返回格式
            if ($request->get('json') || $request->acceptJson()) {
                $token = $request->sessionId();
                return json(['code' => 0, 'data' => compact('token'), 'msg' => 'ok']);
            }

            return redirect($target);
        } catch (Throwable $throwable) {
            return \response('FAIL:' . $throwable->getMessage());
        }
    }
}
