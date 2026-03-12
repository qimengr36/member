<?php


namespace kernel\services\workerMan;


use app\dao\system\log\AdminMessageDao;
use app\model\system\admin\AdminInfo;
use Channel\Client;
use think\facade\Log;

class ChannelService
{
    /**
     * @var ChannelService
     */
    protected static $instance;
    /**
     * @var Client
     */
    protected $channel;
    /**
     * @var
     */
    protected $trigger = 'pmleb';

    public function __construct()
    {
        self::connet();
    }

    public static function connet()
    {
        $config = config('workerman.channel');
        Client::connect($config['ip'], $config['port']);
    }

    public static function instance()
    {
        if (is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setTrigger(string $name)
    {
        $this->trigger = $name;
        return $this;
    }

    /**
     * 发送消息
     * @param string $type 类型
     * @param array|null $data 数据
     * @param array|null $ids 用户 id,不传为全部用户
     */
    public function send(string $type, ?array $data = null, ?array $ids = null)
    {
        /**
         * language_update 保存修改语言
         * reconfirm_delivery_time  发货日填写
         * deliver_goods 发货
         * receiving 收货
         * close_order 订单取消
         * new_order 订单支付
         * import_goods 导入商品
         * import_goods_price 导入商品价格
         */
        $res = compact('type');
        if (!is_null($data))
            $res['data'] = $data;

        if (!is_null($ids) && count($ids))
            $res['ids'] = $ids;

        $this->trigger($this->trigger, $res);
        $this->trigger = 'pmleb';
    }

    public function trigger(string $type, ?array $data = null)
    {
        Client::publish($type, $data);
    }

    /**
     * 获取通知用户地址
     * @param $mer_ids
     * @param $msg
     * @return array
     */
    public function getToUid($mer_ids, $msg)
    {
        $where = [
            ['mer_id', 'in', $mer_ids]
        ];
        $adminInfo = app()->make(AdminInfo::class);
        $info = $adminInfo->where($where)->field(['uuid', 'mer_id'])->select()->toArray();
        $timer_log_open = config("log.timer_log", false);
        if ($timer_log_open) {
            Log::write($info, 'send_msg');
        }
        $ids = [];
        if ($info) {
            $messageDao = app()->make(AdminMessageDao::class);
            foreach ($info as $v) {
                $to_uid = $v['uuid'];
                $mer_id = $v['mer_id'];
                $data = [
                    'mer_id' => $mer_id,
                    'msg' => json_encode($msg, JSON_UNESCAPED_UNICODE),
                    'to_uid' => $to_uid,
                    'add_time' => time(),
                ];
                $messageDao->save($data);
                $ids[] = $to_uid;
            }
        }
        return $ids;
    }
}
