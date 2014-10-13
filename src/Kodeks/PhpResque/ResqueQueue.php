<?php namespace Kodeks\PhpResque;

use Resque;
use Resque_Job_Status;
use Illuminate\Queue\Queue;
use Kodeks\PhpResque\Lib\ResqueJobInterface;

class ResqueQueue extends Queue  {
    
    protected $_default;
    
    function __construct($default) {
       $this->_default=$default;
    }
    
    protected function getQueue($queue) {
        return $queue ? : $this->_default;
    }
    
    protected function isCustomMethod($job) {
        if(is_string($job) && strpos($job, "@")!==false) {
            return true;
        }
        return false;
    }
    
    protected function checkJob($job) {
        if($this->isCustomMethod($job)) {
           throw new \Exception("Custom method support not available");
        } else if($job instanceof \Closure) {
           throw new \Exception("Closures not supported");    
        } else if(!($job instanceof ResqueJobInterface)) {
           throw new \Exception("Instance of job must implement ResqueJobInterface");       
        }
    }
    
    public function push($job, $data = [], $queue = NULL, $track = true) {
        $queue = $this->getQueue($queue);
        $this->checkJob($job);
        $args=[$queue, $job, $data, $track];
        return call_user_func_array("Resque::enqueue",$args);
    }
    
    
    public function reserve($queue=null) {
        $queue = $this->getQueue($queue);
        return Resque::reserve($queue);
    }
    
    public function jobStatus($token)
    {
        $status = new Resque_Job_Status($token);
        return $status->get();
    }
    
    public function isWaiting($token)
    {
        $status = $this->jobStatus($token);
        return $status === Resque_Job_Status::STATUS_WAITING;
    }

    public function isRunning($token)
    {
        $status = $this->jobStatus($token);
        return $status === Resque_Job_Status::STATUS_RUNNING;
    }

    public function isFailed($token)
    {
        $status = $this->jobStatus($token);
        return $status === Resque_Job_Status::STATUS_FAILED;
    }

    public function isComplete($token)
    {
        $status = $this->jobStatus($token);
        return $status === Resque_Job_Status::STATUS_COMPLETE;
    }

    public function later($delay, $job, $data = [], $queue = NULL) {
        $queue = $this->getQueue($queue);
        $this->checkJob($job);
        if (!class_exists('ResqueScheduler')) {  
            throw new \Exception("Class ResqueScheduler not found");
        }
        $later = (is_null($queue) ? $job : $queue);
        $args=[$delay, $later, $job, $data];
        
        if (is_int($delay)) {
            call_user_func_array("ResqueScheduler::enqueueIn",$args);
        } else { 
            call_user_func_array("ResqueScheduler::enqueueAt",$args);
        }
    }
    
    public function listen($event, $function) {
        Resque_Event::listen($event, $function);
    }

    public function pop($queue = null) {
        return Resque::pop($this->getQueue($queue));
    }

    public function pushRaw($payload, $queue = null, array $options = array()) {
        throw new \Exception("Method not available");
    }
    
    public static function __callStatic($method, $parameters) {
        if (method_exists('Resque', $method)) {
            return call_user_func_array(['Resque', $method], $parameters);
        }
        else if (method_exists('ResqueScheduler', $method)) {
            return call_user_func_array(['RescueScheduler', $method], $parameters);
        }
        return call_user_func_array(['Queue', $method], $parameters);
    }
} 