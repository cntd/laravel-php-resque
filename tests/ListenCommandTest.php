<?php
require_once 'utils/CommandsTestCase.php';

use Illuminate\Support\Facades\Session;

class ListenCommandTest extends CommandsTestCase {

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

    public function testCommandRunListner()
    { 
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==0;
        },15));
        $testQueue = 'testCommandRunListner';
        $this->forkListen($testQueue, 1);
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==1;
        },15));
    }
    
    public function testCommandRunFewListners()
    { 
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==0;
        },15));
        $testQueue = 'testCommandRunFewListners';
        $this->forkListen($testQueue, 5);
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==5;
        },30));
    }

    public function testCommandInteractiveListner()
    { 
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==0;
        },15));
        $testQueue = 'testCommandInteractiveListner';
        $this->redis->set("InteractiveTest", 0);
        $pid = pcntl_fork();
        if ($pid == -1) {
             die('could not fork');
        } else if (!$pid) {
            Artisan::call('resque:listen',[
                        '--queue'=>$testQueue, 
                        '--count'=>1, 
                        '--log_expire'=>60, 
                        "--interactive"=>1
                        ]);
            $this->redis->set("InteractiveTest", 1);
            die();
        } else {
            $redis = $this->redis;
            $this->assertFalse($this->waitFor(function() use ($redis) {
                return $redis->get("InteractiveTest")==1;
            },5));
        }
    }
    
    public function testNotCommandInteractiveListner()
    { 
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==0;
        },15));
        $testQueue = 'testCommandInteractiveListner';
        $this->redis->set("NotInteractive", 0);
        $pid = pcntl_fork();
        if ($pid == -1) {
             die('could not fork');
        } else if (!$pid) {
            Artisan::call('resque:listen',[
                        '--queue'=>$testQueue, 
                        '--count'=>1, 
                        '--log_expire'=>60, 
                        "--interactive"=>0
                        ]);
            $this->redis->set("NotInteractive", 1);
            die();
        } else {
            $redis = $this->redis;
            $this->assertTrue($this->waitFor(function() use ($redis) {
                return $redis->get("NotInteractive")==1;
            },10));
        }
    }
}    