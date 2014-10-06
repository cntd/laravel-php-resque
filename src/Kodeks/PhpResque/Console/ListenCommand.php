<?php namespace Kodeks\PhpResque\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Config;
use Resque;
use Resque_Log;
use Resque_Worker;
use ResqueScheduler_Worker;

class ListenCommand extends Command {

protected $name = 'resque:listen';
protected $description = 'Run a resque worker';

const DEFAULT_QUEUE = 'default';

public function __construct(){
    parent::__construct();
}

public function fire() {
    // Read input
    $logLevel = $this->input->getOption('verbose') ? true : false;
    $queue = $this->input->getOption('queue');
    $interval = $this->input->getOption('interval');
    $count = $this->input->getOption('count');
    $blocking = $this->input->getOption('blocking');
    $scheduler = $this->input->getOption('scheduler') ? true : false;
    $schedulerInterval = $this->input->getOption('scheduler-interval') ? $this->input->getOption('interval') : $interval;
    
    // Configuration
    $config = array_merge(Config::get('database.redis.default'),Config::get('queue.connections.resque'));
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
    
    $queues = explode(',', $queue);
    $logger = new Resque_Log($logLevel);
    
    $this->info('Starting worker(s)...');

    $pid = -1;
    for($i = 0; $i < $count; ++$i) {
        $pid = Resque::fork();
        if($pid == -1) {
            $logger->log('emergency', 'Could not fork worker {count}', array('count' => $i));
            die();
        }
        // Child, start the worker
        else if(!$pid) {
            $worker = new Resque_Worker($queues);
            $worker->setLogger($logger);
            $logger->log('notice', 'Starting worker {worker}', array('worker' => $worker));
            $worker->work($interval, $blocking);
            break;
        }
    }
    
    if($scheduler && $pid) {
        $this->info('Starting scheduler worker...');
        $schedulerWorker = new ResqueScheduler_Worker();
        $schedulerWorker -> work($schedulerInterval);
    }
}

protected function getOptions()
{
    return [
        ['queue', NULL, InputOption::VALUE_OPTIONAL, 'The queue to listen on', false],
        ['interval', NULL, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 5],
        ['count', NULL, InputOption::VALUE_OPTIONAL, 'Number of workers to create', 1],
        ['blocking', NULL, InputOption::VALUE_OPTIONAL, 'With blocking timeout', false],
        ['scheduler', NULL, InputOption::VALUE_OPTIONAL, 'With scheduler worker', false],
        ['scheduler-interval', NULL, InputOption::VALUE_OPTIONAL, 'Scheduler interval', false],
    ];
}
} 