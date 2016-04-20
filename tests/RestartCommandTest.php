<?php
use Symfony\Component\Console\Output\BufferedOutput;
use Kodeks\PhpResque\Lib\ResqueWorkerEx;

require_once 'utils/CommandsTestCase.php';

class RestartCommandTest extends CommandsTestCase {

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
    
    public function testRestart()
    { 
        $testQueue='testCommandStopByPid';
        $startOutput = new BufferedOutput;
        $this->forkListen($testQueue, 3, $startOutput);
        $this->assertTrue($this->waitFor(function() {
            return count(ResqueWorkerEx::all())==3;
        },10));
        
        $pids=[];
        foreach(ResqueWorkerEx::all() as $worker) {
            $pids[] = $worker->getPid();
        }
        
        Artisan::call('resque:restart', []);
        
        $this->assertTrue($this->waitFor(function() use ($pids) {
            $pidsChanged = true;
            $workers = ResqueWorkerEx::all();
            foreach($workers as $worker) {
                if(in_array($worker->getPid(), $pids)) {
                    $pidsChanged = false;
                }
            }
            return $pidsChanged && count($workers)==3;
        },15));
        
    }


}    