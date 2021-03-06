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
        $testQueue='testCommandStopByPid';
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 1, $startOutput);
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(1, count($started));
        $worker = array_pop($started);
        $pid = $worker -> getPid();
        
        $output = new BufferedOutput;
        Artisan::call('resque:stop',['--pid'=>$pid], $output);
        $this->assertStringEndsWith('Result: OK', trim($output->fetch()));
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==0;
        },10));
    }

    public function testCommandStopByQueue()
    {
        $testQueue='testCommandStopByQueue';
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 2, $startOutput);
        sleep(1);
        $started = ResqueWorkerEx::findByQueue($testQueue);
        $this->assertEquals(2, count($started));
        
        $output = new BufferedOutput;
        Artisan::call('resque:stop',['--queue'=>$testQueue], $output);
        pcntl_wait($status);
        $this->assertStringEndsWith('Result: OK', trim($output->fetch()));
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==0;
        },10));
    }
    
    public function testCommandStopAll()
    {
        $testQueue='testCommandStopAll';
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 2, $startOutput);
        sleep(1);
        $countBeforeStop = count(ResqueWorkerEx::all());
        $this->assertEquals(2, $countBeforeStop);
        
        $output = new BufferedOutput;
        Artisan::call('resque:stop',['--all'=>1, '--force'=>1], $output);
        sleep(2);
        $this->assertEquals(2, substr_count($output->fetch(), 'Result: OK'));

        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==0;
        },15));
    }  
}    