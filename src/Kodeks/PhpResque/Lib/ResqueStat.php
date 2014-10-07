<?php namespace Kodeks\PhpResque\Lib;

use Kodeks\PhpResque\Lib\ResqueWorkerEx;

class ResqueStat
{
    private $redis;
    public function __construct($redis)
    {
        $this->redis = $redis;
    }
    public function getQueues()
    {
        return $this->redis->smembers('queues');
    }
    public function getQueueLength($queue)
    {
        return $this->redis->llen('queue:' . $queue);
    }
    public function getWorkers()
    {
        return (array)ResqueWorkerEx::all();
    }
    public function getWorkerStartDate($worker)
    {
        return $this->redis->get('worker:' . $worker . ':started');
    }
}