<?php

namespace Ledc\EasyWechat;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Support\AesGcm;

/**
 * 工具类
 */
class Utils
{
    /**
     * 解密微信支付的平台证书
     * - https://pay.weixin.qq.com/docs/merchant/apis/platform-certificate/api-v3-get-certificates/get.html
     * @param array $encrypt_certificate 【证书信息】证书内容 密文
     * @param string $key 商户API v3密钥
     * @return string
     * @throws InvalidArgumentException
     */
    public static function decryptCertificate(array $encrypt_certificate, string $key): string
    {
        return AesGcm::decrypt(
            $encrypt_certificate['ciphertext'],
            $key,
            $encrypt_certificate['nonce'],
            $encrypt_certificate['associated_data']
        );
    }
}
