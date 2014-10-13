<?php
use Kodeks\PhpResque\Lib\ResqueWorkerEx;
use Symfony\Component\Console\Output\BufferedOutput;
require_once 'utils/CommandsTestCase.php';

class PauseResumeCommandTest extends CommandsTestCase {

    public function setUp(){
        parent::setUp();
        require_once 'utils/TestUnitJob.php';
        $this->redis=App::make('redis');
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));     
        $this->killWorkers();
    }
    
    public function tearDown(){
        parent::tearDown();
        $this->killWorkers();
        pcntl_wait($status);
    }
    
    public function testPause()
    { 
        $testQueue = 'testPause';
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 1, $startOutput);
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(1, count($started));
        
        $job_id1 = Queue::push("TestUnitJob",[],$testQueue);
        $this->assertTrue($this->waitFor(function() use ($job_id1) {
            return !Queue::isWaiting($job_id1);
        },15));

        
        Artisan::call('resque:pause',['--all'=>1]);
        $job_id2 = Queue::push("TestUnitJob",[],$testQueue);
        $this->assertFalse($this->waitFor(function() use ($job_id2) {
            return !Queue::isWaiting($job_id2);
        },15));
    }
    
    public function testResume()
    { 
        $testQueue='testPause';
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 1, $startOutput);
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(1, count($started));
        
        Artisan::call('resque:pause',['--all'=>1]);
        
        $job_id1 = Queue::push("TestUnitJob",[],$testQueue);

        $this->assertFalse($this->waitFor(function() use ($job_id1) {
            return !Queue::isWaiting($job_id1);
        },5));
        
        
        Artisan::call('resque:resume',['--all'=>1]);
        $job_id2 = Queue::push("TestUnitJob",[],$testQueue);
        $this->assertTrue($this->waitFor(function() use ($job_id2) {
            return !Queue::isWaiting($job_id2);
        },15));
    }


}    