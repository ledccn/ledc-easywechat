<?php

namespace Ledc\EasyWechat;

/**
 * webman安装类
 */
class Install
{
    const bool WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static array $pathRelation = [
        'config/plugin/ledc/easywechat' => 'config/plugin/ledc/easywechat',
    ];

    /**
     * Install
     * @return void
     */
    public static function install(): void
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall(): void
    {
        static::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            //symlink(__DIR__ . "/$source", base_path()."/$dest");
            // 强制覆盖 2025年1月2日
            copy_dir(__DIR__ . "/$source", base_path() . "/$dest", true);
            echo "Create $dest" . PHP_EOL;
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path() . "/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove $dest" . PHP_EOL;
            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }
            remove_dir($path);
        }
    }
}
