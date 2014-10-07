<?php
use Kodeks\PhpResque\Lib\ResqueWorkerEx;

class ListenCommandTest extends TestCase {
    private $config;
    private $redis;

    public function setUp(){
        parent::setUp();
        $this->redis=App::make('redis');
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));     
        $this->killWorkers();
    }
    
    public function tearDown(){
        parent::tearDown();
        $this->killWorkers();
    }
    
    public function output($text) {
        echo "\n>".$text;
    }
    
    private function killWorkers($pids=[]) {
        
        if(empty($pids)) {
            $all = ResqueWorkerEx::all();
            foreach($all as $work) {
                $result = $work -> suicide();
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
    
    public function testCommandRunListner()
    { 
        Artisan::call('resque:listen');
        sleep(1);
        $all = Resque_Worker::all();
        $this->assertEquals(1, count($all));
    }
    
    public function testCommandRunFewListners()
    { 
        Artisan::call('resque:listen', ['--count'=>3]);
        sleep(1);
        $all = Resque_Worker::all();
        $this->assertEquals(3, count($all));
    }
}    