<?php namespace Kodeks\PhpResque\Console;

use Kodeks\PhpResque\Console\ResqueCommand;
use Kodeks\PhpResque\Lib\ResqueWorkerEx;


class RestartCommand extends ResqueCommand {

protected $name = 'resque:restart';
protected $description = 'Restart worker(s) command';

public function __construct(){
    parent::__construct();
}

public function fire() {
    $signal = SIGQUIT;
    $workers=ResqueWorkerEx::all();
    foreach($workers as $worker) {
        $result = $worker->signal($signal);
        $queues = implode(",", $worker->getQueues);
        $this->info("Senging stop signal for worker: " . $worker . " Result: " . ($result ? "OK" : "ERROR" ));
        while(ResqueWorkerEx::exists((string)$worker)) {
            usleep(100000);
        }
        Artisan::call('resque:listen', ['--count'=>1,'--queue'=>$queues]);
        
    }
}

protected function getOptions()
{
    return [
    ];
}
} 