<?php

namespace app\dao\v1;

use app\dao\BaseDao;
use app\model\v1\Song;
use app\model\v1\SongLyrics;

class SongDao extends BaseDao
{
    protected $alias = 'a';

    protected $joinAlias = 'b';

    /**
     * @return string
     */
    public function setModel(): string
    {
        return Song::class;
    }

    protected function setJoinModel(): string
    {
        return SongLyrics::class;
    }

    public function getInfo($hash)
    {
        $where = [
            [$this->alias.'.song_hash', '=', $hash],
        ];
        $name = app()->make($this->setJoinModel())->getName();
        return $this->getModel()->alias($this->alias)
            ->where($where)
            ->join($name.' '.$this->joinAlias, $this->alias.'.song_hash ='.$this->joinAlias.'.song_hash')
            ->field([$this->alias.'.*', $this->joinAlias.'.lyrics'])
            ->findOrEmpty()->toArray();
    }

    public function insert($song_data, $lyrics_data)
    {
        $song_data['create_time'] = time();
        $this->getModel()->insert($song_data);
        $this->getModel($this->setJoinModel())->insert($lyrics_data);
        return array_merge($song_data, $lyrics_data);
    }
}