<?php

namespace Ledc\EasyWechat\OfficialAccount;

use InvalidArgumentException;
use Ledc\Container\App;
use think\helper\Str;

/**
 * 微信服务号模板消息工厂类
 */
final readonly class TemplateFactory
{
    /**
     * 创建模板消息对象
     * @param string $template_id_short 模板编号
     * @param string $template_id 模板ID
     * @return TemplateBody
     */
    public static function create(string $template_id_short, string $template_id): TemplateBody
    {
        $app = App::getInstance();
        $name = 'Template' . Str::studly($template_id_short);

        // 从容器创建
        $abstract = 'createWechat' . $name;
        if ($app->bound($abstract)) {
            return $app->make($abstract, [$template_id], true);
        }

        // 从命名空间创建
        $class = __NAMESPACE__ . '\\Templates\\' . $name;
        if (class_exists($class)) {
            return $app->invokeClass($class, [$template_id]);
        }

        throw new InvalidArgumentException("Driver [$name] not supported.");
    }
}
