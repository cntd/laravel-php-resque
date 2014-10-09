<?php
require_once 'utils/CommandsTestCase.php';

class ListenCommandTest extends CommandsTestCase {
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
    
    public function testCommandRunListner()
    { 
        Artisan::call('resque:listen');
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==1;
        },15));
    }
    
    public function testCommandRunFewListners()
    { 
        Artisan::call('resque:listen', ['--count'=>3]);
        $this->assertTrue($this->waitFor(function() {
            return count(Resque_Worker::all())==3;
        },15));
    }
}    