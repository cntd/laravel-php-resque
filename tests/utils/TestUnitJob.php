<?php

class TestUnitJob {
    private $redis;
    
    public function fire($job, $data) {  echo "fire...\n";
        $this->redis->set('TestUnitJob::fire', json_encode($data));
    }
    public function custom($job, $data) { echo "custom...\n";
        $this->redis->set('TestUnitJob::custom', json_encode($data));
    }
    
    public function setUp() {  echo "set up...\n";
        $this->redis=App::make('redis');
        $this->redis->set('TestUnitJob::tearDown', 0);
        $this->redis->del('TestUnitJob::custom');
        $this->redis->del('TestUnitJob::fire');  
    }
    
    public function tearDown() {
        $this->redis->set('TestUnitJob::tearDown', 1);
    }
}