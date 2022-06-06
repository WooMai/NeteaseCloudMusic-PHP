<?php


namespace WooMaiLabs\NeteaseCloudMusic;


use IPLib\Factory as IPLibFactory;
use RandomLib\Factory as RandomLibFactory;

class Random
{
    public static function int(int $min, int $max): int
    {
        $factory = new RandomLibFactory();
        $generator = $factory->getMediumStrengthGenerator();
        return $generator->generateInt($min, $max);
    }

    public static function str(int $length, string $list = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $factory = new RandomLibFactory;
        $generator = $factory->getMediumStrengthGenerator();
        return $generator->generateString($length, $list);
    }

    public static function array(array $array)
    {
        if (count($array) < 1) {
            return null;
        }

        sort($array);
        $index = static::int(0, (count($array) - 1));
        return $array[$index];
    }

    public static function ip(): string
    {
        $cidr = static::array([
            // 4134
            '124.228.0.0/14',
            '122.224.0.0/12',
            '113.64.0.0/11',
            '114.224.0.0/12',
            '111.176.0.0/13',
            '36.106.0.0/16',
            '123.244.0.0/14',
            '115.224.0.0/12',

            // 4837
            '101.16.0.0/12',
            '113.224.0.0/12',
            '119.176.0.0/12',

            // 9808
            '221.176.0.0/13',
            '223.96.0.0/12',
            '223.112.0.0/12',
        ]);

        $range = IPLibFactory::parseRangeString($cidr);

        $start = ip2long(strval($range->getStartAddress()->getNextAddress()));
        $end = ip2long(strval($range->getEndAddress()->getPreviousAddress()));

        return long2ip(static::int($start, $end));
    }
}