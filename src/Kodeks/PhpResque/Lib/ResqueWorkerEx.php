<?php namespace Kodeks\PhpResque\Lib;

use Resque_Worker;
use Resque;

class ResqueWorkerEx extends Resque_Worker
{
    protected $pid = null;
    protected $queues_list = null;
    
    public function getPid() {
        if($this->pid !== null) {
            return $this->pid; 
        }
        $params = explode(':', (string)$this);
        $this->pid = $params[1];
        $this->queues_list = explode(",", $params[2]);
        return $this->pid;
    }
    
    public function getQueues() {
        if($this->queues_list !== null) {
            return $this->queues_list; 
        }
        $params = explode(':', (string)$this);
        $this->pid = $params[1];
        $this->queues_list = explode(",", $params[2]);
        return $this->queues_list;
    }
    
    private static function kill($signal, $pid) {
        return posix_kill($pid, $signal);
    }
    public function suicide() {
        return static::kill(SIGTERM, $this->getPid());
    }
    public function signal($signal) {
        return static::kill($signal, $this->getPid());
    }
     
    public static function findByQueue($queue) {
        $workers = static::all();
        $filtred = [];
        foreach($workers as $worker) {
            if(!($worker instanceof Resque_Worker)) {
                continue;
            }
            if(in_array($queue, $worker->getQueues())) {
                $filtred[] = $worker; 
            }
        }
        return $filtred;
    }
    
    public static function findByPid($pid) {
        $workers = static::all();
        foreach($workers as $worker) {
            if($worker->getPid() == $pid) {
                return $worker;
            }
        }
        return false;
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
    
    
    public static function restart() {
        $workers = static::all();
        if (!empty($workers)) {
            foreach ($workers as $worker) {
                if (isset($worker['type']) && $worker['type'] === 'scheduler') {
                    $this->startScheduler($worker);
                } else {
                    $this->start($worker);
                }
            }
        } 
        
    }
}