<?php namespace Kodeks\PhpResque\Console;

use Symfony\Component\Console\Input\InputOption;
use Kodeks\PhpResque\Lib\ResqueSchedulerWorkerEx;
use Kodeks\PhpResque\Console\ResqueCommand;

class SchedulerCommand extends ResqueCommand {
    protected $name = 'resque:scheduler';
    protected $description = 'Run a resque scheduler worker';

    public function __construct(){
        parent::__construct();
    }

    public function fire() {
        // Read input
        $interval = $this->input->getOption('interval');

        $pid = pcntl_fork();
        if($pid == -1) {
            die("Could not fork scheduler worker\n");
        } else if(!$pid) {
            $this->info('*** Starting scheduler worker ***');
            $schedulerWorker = new ResqueSchedulerWorkerEx();
            $schedulerWorker->work($interval);
        }
        
        sleep(1);
    }

    protected function getOptions()
    {
        return [
            ['interval', NULL, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 5],
        ];
    }
}