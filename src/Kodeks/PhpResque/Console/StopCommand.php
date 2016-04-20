<?php namespace Kodeks\PhpResque\Console;

use Symfony\Component\Console\Input\InputOption;
use Kodeks\PhpResque\Console\ResqueCommand;
use Kodeks\PhpResque\Lib\ResqueWorkerEx;
use Kodeks\PhpResque\Lib\ResqueSchedulerWorkerEx;

class StopCommand extends ResqueCommand {
    protected $name = 'resque:stop';
    protected $description = 'Stop worker(s) command';

    public function __construct(){
        parent::__construct();
    }

    public function fire() {
        // Read input
        $stop_all = $this->input->getOption('all') ? true : false;
        $scheduler = $this->input->getOption('scheduler') ? true : false;
        $pid = $this->input->getOption('pid') ? $this->input->getOption('pid') : false;
        $queue = $this->input->getOption('queue') ? $this->input->getOption('queue') : false;
        $force = $this->input->getOption('force') ? true : false;

        $signal = $force ? SIGTERM : SIGQUIT;
        $optionSet = false;

        if($stop_all && $pid) {
            $this->error("Option --all and --pid can't be defined at same time");
            return;
        }
        
        if($scheduler && ($stop_all || $pid)) {
            $this->error("Option --scheduler must be defined separately");
            return;
        }
        
        if($scheduler) {
            if(ResqueSchedulerWorkerEx::isRunning()) {
                $this->info("Shutdown scheduler process");
                ResqueSchedulerWorkerEx::shutdown();
            } else {
                $this->info("No scheduler process found");
            }
            return;
        }

        if($queue) {
            $optionSet = true;
            $workers=ResqueWorkerEx::findByQueue($queue);
            if(empty($workers)) {
                $this->info("No workers found by queue: " . $queue);
                return;
            }
        }

        if($stop_all) {
            $optionSet = true;
            $workers=ResqueWorkerEx::all();
            $isRunning = ResqueSchedulerWorkerEx::isRunning();
            if($isRunning) {
                $this->info("Shutdown scheduler process");
                ResqueSchedulerWorkerEx::shutdown();
            }
            if(empty($workers)) {
                $this->info("No workers found");
                return;
            }
        }

        if($pid) {
            $optionSet = true;
            $worker = ResqueWorkerEx::findByPid($pid);
            if(!$worker) {
                $this->info("No workers found by PID: " . $pid);
                return;
            }
            $workers = [$worker];
        }

        if(!$optionSet) {
            $this->error("No options defined");
            return;
        }

        foreach($workers as $worker) {
            $result = $worker->signal($signal);
            $this->info("Sending a signal for worker: " . $worker . " Result: " . ($result ? "OK" : "ERROR" ));
        }
    }

    protected function getOptions()
    {
        return [
            ['all', NULL, InputOption::VALUE_NONE, 'All workers will be affected'],
            ['scheduler', NULL, InputOption::VALUE_NONE, 'Stop scheduler only'],
            ['pid', NULL, InputOption::VALUE_OPTIONAL, 'Worker\'s pid', false],
            ['queue', NULL, InputOption::VALUE_OPTIONAL, 'All workers of that queue will be affected', false],
            ['force', NULL, InputOption::VALUE_NONE, 'Stop worker immediately'],
        ];
    }
}