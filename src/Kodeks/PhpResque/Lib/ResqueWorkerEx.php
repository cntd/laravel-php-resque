<?php namespace Kodeks\PhpResque\Lib;

use Resque_Worker;
use Resque;

class ResqueWorkerEx extends Resque_Worker
{
    private static function kill($signal, $pid) {
        $output = array();
        $message = exec(sprintf('/bin/kill -9 %s %s 2>&1', $signal, $pid), $output, $code);
        return array('code' => $code, 'message' => $message);
    }
    public function suicide() {
        $this -> unregisterWorker();
        $params = explode(":", (string) $this);
        return ResqueWorkerEx::kill("9", $params[1]);
    }
    
    public static function all() {
        $workers = Resque::redis()->smembers('workers');
        if(!is_array($workers)) {
                $workers = array();
        }

        $instances = array();
        foreach($workers as $workerId) {
                $instances[] = static::find($workerId);
        }
        return $instances;
    }
    
    public static function find($workerId) {
        if(!self::exists($workerId) || false === strpos($workerId, ":")) {
              return false;
        }
        list($hostname, $pid, $queues) = explode(':', $workerId, 3);
        $queues = explode(',', $queues);
        $worker = new static($queues);
        $worker->setId($workerId);
        return $worker;
    }
}