<?php namespace Kodeks\PhpResque\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;
use Resque;
use ResqueScheduler_Worker;


class SchedulerCommand extends Command {

protected $name = 'resque:scheduler';
protected $description = 'Run a resque scheduler worker';

const DEFAULT_QUEUE = 'default';

public function __construct(){
    parent::__construct();
}

public function fire() {
    // Read input
    $queue = $this->input->getOption('queue');
    $interval = $this->input->getOption('interval');
    // Configuration
    $config = array_merge(Config::get('database.redis.default', Config::get('queue.connections.resque', [])));
    if (!isset($config['host'])) {
        $config['host'] = '127.0.0.1';
    }
    if (!isset($config['port'])) {
        $config['port'] = 6379;
    }
    if (!isset($config['database'])) {
        $config['database'] = 0;
    }
    
    if (!isset($config['database'])) {
        $config['database'] = 0;
    }
    
    if(!$queue) {
        $queue = isset($config['queue']) ? $config['queue'] : self::DEFAULT_QUEUE;
    }
    
    // Connect to redis
    Resque::setBackend($config['host'].':'.$config['port'], $config['database']);
    
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