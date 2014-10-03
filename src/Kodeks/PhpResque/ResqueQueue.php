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
    
    protected function jobFilter($job, array $data) {
        if(is_string($job) && strpos($job, "@")!==false) {
            $jobData = explode("@", $job); 
            $data[Resque_Job_Class::INSTANCE_METHOD_NAME]=$jobData[1];
            return $jobData[0];
        }
        return $job;
    }
    
    public function push($job, $data = [], $queue = NULL, $track = true) {
        $queue = $this->getQueue($queue);
        $jobFiltred = $this->jobFilter($job, $data);
        return Resque::enqueue($queue, $jobFiltred, $data, $track);
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
        $jobFiltred = $this->jobFilter($job, $data);
        $queue = $this->getQueue($queue);
        
        if (!class_exists('ResqueScheduler')) {  
            throw new Exception("Class ResqueScheduler not found");
        }
        $later = (is_null($queue) ? $job : $queue);
        if (is_int($delay)) {
            ResqueScheduler::enqueueIn($delay, $later, $jobFiltred, $data);
        }
        else { 
            ResqueScheduler::enqueueAt($delay, $later, $jobFiltred, $data);
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