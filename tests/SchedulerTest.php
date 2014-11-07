<?php
require_once 'utils/CommandsTestCase.php';

use Kodeks\PhpResque\Lib\ResqueSchedulerWorkerEx;

class SchedulerTest extends CommandsTestCase {
    public function testStartDownScheduler()
    { 
        ResqueSchedulerWorkerEx::shutdown();
        Artisan::call('resque:listen',[
                    '--count'=>0,
                    '--scheduler'=>1,
                    ]);
        $this->assertTrue($this->waitFor(function() {
                return ResqueSchedulerWorkerEx::isRunning();
        },10));
        Artisan::call('resque:stop',[
                    '--all'=>1,
                    ]);
        $this->assertTrue($this->waitFor(function() {
                return !ResqueSchedulerWorkerEx::isRunning();
        },10));
        Artisan::call('resque:scheduler',[
                    ]);
        $this->assertTrue($this->waitFor(function() {
                return ResqueSchedulerWorkerEx::isRunning();
        },10));
        Artisan::call('resque:stop',[
                    '--scheduler'=>1,
                    ]);
        $this->assertTrue($this->waitFor(function() {
                return !ResqueSchedulerWorkerEx::isRunning();
        },10));
    }
}
