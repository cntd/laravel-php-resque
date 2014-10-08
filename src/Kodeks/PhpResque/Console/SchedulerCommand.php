<?php namespace Kodeks\PhpResque\Console;

use Symfony\Component\Console\Input\InputOption;
use ResqueScheduler_Worker;
use Kodeks\PhpResque\Console\ResqueCommand;

class SchedulerCommand extends ResqueCommand {

protected $name = 'resque:scheduler';
protected $description = 'Run a resque scheduler worker';

public function __construct(){
    parent::__construct();
}

public function fire() {
    // Read input
    $queue = $this->input->getOption('queue');
    $interval = $this->input->getOption('interval');

    if(!$queue) {
        $queue = isset($this->config['queue']) ? $this->config['queue'] : self::DEFAULT_QUEUE;
    }

    $this->info("Queue: ".$queue);
    
    $this->info('Starting scheduler worker...');
    $schedulerWorker = new ResqueScheduler_Worker($queue);
    $schedulerWorker -> work($interval);
}

protected function getOptions()
{
    return [
        ['queue', NULL, InputOption::VALUE_OPTIONAL, 'The queue to listen on', false],
        ['interval', NULL, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 5],
    ];
}
} // End Listen