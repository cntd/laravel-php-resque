<?php 
use Kodeks\PhpResque\Lib\ResqueWorkerEx;

abstract class CommandsTestCase extends TestCase {
    protected $config;
    protected $redis;
    
    public function output($text) {
        echo "\n>".$text;
    }
    /*
    public function unlockTest($name) {
        $this->redis->set("test:".$name,0); 
    }
    
    public function lockTest($name) {
        if($this->redis->get("test:".$name)==1) {
            die();
        }
        $this->redis->set("test:".$name,1); 
    }
    */
    public function waitFor(Closure $cb, $timer=15) {
        while(!($cb())) {
            if(--$timer==0) {
                return false;
            }
            sleep(1);
        }
        return true; 
    }
    
    public function forkListen($queue, $count=1, $output = null) {
        $pid = pcntl_fork();
        if ($pid == -1) {
             die('could not fork');
        } else if (!$pid) {
             // we are the child
            if($output !== null) {
                Artisan::call('resque:listen',['--queue'=>$queue, '--count'=>$count], $output);
            } else {
                Artisan::call('resque:listen',['--queue'=>$queue, '--count'=>$count]);    
            }
            die();
        } 
        return $pid;
    }
    
    public function killWorkers($pids=[]) {
        if(empty($pids)) {
            $all = ResqueWorkerEx::all();
            foreach($all as $work) {
                $work -> suicide();
                $this->output("suicide id: ".$work);
            }
        } else if(is_array($pids)) {
            foreach($pids as $id) {
                if(ResqueWorkerEx::exists($id)) {
                    $worker=Resque_Worker::find($id);
                    $worker -> suicide();
                    $this->output("suicide id: ".$work);
                }
            }
        } else {
            if(Resque_Worker::exists($pids)) {
                $worker=ResqueWorkerEx::find($pids);
                $worker -> suicide();
                $this->output("suicide id: ".$work);
            }
        }
    }
}