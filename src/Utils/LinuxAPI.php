<?php

namespace WooMaiLabs\NeteaseCloudMusic\Utils;

use JetBrains\PhpStorm\ArrayShape;

class LinuxAPI
{
    protected static $key = 'rFgB&h#%2?^eDg:Q';

    #[ArrayShape(['eparams' => "string"])] public static function encryptParams(string $url, array $params): array
    {
        $params = json_encode([
            'method' => 'POST',
            'url' => $url,
            'params' => array_map('strval', $params)
        ]);

        $encrypted_params = base64_encode(openssl_encrypt(
            $params,
            'aes-128-ecb',
            static::$key
        ));

        return array(
            'eparams' => strtoupper(bin2hex($encrypted_params))
        );
    }
}