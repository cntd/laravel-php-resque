<?php
require_once 'utils/CommandsTestCase.php';

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
        $this->forkListen($testQueue, 3);
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==3;
        },15));
    }
}    