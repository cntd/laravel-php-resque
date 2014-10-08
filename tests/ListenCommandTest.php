<?php

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