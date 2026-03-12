<?php

namespace app\api\controller\v1;

use app\api\controller\AuthController;
use app\services\v1\SongServices;
use think\facade\App;

class Song extends AuthController
{
    /**
     * 歌曲
     * @param  App  $app
     */
    public function __construct(App $app, SongServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 检索
     */
    public function search()
    {
        [$keyword] = $this->request->getMore([
            'keyword',
        ], true);
        $result = $this->services->search($keyword);
        return app('json')->success($result);
    }

    /**
     * 播放
     */
    public function play()
    {
        [$audio_id] = $this->request->getMore([
            'audio_id',
        ], true);
        $result = $this->services->play($audio_id);
        return app('json')->success($result);
    }

    protected function initialize(): void
    {
    }
}