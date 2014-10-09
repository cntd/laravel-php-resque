<?php
require_once 'utils/CommandsTestCase.php';

use Kodeks\PhpResque\Lib\ResqueWorkerEx;

class LogOutputTest extends CommandsTestCase {

    public function setUp(){
        parent::setUp();
        require_once 'utils/TestOutputJob.php';
        $this->redis = App::make('redis');
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));     
        $this->killWorkers();
    }  
    
    public function tearDown(){
        parent::tearDown();
        $this->killWorkers();
        pcntl_wait($status);
    }
    
    public function testLog()
    { 
        $testQueue = 'testLog';
        $this->forkListen($testQueue, 1);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
        
        $logLenBefore = \Resque::redis()->llen("output"); 
        
        $testData = ["test_data"=>time()];
        $id = Queue::push("TestOutputJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id);
        },20)); 
        
        $logLenAfter = \Resque::redis()->llen("output"); 
        $this->assertEquals($logLenBefore + 1, $logLenAfter);
        
        $output = \Resque::redis()->rpop('output');
        $decoded_rec = json_decode($output);
        
        $this->assertTrue(isset($decoded_rec->payload->args[0]));
        $this->assertEmpty(array_diff($testData, (array)$decoded_rec->payload->args[0]));
        
    }
}    