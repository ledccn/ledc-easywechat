<?php

namespace Ledc\EasyWechat\OfficialAccount;

use EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient;
use Ledc\EasyWechat\Traits\HasParserResult;
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
     */
    final protected function __construct(protected AccessTokenAwareClient $client)
    {
    }

    /**
     * 获取所有模板
     * @return array
     */
    public function getAll(): array
    {
        try {
            $response = $this->client->get('cgi-bin/template/get_all_private_template');
            $result = $this->parserResult($response);
            $template_list = $result['template_list'] ?? [];

            return $template_list ?: [];
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
