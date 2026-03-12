<?php


namespace kernel\services\workerMan;


use Channel\Client;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

class WorkerManService
{
    /**
     * @var Worker
     */
    protected $worker;

    /**
     * @var TcpConnection[]
     */
    protected $connections = [];

    /**
     * @var TcpConnection[]
     */
    protected array $user = [];

    /**
     * @var WorkerManHandle
     */
    protected $handle;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var int
     */
    protected $timer;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
        $this->handle = new WorkerManHandle($this);
        $this->response = new Response();
    }

    public function setUser(TcpConnection $connection)
    {
        $this->user[$connection->adminInfo['uuid']][$connection->noncestr] = $connection;
    }

    public function onConnect(TcpConnection $connection)
    {
        $this->connections[$connection->id] = $connection;
        $now_time = time();
        $connection->lastMessageTime = $now_time;
    }

    public function onMessage(TcpConnection $connection, $res)
    {
        $connection->lastMessageTime = time();
        $res = json_decode($res, true);
        if (!($res && isset($res['type']))) return false;
        if ($res['type'] == 'ping') {
            return $this->response->connection($connection)->success('ping', ['now' => time()]);
        }
        if (!method_exists($this->handle, $res['type'])) return false;
        $this->handle->{$res['type']}($connection, $res + ['data' => []], $this->response->connection($connection));
    }


    public function onWorkerStart(Worker $worker)
    {
        var_dump('onWorkerStart');

        ChannelService::connet();

        Client::on('pmleb', function ($eventData) use ($worker) {
            if (!isset($eventData['type']) || !$eventData['type']) return;
            $ids = isset($eventData['ids']) && count($eventData['ids']) ? $eventData['ids'] : array_keys($this->user);
            foreach ($ids as $id) {
//                var_dump('onMessage', $this->user);
                if (isset($this->user[$id])) {
                    foreach ($this->user[$id] as $connection) {
                        $this->response->connection($connection)->success($eventData['type'], $eventData['data'] ?? null);
                    }
                }
            }
        });

        $this->timer = Timer::add(15, function () use (&$worker) {
            $time_now = time();
            foreach ($worker->connections as $connection) {
                if ($time_now - $connection->lastMessageTime > 12) {
                    $this->response->connection($connection)->close('timeout');
                }
            }
        });
    }


    public function onClose(TcpConnection $connection)
    {
        var_dump('onClose');
        unset($this->connections[$connection->id]);
    }
}
