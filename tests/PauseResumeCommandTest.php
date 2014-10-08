<?php
use Kodeks\PhpResque\Lib\ResqueWorkerEx;
use Symfony\Component\Console\Output\BufferedOutput;
require_once 'utils/CommandsTestCase.php';

class PauseResumeCommandTest extends CommandsTestCase {

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
    
    public function testPause()
    { 
        $this->unlockTest("testPause");
        $testQueue='testPause';
        $startOutput = new BufferedOutput;
        Artisan::call('resque:listen',['--queue'=>$testQueue, '--count'=>1], $startOutput);
        $this->lockTest("testPause");
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(1, count($started));
        
        $job_id1 = Queue::push("TestUnitJob",[],$testQueue);
        sleep(5);
        $this->assertFalse(Queue::isWaiting($job_id1));
        Artisan::call('resque:pause',['--all'=>1]);
        $job_id2 = Queue::push("TestUnitJob",[],$testQueue);
        sleep(5);
        $this->assertTrue(Queue::isWaiting($job_id2));
    }
    
    public function testResume()
    { 
        $this->unlockTest("testResume");
        $testQueue='testPause';
        $startOutput = new BufferedOutput;
        Artisan::call('resque:listen',['--queue'=>$testQueue, '--count'=>1], $startOutput);
        $this->lockTest("testResume");
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(1, count($started));
        
        Artisan::call('resque:pause',['--all'=>1]);
        pcntl_wait($status);
        
        $job_id1 = Queue::push("TestUnitJob",[],$testQueue);
        sleep(5);
        $this->assertTrue(Queue::isWaiting($job_id1));
        Artisan::call('resque:resume',['--all'=>1]);
        $job_id2 = Queue::push("TestUnitJob",[],$testQueue);
        sleep(10);
        $this->assertFalse(Queue::isWaiting($job_id2));
    }


}    