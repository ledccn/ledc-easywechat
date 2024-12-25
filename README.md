# 基于EasyWechat的webman支付插件

## 安装
`composer require ledc/easywechat`

## 使用

开箱即用，只需要传入一个配置即可：

```php
use Ledc\EasyWechat\PayConfigManager;
use Ledc\EasyWechat\Enums\TerminalEnum;

// 可在Bootstrap，全局注入配置
PayConfigManager::set(function (TerminalEnum $terminalEnum) {
    // TODO... 从数据库读取配置，然后返回
    return [];
});
```

在创建实例后，所有的方法都可以有IDE自动补全；例如：

```php
use Ledc\EasyWechat\Enums\TerminalEnum;
use Ledc\EasyWechat\PayService;

// 用户支付终端
$terminal = $request->post('terminal');
$terminalEnum = TerminalEnum::from((int)$terminal);

$payService = new PayService($terminalEnum);

$attach = '业务附加数据（微信支付成功后，原样返回）';
// 待支付的订单数据（可以看微信官方文档或跟踪进pay方法查看参数，按实际需要的传）
$order = [
    'description' => '购买会员',
    'out_trade_no' => uniqid(),
    'amount' => 1,
];

// 统一支付
$result = $payService->pay($attach, $order);
```

## 微信支付回调URL

`https://您的域名/wechat/pay/callback`

具体处理逻辑在：

`\Ledc\EasyWechat\PayNotifyService::handle`

### 处理支付回调的最佳实践：

注册webman事件，监听 `\Ledc\EasyWechat\Enums\EventEnum` 的三个事件即可；

修改 `config/event.php` ，加入：

```php
use Ledc\EasyWechat\Enums\EventEnum;

return [
    // 微信支付回调通知：所有事件消息
    EventEnum::wechat_pay_any->value => [],
    // 微信支付回调通知：支付成功事件
    EventEnum::wechat_pay_success->value => [
        [WechatPaySuccessListener::class, 'handle'],
    ],
    // 微信支付回调通知：退款成功事件
    EventEnum::wechat_pay_refunded->value => [],
];
```

## 微信公众号消息回调URL

`https://您的域名/wechat/account/callback`

具体处理逻辑在：

`\Ledc\EasyWechat\WechatService::handle`

## 微信公众号网页授权登录授权完成后，重定向URL

`https://您的域名/wechat/account/oauth/redirect`

具体处理逻辑在：

`\Ledc\EasyWechat\OfficialAccount\OauthMiddleware::redirect`

注：使用微信公众号网页授权时，请在中间件配置中，添加此中间件

`\Ledc\EasyWechat\OfficialAccount\OauthMiddleware::class`

## 二次开发


## 捐赠

![reward](reward.png)
