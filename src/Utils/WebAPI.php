<?php

namespace WooMaiLabs\NeteaseCloudMusic\Utils;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use phpseclib3\Math\BigInteger;
use WooMaiLabs\NeteaseCloudMusic\Random;

class WebAPI
{
    protected static $pubkey = '010001';
    protected static $modulus = '00e0b509f6259df8642dbc35662901477df22677ec152b5ff68ace615bb7b725152b3ab17a876aea8a5aa76d2e417629ec4ee341f56135fccf695280104e0312ecbda92557c93870114af6c9d05c4f7f0c3685b7a46bee255932575cce10b424d813cfe4875d3e82047b97ddef52741d546b8e289dc6935b3ece0462db0a22b8e7';

    protected static $key = '0CoJUm6Qyw8W8jud';
    protected static $iv = '0102030405060708';

    #[ArrayShape(['params' => "string", 'encSecKey' => "string"])]
    public static function encryptParams(array $params): array
    {
        $key1 = static::$key;
        $key2 = Random::str(16);

        $params = json_encode($params, JSON_FORCE_OBJECT);

        $encrypted_params = openssl_encrypt(
            $params,
            'aes-128-cbc',
            $key1,
            iv: self::$iv
        );

        if (!$encrypted_params) {
            throw new Exception('Encrypt Failed');
        }

        $encrypted_params = openssl_encrypt(
            $encrypted_params,
            'aes-128-cbc',
            $key2,
            iv: self::$iv
        );

        if (!$encrypted_params) {
            throw new Exception('Encrypt Failed');
        }

        return array(
            'params' => $encrypted_params,
            'encSecKey' => static::encryptSecretKey($key2)
        );
    }

    protected static function encryptSecretKey(string $key): string
    {
        $key = strrev(utf8_encode($key));
        $key_dec = new BigInteger(static::hex2dec(static::str2hex($key)));
        $pubkey_dec = new BigInteger(static::hex2dec(static::$pubkey));
        $modulus = new BigInteger(static::hex2dec(static::$modulus));
        $key = $key_dec->modPow($pubkey_dec, $modulus)->toHex();
        return str_pad($key, 256, '0', STR_PAD_LEFT);
    }

    #[Pure]
    protected static function hex2dec($hex): int|string
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 0; $i < $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i])), bcpow('16', strval($len - $i - 1))));
        }
        return $dec;
    }

    #[Pure]
    protected static function str2hex($str): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $hex .= dechex(ord($str[$i]));
        }
        return $hex;
    }
}