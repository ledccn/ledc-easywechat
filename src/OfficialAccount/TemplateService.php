<?php

namespace Ledc\EasyWechat\OfficialAccount;

use EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient;
use Ledc\EasyWechat\Traits\HasParserResult;
use Ledc\EasyWechat\WechatService;
use RuntimeException;
use Throwable;

/**
 * 服务号模板消息服务
 */
readonly class TemplateService
{
    use HasParserResult;

    /**
     * 构造函数
     * @param AccessTokenAwareClient $client
     */
    final public function __construct(protected AccessTokenAwareClient $client)
    {
    }

    /**
     * 实例化服务号模板消息服务
     * @param int|string|null $key
     * @param string|null $name
     * @return static
     */
    public static function make(int|string|null $key = null, ?string $name = null): static
    {
        return new static(WechatService::instance($key, $name)->getClient());
    }

    /**
     * 获取所有模板
     * @return array
     */
    public function getAll(): array
    {
        try {
            $response = $this->client->get('cgi-bin/template/get_all_private_template');
            if ($response->isFailed()) {
                throw new RuntimeException($response->getContent());
            }

            $result = json_decode($response->getContent(), true);
            $template_list = ($result['template_list'] ?? []) ?: [];
            $errcode = $result['errcode'] ?? null;
            $errmsg = $result['errmsg'] ?? '获取所有模板失败，微信未返回错误信息';
            if ($template_list || is_null($errcode) || 0 === $errcode) {
                return $template_list;
            }
            throw new RuntimeException($errmsg, $errcode);
        } catch (Throwable $throwable) {
            throw new RuntimeException($throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * 发送模板消息
     * @param TemplateBody $body
     * @return string
     */
    public function sendTemplateMessage(TemplateBody $body): string
    {
        try {
            $response = $this->client->postJson('cgi-bin/message/template/send', $body->jsonSerialize());
            $result = $this->parserResult($response);

            return (string)($result['msgid'] ?? '');
        } catch (Throwable $throwable) {
            throw new RuntimeException('发送模板消息失败：' . $throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * 删除账号下的指定模板
     * @param string $template_id 模板ID
     * @return bool
     */
    public function delTemplate(string $template_id): bool
    {
        try {
            $response = $this->client->postJson('cgi-bin/template/del_private_template', [
                'template_id' => $template_id
            ]);
            $this->parserResult($response);

            return true;
        } catch (Throwable $throwable) {
            throw new RuntimeException('删除模板失败：' . $throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * 添加模板获得模板ID
     * @param string $template_id_short 模板编号
     * @param array $keyword_name_list 模板的关键词列表
     * @return string
     */
    public function addTemplate(string $template_id_short, array $keyword_name_list): string
    {
        try {
            $response = $this->client->postJson('cgi-bin/template/api_add_template', [
                'template_id_short' => $template_id_short,
                'keyword_name_list' => $keyword_name_list
            ]);
            $result = $this->parserResult($response);

            return $result['template_id'] ?? '';
        } catch (Throwable $throwable) {
            throw new RuntimeException('添加模板失败：' . $throwable->getMessage(), $throwable->getCode());
        }
    }
}
