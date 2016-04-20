<?php
require_once 'utils/CommandsTestCase.php';

use Kodeks\PhpResque\Lib\ResqueWorkerEx;
use Kodeks\PhpResque\Lib\ResqueLog;
use Symfony\Component\Console\Output\BufferedOutput;

class LogOutputTest extends CommandsTestCase {

    public function setUp(){
        parent::setUp();
        require_once 'utils/TestOutputJob.php';
        require_once 'utils/TestExceptionJob.php';
        $this->redis = App::make('redis');
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));     
        $this->killWorkers();
    }  
    
    public function tearDown(){
        parent::tearDown();
        $this->killWorkers();
        pcntl_wait($status);
    }
    
    public function testInteractiveOutput() {
        $testQueue = 'testInteractiveOutput';
 
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 1, $startOutput, 60, 1);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
        $testData = ["test_data"=>rand(100, 99999)];
        $id = Queue::push("TestOutputJob",$testData, $testQueue);
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id);
        },20));
        $worker = ResqueWorkerEx::all();
        $worker = array_pop($worker);
        $output = ResqueLog::getByPid('output', $worker->getPid());
        $this->assertEmpty($output);
    }

    public function testLog()
    { 
        $testQueue = 'testLog';
        $expire = 15; 
        $this->forkListen($testQueue, 1, null, $expire);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData = ["test_data"=>time()];
        $id = Queue::push("TestOutputJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id);
        },20)); 
        
        $output = ResqueLog::getLog('output');
        
        $equal_found = false;
        foreach($output as $record) {
            $decoded_rec = json_decode($record);
            $this->assertTrue(isset($decoded_rec->payload->args[0]));
            if(empty(array_diff($testData, (array)$decoded_rec->payload->args[0]))) {
                $equal_found = true;
                break;
            }
        }
        $this->assertTrue($equal_found);
        
        
    }
    
    public function testErrorLog()
    { 
        $testQueue = 'testErrorLog';
        $expire = 120; 
        $this->forkListen($testQueue, 1, null, $expire);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData = ["test_data"=>time() . "-" . rand(10,1000)];
        $id = Queue::push("TestExceptionJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_FAILED == Queue::jobStatus($id);
        },20)); 
        
        $output = ResqueLog::getLog('error');
        
        $equal_found = false;
        foreach($output as $record) {
            $decoded_rec = json_decode($record);
            $this->assertTrue(isset($decoded_rec->error));
            if(empty(array_diff($testData, (array)$decoded_rec->error))) {
                $equal_found = true;
                break;
            }
        }
        $this->assertFalse($equal_found);    
    }
    
    public function testErrorInreractiveLog()
    { 
        $testQueue = 'testErrorLog';
        $expire = 120; 
        $this->forkListen($testQueue, 1, null, $expire, 1);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData = ["test_data"=>time() . "-" . rand(10,1000)];
        $id = Queue::push("TestExceptionJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_FAILED == Queue::jobStatus($id);
        },20)); 
        
        echo "\n ^ INTERACTIVE ERROR EXPECT HERE ^ \n";
        
  
    }
    
    public function testLogExpire()
    { 
        $testQueue = 'testLog';
        $expire = 10; 
        $this->forkListen($testQueue, 1, null, $expire);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData = ["test_data"=>time() . "-" . rand(10,1000)];
        $id = Queue::push("TestOutputJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id);
        },20)); 
        
        sleep($expire);
        $output = ResqueLog::getLog('output');
        
        $equal_found = false;
        foreach($output as $record) {
            $decoded_rec = json_decode($record);
            $this->assertTrue(isset($decoded_rec->payload->args[0]));
            if(empty(array_diff($testData, (array)$decoded_rec->payload->args[0]))) {
                $equal_found = true;
                break;
            }
        }
        $this->assertFalse($equal_found);    
    }

    public function testLogByPid()
    { 
        $testQueue = 'testLog';
        $expire = 15; 
        $this->forkListen($testQueue, 1, null, $expire);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData = ["test_data"=>time() . "-" . rand(10,1000)];
        $id = Queue::push("TestOutputJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id);
        },20)); 

        $worker = ResqueWorkerEx::all();
        $worker = array_pop($worker);
        $output = ResqueLog::getByPid('output', $worker->getPid());
        $equal_found = false;
        foreach($output as $record) {
            $decoded_rec = json_decode($record);
            $this->assertTrue(isset($decoded_rec->payload->args[0]));
            if(empty(array_diff($testData, (array)$decoded_rec->payload->args[0]))) {
                $equal_found = true;
                break;
            }
        }
        $this->assertTrue($equal_found);
        
        
    }
    
    public function testLogByQueue()
    { 
        $testQueue = 'testLogByQueue';
        $expire = 15; 
        $this->forkListen($testQueue, 1, null, $expire);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData = ["test_data"=>time() . "-" . rand(10,1000)];
        $id = Queue::push("TestOutputJob",$testData, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id);
        },20)); 

        $worker = ResqueWorkerEx::all();
        $worker = array_pop($worker);
        $queues = $worker->getQueues();
        $output = ResqueLog::getByQueue('output', $queues[0]);
        $equal_found = false;
        foreach($output as $record) {
            $decoded_rec = json_decode($record);
            $this->assertTrue(isset($decoded_rec->payload->args[0]));
            if(empty(array_diff($testData, (array)$decoded_rec->payload->args[0]))) {
                $equal_found = true;
                break;
            }
        }
        $this->assertTrue($equal_found);
        
    }  

    public function testLogByQueueManyRecs()
    { 
        $testQueue = 'testLogByQueue';
        $expire = 30; 
        $this->forkListen($testQueue, 1, null, $expire);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));
   
        $testData1 = ["test_data"=>time() . "-" . rand(10,1000)];
        $testData2 = ["test_data"=>time() . "-" . rand(10,1000)];
        $testData3 = ["test_data"=>time() . "-" . rand(10,1000)];
        
        $id1 = Queue::push("TestOutputJob",$testData1, $testQueue);
        $id2 = Queue::push("TestOutputJob",$testData2, $testQueue);
        $id3 = Queue::push("TestOutputJob",$testData3, $testQueue);
        
        $this->assertTrue($this->waitFor(function() use ($id1) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id1);
        },20)); 
        $this->assertTrue($this->waitFor(function() use ($id2) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id2);
        },20)); 
        $this->assertTrue($this->waitFor(function() use ($id3) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id3);
        },20)); 

        $worker = ResqueWorkerEx::all();
        $worker = array_pop($worker);
        $queues = $worker->getQueues();
        $output = ResqueLog::getByQueue('output', $queues[0]);
        
        $equal_found1 = false;
        $equal_found2 = false;
        $equal_found3 = false;
      
        foreach($output as $record) {
            $decoded_rec = json_decode($record);
            $this->assertTrue(isset($decoded_rec->payload->args[0]));
            if(empty(array_diff($testData1, (array)$decoded_rec->payload->args[0]))) {
                $equal_found1 = true;
            }
            if(empty(array_diff($testData2, (array)$decoded_rec->payload->args[0]))) {
                $equal_found2 = true;
            }
            if(empty(array_diff($testData3, (array)$decoded_rec->payload->args[0]))) {
                $equal_found3 = true;
            }
        }
        $this->assertTrue($equal_found1 && $equal_found2 && $equal_found3);
        
    }
}    