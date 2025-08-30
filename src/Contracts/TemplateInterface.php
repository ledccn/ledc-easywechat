<?php

namespace Ledc\EasyWechat\Contracts;

/**
 * 微信服务号模板消息接口
 */
interface TemplateInterface
{
    /**
     * 接收者（用户）的 openid
     * @return string
     */
    public function getToUser(): string;

    /**
     * 获取模板ID
     * @return string
     */
    public function getTemplateId(): string;

    /**
     * 模板数据
     * @return array
     */
    public function getData(): array;

    /**
     * 模板跳转链接
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * 跳转小程序
     * @return array|null
     */
    public function getMiniProgram(): ?array;

    /**
     * 防重入id
     * @return string|null
     */
    public function getClientMsgId(): ?string;
}
