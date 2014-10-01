<?php namespace Kodeks\PhpResque;

use Resque;
use Resque_Job_Status;
use Illuminate\Queue\QueueInterface;

class ResqueQueue implements QueueInterface {

    public function push($job, $data = [], $queue = NULL, $track = false) {
        $queue = (is_null($queue) ? $job : $queue);
        return Resque::enqueue($queue, $job, $data, $track);
    }

    public function jobStatus($token)
    {
        $status = new Resque_Job_Status($token);
        return $status->get();
    }

    public function later($delay, $job, $data = '', $queue = null) {
        
    }

    public function pop($queue = null) {
        
    }

    public function pushRaw($payload, $queue = null, array $options = array()) {
        
    }

} 