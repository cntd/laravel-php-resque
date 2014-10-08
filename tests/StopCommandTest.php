<?php
use Kodeks\PhpResque\Lib\ResqueWorkerEx;
use Symfony\Component\Console\Output\BufferedOutput;
require_once 'utils/CommandsTestCase.php';

class StopCommandTest extends CommandsTestCase {

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
    
    public function testCommandStopByPid()
    { 
        $this->unlockTest("testCommandStopByPid");
        $testQueue='testCommandStopByPid';
        $startOutput = new BufferedOutput;
        Artisan::call('resque:listen',['--queue'=>$testQueue, '--count'=>1], $startOutput);
        $this->lockTest("testCommandStopByPid");
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(1, count($started));
        $worker = array_pop($started);
        $pid = $worker -> getPid();
        
        $output = new BufferedOutput;
        Artisan::call('resque:stop',['--pid'=>$pid], $output);
        pcntl_wait($status);
        $this->assertStringEndsWith('Result: OK', trim($output->fetch()));
        $allAfterStop = ResqueWorkerEx::all();
        $this->assertEquals(0, count($allAfterStop)); 
    }
    
    public function testCommandStopByQueue()
    {
        $this->unlockTest("testCommandStopByQueue");
        $testQueue='testCommandStopByQueue';
        $startOutput = new BufferedOutput;
        Artisan::call('resque:listen',['--queue'=>$testQueue, '--count'=>2], $startOutput);
        $this->lockTest("testCommandStopByQueue");
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(2, count($started));
        
        $output = new BufferedOutput;
        Artisan::call('resque:stop',['--queue'=>$testQueue], $output);
        pcntl_wait($status);
        $this->assertStringEndsWith('Result: OK', trim($output->fetch()));
        $allAfterStop = ResqueWorkerEx::all();
        $this->assertEquals(0, count($allAfterStop));
    }
    
    public function testCommandStopAll()
    {
        $this->unlockTest("testCommandStopAll");
        $testQueue='testCommandStopAll';
        $startOutput = new BufferedOutput;
        Artisan::call('resque:listen',['--queue'=>$testQueue, '--count'=>2], $startOutput);
        $this->lockTest("testCommandStopAll");
        sleep(1);
        $countBeforeStop = count(ResqueWorkerEx::all());
        $this->assertEquals(2, $countBeforeStop);
        
        $output = new BufferedOutput;
        Artisan::call('resque:stop',['--all'=>1, '--force'=>1], $output);
        pcntl_wait($status);
        sleep(5);
        $this->assertEquals(2, substr_count($output->fetch(), 'Result: OK'));
        $allAfterStop = ResqueWorkerEx::all();
        $this->assertEquals(0, count($allAfterStop));
    }  
}    