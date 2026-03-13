<?php

namespace app\model\v1;

use kernel\basic\BaseModel;

class Song extends BaseModel
{
    protected $connection = 'sharding';
    protected $name = 'song';
}