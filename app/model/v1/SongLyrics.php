<?php

namespace app\model\v1;

use kernel\basic\BaseModel;

class SongLyrics extends BaseModel
{
    protected $connection = 'sharding';
    protected $name = 'song_lyrics';
}