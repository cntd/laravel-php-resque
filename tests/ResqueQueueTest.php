<?php

class ResqueQueueTest extends TestCase {

    private $config;
    private $redis;

    public function setUp(){
        parent::setUp();
        require_once 'utils/TestUnitJob.php';
        $this->redis=App::make('redis');
     
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));       
    }  
    
    public function testQueuePushClosure()
    { 
        $testValue=rand(10, 999999);
        $redis = $this->redis;

        Queue::push(function ($job, $data) use ($testValue) {
            $redis=App::make('redis');
            $redis->set('testPushValue', $testValue);
        });

        $worker = new Resque_Worker($this->config['queue']);
        $worker->work(0);
        $getSettedValue=$redis->get('testPushValue');
        $this->assertEquals($testValue, $getSettedValue);
    }
    public function testQueuePush()
    { 
        $testValue=rand(10, 999999);
        $redis = $this->redis;
        $testData = ["test_data"=>$testValue];
        Queue::push("TestUnitJob",$testData);

        $worker = new Resque_Worker($this->config['queue']);
        $worker->work(0);
        $getSettedValue=$redis->get('TestUnitJob::fire');
        $this->assertEquals(json_encode($testData), $getSettedValue);
    }    
    public function testQueuePushCustomMethod()
    {   
        $testValue=rand(10, 999999);
        $redis = $this->redis;
        $testData = ["test_data"=>$testValue];
        Queue::push("TestUnitJob@custom",$testData);

        $worker = new Resque_Worker($this->config['queue']);
        $worker->work(0);
        $getSettedValue=$redis->get('TestUnitJob::custom');
        $this->assertEquals(json_encode($testData), $getSettedValue);
    }  
    
    private function clearRedisDates() {
        while (($timestamp = ResqueScheduler::nextDelayedTimestamp(null)) !== false) {
            ResqueScheduler::nextItemForTimestamp($timestamp);
        }
    }
    
    public function testLaterPushByDate() {
        $this->clearRedisDates();
        $redis = $this->redis;
        $date = Carbon::now()->addSeconds(4);
        $testValue=time();
        $testData = ["test_data_by_date"=>$testValue];
        Queue::later($date, 'TestUnitJob', $testData);
           
        $timestamp = ResqueScheduler::nextDelayedTimestamp(null);
        $this->assertFalse($timestamp);
        $limit=100;
        while ($timestamp === false) {
            usleep(100000);
            $timestamp = ResqueScheduler::nextDelayedTimestamp(null);
            if(--$limit==0) {
                break;
            }
        }
        $this->assertNotEquals(0, $limit);
           
        $schedulerWorker = new ResqueScheduler_Worker();
        $schedulerWorker -> enqueueDelayedItemsForTimestamp($timestamp);
        
        
        $worker = new Resque_Worker($this->config['queue']);
        $worker->work(0);
        sleep(1);

        $getSettedValue=$redis->get('TestUnitJob::fire');
        $this->assertEquals(json_encode($testData), $getSettedValue);
        
    }
    
    public function testLaterPushByOffsetSeconds() {
        $this->clearRedisDates();
        $redis = $this->redis;
        $testValue=time();
        $testData = ["test_data_by_date"=>$testValue];
        Queue::later(3, 'TestUnitJob', $testData);
           
        $timestamp = ResqueScheduler::nextDelayedTimestamp(null);
        $this->assertFalse($timestamp);
        $limit=100;
        while ($timestamp === false) {
            usleep(100000);
            $timestamp = ResqueScheduler::nextDelayedTimestamp(null);
            if(--$limit==0) {
                break;
            }
        }
        $this->assertNotEquals(0, $limit);
           
        $schedulerWorker = new ResqueScheduler_Worker();
        $schedulerWorker -> enqueueDelayedItemsForTimestamp($timestamp);
        
        
        $worker = new Resque_Worker($this->config['queue']);
        $worker->work(0);
        sleep(1);

        $getSettedValue=$redis->get('TestUnitJob::fire');
        $this->assertEquals(json_encode($testData), $getSettedValue);
        
    }
}