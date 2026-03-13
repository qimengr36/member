<?php

namespace app\services\v1;

use app\dao\v1\SongDao;
use app\services\BaseServices;
use kernel\utils\HttpClient;

class SongServices extends BaseServices
{
    /**
     * @param  SongDao  $dao
     */
    public function __construct(SongDao $dao)
    {
        $this->dao = $dao;
    }

    public function search($keyword)
    {
        $pagesize = $page = $total = 0;
        $lists = [];
        $default_data = compact('pagesize', 'page', 'total', 'lists');
        if (!$keyword) {
            return $default_data;
        }
        [$page, $limit] = $this->getPageValue();
        $params = array_merge($this->commonParams(), $this->searchSongParams($keyword, $page, $limit));
        $params = $this->setSign($params);
        $url = 'https://complexsearch.kugou.com/v2/search/song';
        $client = app()->make(HttpClient::class);
        $res = $client->request($url, $params, 'get');
        if ($res['code']) {
            return [];
        } else {
            $html_data = $res['data'];
        }
        if (is_bool($html_data)) {
            return $default_data;
        }
        $str = preg_replace('/\)$/', '', preg_match('/\((.*)\)/', $html_data, $matches) ? $matches[1] : '');
        $arr = json_decode($str, true);
        if ($arr && isset($arr['data'])) {
            $arr = $arr['data'];

            [
                'pagesize' => $pagesize,
                'page'     => $page,
                'total'    => $total,
                'lists'    => $array,
            ] = $arr;
            if ($array) {
                foreach ($array as $val) {
                    $song_id = $val['FileHash'];
                    $lists[] = [
                        'singer_name' => $val['SingerName'],
                        'album_name'  => $val['AlbumName'],
                        'file_name'   => $val['FileName'],
                        'song_id'     => $song_id,
                    ];
                }
            }
        }
        return compact('pagesize', 'page', 'total', 'lists');
    }

    public function play($audio_id)
    {
        //查表
        $info = $this->dao->getInfo($audio_id);
        if ($info) {
            return $this->getPlayInfo($info);
        }
        $params = [
            'hash'  => $audio_id,
            'token' => 'dR1HYaT_ocNRzJfaQ7ydgQ',
        ];
        $url = 'https://apicx.asia/api/kugoumusic?'.http_build_query($params);
        $client = app()->make(HttpClient::class);
        $res = $client->request($url, $params, 'get');

        if ($res['code']) {
            return '';
        } else {
            $html_data = $res['data'];
        }
        if (is_bool($html_data)) {
            return '';
        }
        if ($html_data && isset($html_data['data'])) {
            $arr = $html_data['data'];
        } else {
            return [];
        }
        $song_data = [
            'song_hash' => $arr['song_hash'],
            'name'      => $arr['name'],
            'artist'    => $arr['artist'],
            'duration'  => $arr['duration'],
            'cover'     => $arr['cover'],
            'bitrate'   => $arr['bitrate'],
            'play_url'  => $arr['play_info']['url'],
            'format'    => $arr['play_info']['format'],
            'file_size' => $arr['play_info']['file_size'],
        ];

        $lyrics_data = [
            'song_hash' => $arr['song_hash'],
            'lyrics'    => $arr['lyrics'],
        ];
        $data = $this->dao->insert($song_data, $lyrics_data);
        return $this->getPlayInfo($data);
    }

    public function getPlayInfo($data): array
    {
        return [
            'audio_name'  => $data['artist'].'-'.$data['name'],
            'play_url'    => $data['play_url'],
            'author_name' => $data['artist'],
            'lyrics'      => $data['lyrics'],
        ];
    }

    public function setSign($array): array
    {
        $serve_key = 'NVPh5oo715z5DIWAeQlhMDsWXXQV4hwt';
        if (!$array) {
            return [];
        }
        $arr = [];
        ksort($array);
        foreach ($array as $key => $value) {
            $arr[] = $key.'='.$value;
        }
        array_unshift($arr, $serve_key);
        $arr[] = $serve_key;
        $str = implode('', $arr);
        $sign = md5($str);
        $array['signature'] = $sign;
        return $array;
    }

    public function commonParams($client_time = ''): array
    {
        $mid = '55eece85049cab63b8aae5a187f8327f';
        $dfid = '090KoK2zmxLW0a2dFv4e9K9r';
        return [
            'srcappid'   => 2919,
            'appid'      => 1014,
            'clienttime' => $client_time ?: msectime(),
            'mid'        => $mid,
            'uuid'       => $mid,
            'dfid'       => $dfid,
            'token'      => '',
            'userid'     => 0,
        ];
    }

    public function searchSongParams($keyword, $page, $limit): array
    {
        return [
            'callback'         => 'callback123',
            'clientver'        => 1000,
            'keyword'          => $keyword,
            'page'             => $page,
            'pagesize'         => $limit,
            'bitrate'          => 0,
            'isfuzzy'          => 0,
            'inputtype'        => 0,
            'platform'         => 'WebFilter',
            'iscorrection'     => 1,
            'privilege_filter' => 0,
            'filter'           => 10,
        ];
    }
}