<?php

use Kodeks\PhpResque\Lib\ResqueWorkerEx;

require_once 'utils/CommandsTestCase.php';

class ResqueQueueTest extends CommandsTestCase {

    public function setUp(){
        parent::setUp();
        require_once 'utils/TestUnitJob.php';
        require_once 'utils/NotImplementedJob.php';
        $this->redis=App::make('redis');
        $this->killWorkers();
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));   
        $this->clearRedisQueue($this->config['queue']);
    }  
    
    private function clearRedisQueue($queue) {
        while(Resque::pop($queue)) {}
    }
    
    private function clearRedisRecord($rec) {
        if(is_array($rec)) {
           foreach($rec as $i) {
               $this->redis->del($i); 
           } 
        } else {
           $this->redis->del($rec); 
        }
    }
    
    private function clearRedisDates() {
        while (($timestamp = ResqueScheduler::nextDelayedTimestamp(null)) !== false) {
            ResqueScheduler::nextItemForTimestamp($timestamp);
        }
    }
    /**
     * @expectedException \Exception
     */
    public function testQueuePushClosure()
    { 
        Queue::push(function ()  {
          
        });
    }
    public function testQueuePush()
    { 
        $redis = $this->redis;
        $testData = ["test_data"=>time()];
        $queueId = Queue::push("TestUnitJob",$testData, $this->config["queue"]);
        $getSettedValue = $redis->lpop('resque:queue:'.$this->config["queue"]);
        $queueData = json_decode($getSettedValue);   
        $this->assertEmpty(array_diff($testData, (array)$queueData->args[0]));
        $this->assertEquals("TestUnitJob", $queueData->class);
        $this->assertEquals($queueId, $queueData->id);
    }  
    /**
     * @expectedException Exception
     */
    public function testQueuePushCustomMethod()
    {   
        Queue::push("TestUnitJob@custom",[]);
    }
    
    /**
     * @expectedException Exception
     */
    public function testJobImplement()
    {   
        Queue::push("NotImplementedJob",[]);
    } 
    
    public function testJobStatus()
    { 
        $testData = ["test_data"=>time()];
        $id = Queue::push("TestUnitJob",$testData);
        $status1 = Queue::jobStatus($id);
        $this->assertEquals(Resque_Job_Status::STATUS_WAITING, $status1);
        $this->assertTrue(Queue::isWaiting($id));
        while($job = Queue::reserve()) {
            $this->assertTrue($job instanceof Resque_Job);
            if(empty(array_diff($job->payload["args"][0], $testData))) {
                break;
            }
        }
        $job -> updateStatus (Resque_Job_Status::STATUS_RUNNING);
        $status2 = Queue::jobStatus($id);
        $this->assertEquals(Resque_Job_Status::STATUS_RUNNING, $status2);
    }
    
    
    public function testLaterPushByDate() {
        
        $this->clearRedisDates();
        $this->clearRedisRecord("TestUnitJob::fire");
        
        $redis = $this->redis;
        $date = Carbon::now()->addSeconds(4);
        $testValue=time();
        $testData = ["test_data_by_date"=>$testValue];
        Queue::later($date, 'TestUnitJob', $testData, $this->config['queue']);
           
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
        usleep(100000);

        $getSettedValue=$redis->get('TestUnitJob::fire');
        $this->assertEquals(json_encode($testData), $getSettedValue);
        
    }
    
    public function testLaterPushByOffsetSeconds() {
        $this->clearRedisDates();
        $redis = $this->redis;
        $testValue=time();
        $testData = ["test_data_by_date"=>$testValue];
        Queue::later(3, 'TestUnitJob', $testData, $this->config['queue']);
           
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
    
    public function testPushDafaultQueue()
    { 
        $queue = Config::get("queue.connections.resque.queue");
        $this->forkListen($queue, 1, null);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==1;
        },15));

        //Artisan::call('resque:push', ['--job'=>TestUnitJob::class, '--args'=> json_encode($dataArgs)]);  
        $testData = ["test_data"=>time()];
        $id1 = Queue::push("TestUnitJob",$testData);
        $id2 = Queue::push("TestUnitJob",$testData, "diff_queue_name");
        
        $this->assertTrue($this->waitFor(function() use ($id1) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id1);
        },10));
        $this->assertFalse($this->waitFor(function() use ($id2) {
            return Resque_Job_Status::STATUS_COMPLETE == Queue::jobStatus($id2);
        },10));
    }
}