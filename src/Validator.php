<?php

declare(strict_types=1);

namespace Ledc\EasyWechat;

use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Pay\Contracts\Merchant as MerchantInterface;
use EasyWeChat\Pay\Exceptions\InvalidSignatureException;
use EasyWeChat\Pay\Validator as BaseValidator;
use Psr\Http\Message\MessageInterface;
use function openssl_verify;

/**
 * 签名验证
 */
class Validator implements \EasyWeChat\Pay\Contracts\Validator
{
    /**
     * 构造函数
     * @param MerchantInterface $merchant
     */
    public function __construct(protected MerchantInterface $merchant)
    {
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignatureException
     */
    public function validate(MessageInterface $message): void
    {
        foreach ([BaseValidator::HEADER_SIGNATURE, BaseValidator::HEADER_TIMESTAMP, BaseValidator::HEADER_SERIAL, BaseValidator::HEADER_NONCE] as $header) {
            if (!$message->hasHeader($header)) {
                throw new InvalidSignatureException("Missing Header: {$header}");
            }
        }

        [$timestamp] = $message->getHeader(BaseValidator::HEADER_TIMESTAMP);
        [$nonce] = $message->getHeader(BaseValidator::HEADER_NONCE);
        [$serial] = $message->getHeader(BaseValidator::HEADER_SERIAL);
        [$signature] = $message->getHeader(BaseValidator::HEADER_SIGNATURE);

        $body = (string)$message->getBody();

        $message = "{$timestamp}\n{$nonce}\n{$body}\n";

        if (\time() - \intval($timestamp) > BaseValidator::MAX_ALLOWED_CLOCK_OFFSET) {
            throw new InvalidSignatureException('Clock Offset Exceeded');
        }

        $publicKey = $this->merchant->getPlatformCert($serial);

        if (!$publicKey) {
            throw new InvalidConfigException(
                "No platform certs found for serial: {$serial}, 
                please download from wechat pay and set it in merchant config with key `certs`."
            );
        }

        if (openssl_verify(
                $message,
                base64_decode($signature),
                strval($publicKey),
                OPENSSL_ALGO_SHA256
            ) !== 1) {
            throw new InvalidSignatureException('Invalid Signature');
        }
    }
}
