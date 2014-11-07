<?php namespace Kodeks\PhpResque\Lib;

use ResqueScheduler_Worker;
use Resque;

class ResqueSchedulerWorkerEx extends ResqueScheduler_Worker {
    public static function shutdown($signal = 9) 
    {
        if(Resque::redis()->exists('schedulerPid')) {
           if(posix_kill(Resque::redis()->get('schedulerPid'), $signal)) {
               Resque::redis()->del('schedulerPid');
               return true;
           } 
        }
        return false;
    }
    
    public static function isRunning() 
    {
        return Resque::redis()->exists('schedulerPid') ? true : false;
    }
    
    public function work($interval = null)
    {
        $pid = posix_getpid();
        Resque::redis()->set('schedulerPid', $pid);
        parent::work($interval);
    }
}
