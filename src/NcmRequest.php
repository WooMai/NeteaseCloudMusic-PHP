<?php


namespace WooMaiLabs\NeteaseCloudMusic;


use Exception;
use GuzzleHttp\Client as GuzzleClient;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use WooMaiLabs\NeteaseCloudMusic\Utils\LinuxAPI;
use WooMaiLabs\NeteaseCloudMusic\Utils\WebAPI;

class NcmRequest
{
    protected $headers = [];

    protected $guzzle;

    public function __construct(protected string $url, protected string $type, protected ?string $music_u = null)
    {
        $rndip = Random::ip();
        $this->guzzle = new GuzzleClient([
            'timeout' => 20,
            'headers' => [
                'X-Real-IP' => $rndip,
                'X-Forwarded-For' => $rndip,
            ], 'debug' => true
        ]);
    }

    public function request(array $params = []): ResponseInterface
    {
        try {
            $body = $this->prepareData($params);
            return $this->guzzle->post($this->url, [
                'form_params' => $body,
                'headers' => $this->headers,
            ]);
        } catch (Throwable $e) {
            throw new Exception("NCM API Request Failed: {$e->getMessage()}");
        }
    }

    protected function prepareData(array $params): array
    {
        switch ($this->type) {
            case 'weapi':
                $this->headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36';
                $this->headers['Referer'] = 'https://music.163.com/';
                $this->headers['Origin'] = 'https://music.163.com';
                $this->headers['Cookie'] = $this->getCookie();
                return WebAPI::encryptParams($params);

            case 'linuxapi':
                $url = $this->url;
                $this->url = 'https://music.163.com/api/linux/forward';
                $this->headers['User-Agent'] = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.85 Safari/537.36';
                $this->headers['Referer'] = 'https://music.163.com/';
                $this->headers['Origin'] = 'https://music.163.com';
                $this->headers['Cookie'] = $this->getCookie();
                return LinuxAPI::encryptParams($url, $params);

            default:
                throw new Exception('Unsupported type');
        }
    }

    #[Pure]
    protected function getCookie(): string
    {
        $cookie = '';
        if ($this->music_u) {
            $cookie .= "MUSIC_U={$this->music_u}; ";
        }

        return rtrim($cookie, '; ');
    }
}