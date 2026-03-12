<?php
namespace app\dao;

use kernel\basic\BaseModel;
use think\helper\Str;
use think\Model;

/**
 * Class BaseDao
 * @package app\dao
 */
abstract class BaseDao
{
    /**
     * 当前表名别名
     * @var string
     */
    protected $alias;

    /**
     * join表别名
     * @var string
     */
    protected $joinAlias;


    /**
     * 获取当前模型
     * @return string
     */
    abstract protected function setModel(): string;

    /**
     * 设置join链表模型
     * @return string
     */
    protected function setJoinModel(): string
    {
    }

    /**
     * 读取数据条数
     * @param array $where
     * @param bool $search
     * @return int
     * @throws \ReflectionException
     */
    public function count(array $where = [], bool $search = true)
    {
        return $this->search($where, $search)->count();
    }

    /**
     * 获取某些条件数据
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param bool $search
     * @return \think\Collection
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
    {
        return $this->selectModel($where, $field, $page, $limit, $order, $with, $search)->select();
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param array $with
     * @param bool $search
     * @return BaseModel
     * @throws \ReflectionException
     */
    public function selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
    {
        if ($search) {
            $model = $this->search($where);
        } else {
            $model = $this->getModel()->where($where);
        }
        return $model->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when($order !== '', function ($query) use ($order) {
            $query->order($order);
        })->when($with, function ($query) use ($with) {
            $query->with($with);
        });
    }

    /**
     * 获取某些条件总数
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 获取某些条件去重总数
     * @param array $where
     * @param $field
     * @param bool $search
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDistinctCount(array $where, $field, bool $search = true)
    {
        if ($search) {
            return $this->search($where)->field('COUNT(distinct(' . $field . ')) as count')->select()->toArray()[0]['count'] ?? 0;
        } else {
            return $this->getModel()->where($where)->field('COUNT(distinct(' . $field . ')) as count')->select()->toArray()[0]['count'] ?? 0;
        }
    }

    /**获取模型
     * @param $model
     * @return object
     */
    protected function getModel($model = null)
    {
        return app()->make($model ?: $this->setModel());
    }

    /**
     * 获取主键
     * @return array|string
     */
    protected function getPk()
    {
        return $this->getModel()->getPk();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->getModel()->getName();
    }

    /**
     * 获取一条数据
     * @param $id
     * @param array|null $field
     * @param array|null $with
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get($id, ?array $field = [], ?array $with = [])
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$this->getPk() => $id];
        }
        return $this->getModel()->where($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->field($field ?? ['*'])->find();
    }

    public function getEmpty($id, ?array $field = [], ?array $with = [])
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$this->getPk() => $id];
        }
        return $this->getModel()->where($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->field($field ?? ['*'])->findOrEmpty()->toArray();
    }

    /**
     * 查询一条数据是否存在
     * @param $map
     * @param string $field
     * @return bool 是否存在
     */
    public function be($map, string $field = '')
    {
        if (!is_array($map) && empty($field)) $field = $this->getPk();
        $map = !is_array($map) ? [$field => $map] : $map;
        return 0 < $this->getModel()->where($map)->count();
    }

    /**
     * 根据条件获取一条数据
     * @param array $where
     * @param string|null $field
     * @param array $with
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne(array $where, ?string $field = '*', array $with = [])
    {
        $field = explode(',', $field);
        return $this->get($where, $field, $with);
    }

    /**
     * 获取单个字段值
     * @param $where
     * @param string|null $field
     * @return mixed
     */
    public function value($where, ?string $field = '', $type = true)
    {
        $pk = $this->getPk();
        if ($type) {
            return $this->search($this->setWhere($where))->value($field ?: $pk);
        } else {
            return $this->getModel()->where($where)->value($field ?: $pk);
        }
    }

    /**
     * 获取某个字段数组
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getColumn(array $where, string $field, string $key = '')
    {
        return $this->getModel()->where($where)->column($field, $key);
    }


    /**
     * 删除
     * @param int|string|array $id
     * @return mixed
     */
    public function delete($id, ?string $key = null)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()->where($where)->delete();
    }

    /**
     * 删除记录
     * @param $id
     * @param bool $force
     * @return bool
     */
    public function destroy($id, bool $force = false)
    {
        return $this->getModel()->destroy($id, $force);
    }


    /**
     * 更新数据
     * @param int|string|array $id
     * @param array $data
     * @param string|null $key
     * @return BaseModel
     */
    public function update($id, array $data, ?string $key = null)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()::update($data, $where);
    }

    /**
     * @param $where
     * @return array|mixed
     */
    protected function setWhere($where, ?string $key = null)
    {
        if (!is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    /**
     * 批量更新数据
     * @param array $ids
     * @param array $data
     * @param string|null $key
     * @return BaseModel
     */
    public function batchUpdate(array $ids, array $data, $where = [], ?string $key = null)
    {
        return $this->getModel()->where($where)->whereIn(is_null($key) ? $this->getPk() : $key, $ids)->update($data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        return $this->getModel()::create($data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return \think\Collection
     * @throws \Exception
     */
    public function saveAll(array $data)
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 获取某个字段内的值
     * @param $value
     * @param string $filed
     * @param string|null $valueKey
     * @param array|string[] $where
     * @return mixed
     */
    public function getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = [])
    {
        return $this->getModel()->getFieldValue($value, $filed, $valueKey, $where);
    }

    /**
     * 获取搜索器和搜索条件key,以及不在搜索器的条件数组
     * @param array $where
     * @return array[]
     * @throws \ReflectionException
     */
    private function getSearchData(array $where)
    {
        $with = [];
        $otherWhere = [];
        $responses = new \ReflectionClass($this->setModel());
        foreach ($where as $key => $value) {
            $method = 'search' . Str::studly($key) . 'Attr';
            if ($responses->hasMethod($method)) {
                $with[] = $key;
            } else {
                if (!in_array($key, ['timeKey', 'goods_stock', 'integral_time'])) {
                    if (!is_array($value)) {
                        $otherWhere[] = [$key, '=', $value];
                    } else if (count($value) === 3) {
                        $otherWhere[] = $value;
                    }
                }
            }
        }
        return [$with, $otherWhere];
    }

    /**
     * 根据搜索器获取搜索内容
     * @param $where
     * @param $search
     * @return BaseModel
     * @throws \ReflectionException
     */
    protected function withSearchSelect($where, $search)
    {
        [$with, $otherWhere] = $this->getSearchData($where);
        return $this->getModel()->withSearch($with, $where)->when($search, function ($query) use ($otherWhere) {
            $query->where($this->filterWhere($otherWhere));
        });
    }

    /**
     * 过滤数据表中不存在的where条件字段
     * @param array $where
     * @return array
     */
    protected function filterWhere(array $where = [])
    {
        $fields = $this->getModel()->getTableFields();
        foreach ($where as $key => $item) {
            if (!in_array($item[0], $fields)) {
                unset($where[$key]);
            }
        }
        return $where;
    }

    /**
     * 搜索
     * @param array $where
     * @param bool $search
     * @return BaseModel
     * @throws \ReflectionException
     */
    public function search(array $where = [], bool $search = true)
    {
        if ($where) {
            return $this->withSearchSelect($where, $search);
        } else {
            return $this->getModel();
        }
    }

    /**
     * 求和
     * @param array $where
     * @param string $field
     * @param bool $search
     * @return float
     * @throws \ReflectionException
     */
    public function sum(array $where, string $field, bool $search = false)
    {
        if ($search) {
            return $this->search($where)->sum($field);
        } else {
            return $this->getModel()->where($where)->sum($field);
        }
    }

    /**
     * 高精度加法
     * @param $key
     * @param string $incField
     * @param string $inc
     * @param string|null $keyField
     * @param int $acc
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2)
    {
        return $this->bc($key, $incField, $inc, $keyField, 1);
    }

    /**
     * 高精度 减法
     * @param $key
     * @param string $decField
     * @param string $dec
     * @param string|null $keyField
     * @param int $acc
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2)
    {
        return $this->bc($key, $decField, $dec, $keyField, 2);
    }

    /**
     * 高精度计算并保存
     * @param $key
     * @param string $incField
     * @param string $inc
     * @param string|null $keyField
     * @param int $type
     * @param int $acc
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bc($key, string $incField, string $inc, string $keyField = null, int $type = 1, int $acc = 2)
    {
        if ($keyField === null) {
            $result = $this->get($key);
        } else {
            $result = $this->getOne([$keyField => $key]);
        }
        if (!$result) return false;
        $new = 0;
        if ($type === 1) {
            $new = bcadd($result[$incField], $inc, $acc);
        } else if ($type === 2) {
            if ($result[$incField] < $inc) return false;
            $new = bcsub($result[$incField], $inc, $acc);
        }
        $result->{$incField} = $new;
        return false !== $result->save();
    }

    /**
     * 减库存加销量
     * @param array $where
     * @param int $num
     * @param string $stock
     * @param string $sales
     */
    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
    {
        $isQuota = false;
        if (isset($where['type']) && $where['type']) {
            $isQuota = true;
            if (count($where) == 2) {
                unset($where['type']);
            }
        }
        $field = $isQuota ? 'stock,quota,is_stock' : 'stock,is_stock';
        $product = $this->getModel()->where($where)->field($field)->findOrEmpty()->toArray();
        if ($product) {
            $stock_num = $num;
            if ($product['is_stock'] == 1) {
                if (!$product['stock']) {
                    return false;
                }
                if ($product['stock'] < $num) {
                    return false;
                }
            } else {
                $stock_num = 0;
            }
            return $this->getModel()->where($where)->when($isQuota, function ($query) use ($num) {
                $query->dec('quota', $num);
            })->dec($stock, $stock_num)->inc($sales, $num)->update();
        }
        return false;
    }

    /**
     * 加库存减销量
     * @param array $where
     * @param int $num
     * @param string $stock
     * @param string $sales
     * @return mixed
     */
    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
    {
        $isQuota = false;
        if (isset($where['type']) && $where['type']) {
            $isQuota = true;
            if (count($where) == 2) {
                unset($where['type']);
            }
        }
        $salesOne = $this->getModel()->where($where)->value($sales);
        $is_stock = $this->getModel()->where($where)->value('is_stock');
        if ($salesOne) {
            $salesNum = $num;
            if ($num > $salesOne) {
                $salesNum = $salesOne;
            }
            $stock_num = $num;
            if ($is_stock != 1) {
                $stock_num = 0;
            }
            return $this->getModel()->where($where)->when($isQuota, function ($query) use ($num) {
                $query->inc('quota', $num);
            })->inc($stock, $stock_num)->dec($sales, $salesNum)->update();
        }
        return true;
    }

    /**
     * 获取条件数据中的某个值的最大值
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getMax(array $where = [], string $field = '')
    {
        return $this->getModel()->where($where)->max($field);
    }

    /**
     * 获取条件数据中的某个值的最小值
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getMin(array $where = [], string $field = '')
    {
        return $this->getModel()->where($where)->min($field);
    }

    /**获取展示的列
     * @param $model
     * @param array $array
     * @param bool $except true展示 false隐藏
     * @param string $alias
     * @return array
     */
    public function getFields($model, array $array = [], bool $except = false, string $alias = '')
    {
        $fields = $array;
        if ($array) {
            if ($except) {
                $fields = array_keys($model->getFields());
                if ($fields) {
                    foreach ($fields as $k => $field) {
                        if (in_array($field, $array)) {
                            unset($fields[$k]);
                        }
                    }
                    rsort($fields);
                }
            }
        }
        if ($fields) {
            if ($alias) {
                foreach ($fields as &$field) {
                    $field = $alias . '.' . $field;
                }
            }
        }
        return $fields;
    }

    public function aggregate($where, $action, $field = '')
    {
        return $this->getModel()->where($where)->$action($field);
    }
}
