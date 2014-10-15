<?php namespace Kodeks\PhpResque\Console;

use Symfony\Component\Console\Input\InputOption;
use Kodeks\PhpResque\Console\ResqueCommand;

class PushCommand extends ResqueCommand {
    protected $name = 'resque:push';
    protected $description = 'Push job in queue';

    public function __construct(){
        parent::__construct();
    }

    public function fire() {
        $queue = $this->input->getOption('queue') ? $this->input->getOption('queue') : false;
        $job = $this->input->getOption('job');
        $args = $this->input->getOption('args') ? $this->input->getOption('args') : [];
        if(!empty($args)) {
            $args = (array)json_decode($args);
        }
        if(!$queue) {
            \Queue::push($job, $args);    
        } else {
            \Queue::push($job, $args, $queue);
        }
    }

    protected function getOptions()
    {
        return [
            ['queue', NULL, InputOption::VALUE_OPTIONAL, 'Name of queue', false],
            ['job', NULL, InputOption::VALUE_REQUIRED, 'Name of job-class', false],
            ['args', NULL, InputOption::VALUE_OPTIONAL, 'Job\'s arguments in json format', false],
        ];
    }
}