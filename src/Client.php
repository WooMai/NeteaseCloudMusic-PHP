<?php

namespace WooMaiLabs\NeteaseCloudMusic;


use Exception;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * Login to Netease Cloud Music
     * @param string $md5_password Password after MD5 hashing
     * @param string|null $email
     * @param int|null $phone
     * @param int $phone_country_code
     * @return string MUSIC_U Cookie (without 'MUSIC_U=' prefix)
     * @throws Exception
     */
    public static function login(string $md5_password, ?string $email = null, ?int $phone = null, int $phone_country_code = 86): string
    {
        if ($email == null && $phone == null) {
            throw new Exception('Email or Phone number required');
        }

        $params = [
            'password' => $md5_password
        ];

        if ($phone) {
            $url = 'https://music.163.com/weapi/login/cellphone';
            $params['phone'] = $phone;
            $params['countrycode'] = $phone_country_code;
        } else {
            $url = 'https://music.163.com/weapi/login';
            $params['username'] = $email;
        }

        $req = new NcmRequest($url, 'weapi');
        $response = $req->request($params);
        $setcookie_headers = $response->getHeader('Set-Cookie');
        $music_u = null;
        foreach ($setcookie_headers as $setcookie) {
            if (preg_match('MUSIC_U=([a-f0-9]{80})', $setcookie, $match)) {
                $music_u = $match[1];
            }
        }

        if ($music_u) {
            return $music_u;
        }

        $result = json_decode($response->getBody());
        if (!empty($result->msg)) {
            throw new Exception("Login Failed: $result->msg");
        } else {
            throw new Exception('Login Failed');
        }
    }

    /**
     * Client constructor.
     * @param string|null $music_u MUSIC_U Cookie (without 'MUSIC_U=' prefix)
     * @param bool $raw_response Get Raw ResponseInterface from NCM API
     */
    public function __construct(protected ?string $music_u = null, protected bool $raw_response = false)
    {
    }

    /**
     * @param string $keyword Search keyword
     * @param int $type 1/单曲(default), 10/专辑, 100/歌手, 1000/歌单, 1002/用户, 1004/MV, 1006/歌词, 1009/电台, 1014/视频, 1018/综合
     * @param int $limit Maximum returned results
     * @param int $offset Result offset
     * @return string|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function search(string $keyword, int $type = 1, int $limit = 30, int $offset = 0): string|ResponseInterface
    {
        $req = new NcmRequest('https://music.163.com/weapi/search/get', 'weapi', $this->music_u);
        return $this->ret($req->request([
            's' => $keyword,
            'type' => $type,
            'limit' => $limit,
            'offset' => $offset
        ]));
    }

    /**
     * @param int|array $song_id
     * @param int $bitrate Song bitrate, defaults to best quality
     * @return string|ResponseInterface
     * @throws Exception
     */
    public function getSongUrl(int|array $song_id, int $bitrate = 999000): string|ResponseInterface
    {
        if (is_int($song_id)) {
            $ids = [$song_id];
        } else {
            $ids = $song_id;
        }


        $req = new NcmRequest('https://music.163.com/weapi/song/enhance/player/url', 'weapi', $this->music_u);
        return $this->ret($req->request([
            'ids' => json_encode($ids),
            'br' => $bitrate
        ]));
    }

    public function getSongDetail(int|array $song_id): string|ResponseInterface
    {
        if (is_int($song_id)) {
            $ids = [$song_id];
        } else {
            $ids = $song_id;
        }

        $c = [];
        foreach ($ids as $id) {
            $c[] = ['id' => $id];
        }

        $req = new NcmRequest('https://music.163.com/weapi/v3/song/detail', 'weapi', $this->music_u);
        return $this->ret($req->request([
            'ids' => json_encode($ids),
            'c' => json_encode($c)
        ]));
    }

    public function getUserDetail(int $user_id): string|ResponseInterface
    {
        $req = new NcmRequest("https://music.163.com/weapi/v1/user/detail/$user_id", 'weapi', $this->music_u);
        return $this->ret($req->request());
    }

    protected function ret(ResponseInterface $response): string|ResponseInterface
    {
        if ($this->raw_response) {
            return $response;
        } else {
            return $response->getBody()->getContents();
        }
    }
}