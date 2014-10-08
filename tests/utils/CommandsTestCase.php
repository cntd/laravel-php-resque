<?php 
use Kodeks\PhpResque\Lib\ResqueWorkerEx;

abstract class CommandsTestCase extends TestCase {
    protected $config;
    protected $redis;
    
    public function output($text) {
        echo "\n>".$text;
    }
    
    public function unlockTest($name) {
        $this->redis->set("test:".$name,0); 
    }
    
    public function lockTest($name) {
        if($this->redis->get("test:".$name)==1) {
            die();
        }
        $this->redis->set("test:".$name,1); 
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