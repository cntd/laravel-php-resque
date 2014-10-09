<?php namespace Kodeks\PhpResque\Console;

use Kodeks\PhpResque\Console\ResqueCommand;
use Kodeks\PhpResque\Lib\ResqueWorkerEx;
use Illuminate\Support\Facades\Artisan;

class RestartCommand extends ResqueCommand {

    protected $name = 'resque:restart';
    protected $description = 'Restart worker(s) command';

    public function __construct(){
        parent::__construct();
    }

    public function forkListen($queue, $interval, $count=1, $output = null) {
        $pid = pcntl_fork();
        if ($pid == -1) {
             die('could not fork');
        } else if (!$pid) {
             // we are the child
            if($output !== null) {
                Artisan::call('resque:listen',['--queue'=>$queue, '--count'=>$count, '--interval'=>$interval], $output);
            } else {
                Artisan::call('resque:listen',['--queue'=>$queue, '--count'=>$count, '--interval'=>$interval]);    
            }
            die();
        } 
        return $pid;
    }

    public function fire() {
        $signal = SIGQUIT;
        $workers = ResqueWorkerEx::all();
        foreach($workers as $worker) {
            $interval = $worker->getInterval();
            $result = $worker->signal($signal);
            $queues = implode(",", $worker->getQueues());
            $this->info("Senging stop signal for worker: " . $worker . " Result: " . ($result ? "OK" : "ERROR" ));
            while(ResqueWorkerEx::exists((string)$worker)) {
                usleep(500000);
            }
            $this->forkListen($queues, $interval, 1);
        }
    }

    protected function getOptions()
    {
        return [
        ];
    }
} 