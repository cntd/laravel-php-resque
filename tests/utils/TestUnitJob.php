<?php

class TestUnitJob {
    private $redis;
    
    public function perform() {  
        $this->redis->set('TestUnitJob::fire', json_encode($this->args));
    }

    public function setUp() {
        $this->redis=App::make('redis');
        $this->redis->set('TestUnitJob::tearDown', 0);
        $this->redis->del('TestUnitJob::custom');
        $this->redis->del('TestUnitJob::fire');  
    }
    
    public function tearDown() {
        $this->redis->set('TestUnitJob::tearDown', 1);
    }
}