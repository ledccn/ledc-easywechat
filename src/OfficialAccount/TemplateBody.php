<?php

namespace Ledc\EasyWechat\OfficialAccount;

use InvalidArgumentException;
use JsonSerializable;
use Ledc\EasyWechat\Contracts\TemplateInterface;

/**
 * 微信服务号模板消息基类
 */
abstract class TemplateBody implements TemplateInterface, JsonSerializable
{
    /**
     * 接收者（用户）的 openid
     * @var string
     */
    private string $toUser = '';
    /**
     * 模板消息数据
     * @var array
     */
    private array $data = [];
    /**
     * 模板跳转链接
     * - 海外账号没有跳转能力,url 和 miniprogram 同时不填，无跳转，url 和 miniprogram 同时填写，优先跳转小程序
     * @var string|null
     */
    private ?string $url = null;
    /**
     * 跳转小程序时填写
     * - url 和 miniprogram 同时不填，无跳转，page 和 miniprogram 同时填写，优先跳转小程序
     * @var array|null
     */
    private ?array $miniProgram = null;
    /**
     * 防重入id
     * - 对于同一个openid + client_msg_id, 只发送一条消息,10分钟有效,超过10分钟不保证效果。若无防重入需求，可不填
     * @var string|null
     */
    private ?string $clientMsgId = null;

    /**
     * 获取必需参数列表
     * @return array|string[]
     */
    abstract protected function getRequiredParams(): array;

    /**
     * 构造函数
     * @param string $templateId 模板ID
     */
    final public function __construct(private readonly string $templateId)
    {
    }

    /**
     * 获取模板ID
     * @return string
     */
    final public function getTemplateId(): string
    {
        return $this->templateId;
    }

    /**
     * 接收者（用户）的 openid
     * @return string
     */
    final public function getToUser(): string
    {
        return $this->toUser;
    }

    /**
     * 设置接收者（用户）的 openid
     * @param string $toUser
     * @return static
     */
    final public function setToUser(string $toUser): static
    {
        $this->toUser = $toUser;
        return $this;
    }

    /**
     * 获取模板消息数据
     * @return array
     */
    final public function getData(): array
    {
        return $this->data;
    }

    /**
     * 设置模板消息数据
     * @param array $data
     * @return static
     */
    public function setData(array $data): static
    {
        $result = [];
        foreach ($this->getRequiredParams() as $field) {
            $value = $data[$field] ?? null;
            if (null === $value) {
                throw new InvalidArgumentException("模板参数 {$field} 缺失");
            }

            // 模板参数值，都是字符串
            $result[$field] = is_array($value) || is_object($value) ? $value : ['value' => (string)$value];
        }
        $this->data = $result;
        return $this;
    }

    /**
     * 模板跳转链接
     * @return string|null
     */
    final public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * 设置模板跳转链接
     * @param string $url
     * @return static
     */
    final public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 跳转小程序
     * @return array|null
     */
    final public function getMiniProgram(): ?array
    {
        return $this->miniProgram;
    }

    /**
     * 设置跳转小程序
     * @param string $appid 小程序appid
     * @param string $pagePath 小程序跳转路径
     * @return static
     */
    final public function setMiniProgram(string $appid, string $pagePath): static
    {
        if (empty($appid) || empty($pagePath)) {
            throw new InvalidArgumentException('跳转小程序参数错误');
        }
        $this->miniProgram = [
            'appid' => $appid,
            'pagepath' => $pagePath
        ];
        return $this;
    }

    /**
     * 防重入id
     * @return string|null
     */
    final public function getClientMsgId(): ?string
    {
        return $this->clientMsgId;
    }

    /**
     * 设置防重入id
     * @param string $clientMsgId
     * @return static
     */
    final public function setClientMsgId(string $clientMsgId): static
    {
        $this->clientMsgId = $clientMsgId;
        return $this;
    }

    /**
     * 重置模板消息参数
     * @return static
     */
    final public function reset(): static
    {
        $this->toUser = '';
        $this->data = [];
        $this->url = null;
        $this->miniProgram = null;
        $this->clientMsgId = null;
        return $this;
    }

    /**
     * 获取微信服务号模板的全部参数
     * @return array
     */
    final public function jsonSerialize(): array
    {
        return array_filter([
            'touser' => $this->getToUser(),
            'template_id' => $this->getTemplateId(),
            'data' => $this->getData(),
            'url' => $this->getUrl(),
            'miniprogram' => $this->getMiniProgram(),
            'client_msg_id' => $this->getClientMsgId()
        ], fn($v) => null !== $v);
    }
}
