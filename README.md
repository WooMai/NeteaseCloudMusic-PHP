# NeteaseCloudMusic-PHP

A simple PHP client library for Netease Cloud Music. Used in WooMai Bot V2.

## Requirements

PHP 8.0 (64bit) or later 

## Installation

```shell
composer install woomai/netease-cloud-music ^1.0
```

## Example Usage

```php
<?php

use WooMaiLabs\NeteaseCloudMusic\Client;

/**
 * @var string $music_u Your MUSIC_U cookie. Can be null.
 */

$client = new Client($music_u);

print_r(json_decode($client->search('Hand in Hand')->getBody()));

print_r(json_decode($client->getSongUrl(1808556594)->getBody()));
```

## API

It's simple and easy to read. Please checkout the [source code](./src/Client.php).

## Reference

* https://github.com/Binaryify/NeteaseCloudMusicApi
* https://github.com/metowolf/Meting

## License

MIT
