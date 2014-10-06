<?php namespace Kodeks\PhpResque;

use Resque;
use Resque_Job_Status;
use ResqueScheduler;
use Illuminate\Queue\Queue;
use Resque_Job_Class;

class ResqueQueue extends Queue  {
    
    protected $_default;
    
    function __construct($default) {
       $this->_default=$default;
    }
    
    protected function getQueue($queue) {
        return $queue ? : $this->_default;
    }
    
    protected function getCustomMethod($job) {
        $jobData = explode("@", $job); 
        return $jobData[1];
    }
    
    protected function getClearedJobName($job) {
        $jobData = explode("@", $job); 
        return $jobData[0];
    }
    
    protected function isCustomMethod($job) {
        if(is_string($job) && strpos($job, "@")!==false) {
            return true;
        }
        return false;
    }
    
    public function push($job, $data = [], $queue = NULL, $track = true) {
        $queue = $this->getQueue($queue);
        if($this->isCustomMethod($job)) {
           $args=[$queue, $this->getClearedJobName($job), $data, $track, $this->getCustomMethod($job)];  
        } else {
           $args=[$queue, $job, $data, $track];
        }
        call_user_func_array("Resque::enqueue",$args);
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
        
        if (!class_exists('ResqueScheduler')) {  
            throw new Exception("Class ResqueScheduler not found");
        }
        $later = (is_null($queue) ? $job : $queue);
        if($this->isCustomMethod($job)) {
           $args=[$delay, $later, $this->getClearedJobName($job), $data, $this->getCustomMethod($job)];  
        } else {
           $args=[$delay, $later, $job, $data];
        }
        
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
        throw new Exception("Method not available");
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