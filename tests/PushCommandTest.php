<?php
use Symfony\Component\Console\Output\BufferedOutput;
use Kodeks\PhpResque\Lib\ResqueWorkerEx;

require_once 'utils/CommandsTestCase.php';

class PushCommandTest extends CommandsTestCase {

    public function setUp(){ 
        parent::setUp();
        require_once 'utils/TestUnitJob.php';
        $this->redis=App::make('redis');
        $this->config = array_merge(Config::get('database.redis.default'), Config::get('queue.connections.resque'));     
        $this->killWorkers();
        $this->clearQueue($this->config['queue']);
    }
    
    public function tearDown(){
        parent::tearDown();
        $this->killWorkers();
    }
    
    public function testPush()
    { 
        Artisan::call('resque:push', ['--job'=>TestUnitJob::class, '--queue'=>$this->config['queue']]);  
        $data = Resque::pop($this->config['queue']);
        $this->assertEquals(TestUnitJob::class, $data["class"]);  
    }
    
    public function testPushArgs()
    { 
        $dataArgs = ["test"=>time()];    
        Artisan::call('resque:push', ['--job'=>TestUnitJob::class, '--args'=> json_encode($dataArgs), '--queue'=>$this->config['queue']]);  
        $data = Resque::pop($this->config['queue']);
        $this->assertEquals(TestUnitJob::class, $data["class"]);
        $this->assertEmpty(array_diff($dataArgs, $data['args'][0]));  
    }
    
}    