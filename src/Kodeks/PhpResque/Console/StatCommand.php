<?php namespace Kodeks\PhpResque\Console;

use Resque;
use Kodeks\PhpResque\Lib\ResqueStat;
use ResqueScheduler;
use Resque_Stat;
use Kodeks\PhpResque\Console\ResqueCommand;
use Carbon\Carbon;
use Kodeks\PhpResque\Lib\ColorOutput;

class StatCommand extends ResqueCommand {
    protected $name = 'resque:stat';
    protected $description = 'Display total number of failed/processed jobs, as well as various stats for each workers and queues.';

    const TAB1 = "  ";
    const TAB2 = "     ";

    public function __construct(){
        parent::__construct();
    }

    public function fire() {
        $stat = new ResqueStat(Resque::redis());
        $queues = $stat->getQueues();
        $workers=$stat->getWorkers();
        
        $output = new ColorOutput();
        $this->info($output->getColoredString('Jobs Stats...', "light_purple"));
        $this->info(self::TAB1.'Processed Jobs : ' . 
                $output->getColoredString(number_format(Resque_Stat::get('processed')), "yellow"));
        $this->info(self::TAB1.'Failed Jobs : ' . 
                $output->getColoredString(number_format(Resque_Stat::get('failed')), "yellow"));
        $this->info(self::TAB1.'Scheduled Jobs : ' . 
                $output->getColoredString(number_format(ResqueScheduler::getDelayedQueueScheduleSize()), "yellow"));
        $this->info('');
        
        $this->info($output->getColoredString('Queues stats...', "light_purple"));
        $this->info('Queues count: ' . $output->getColoredString(count($queues), "yellow"));
        foreach($queues as $queue) {
            $this->info(self::TAB1."Queue: " . $output->getColoredString($queue, "light_green") . " Pending: " . 
                    $output->getColoredString($stat->getQueueLength($queue), "yellow"));
        }
        $this->info('');
        
        $this->info($output->getColoredString('Workers stats...', "light_purple"));
        $this->info('Workers active: ' . $output->getColoredString(count($workers), "yellow"));
        foreach($workers as $worker) {
            $carbon = new Carbon($stat->getWorkerStartDate($worker));
            $this->info(self::TAB1.'Worker ID: ' . $output->getColoredString($worker, "yellow"));
            $this->info(self::TAB2.'PID: ' . $output->getColoredString($worker->getPid(), "yellow"));
            $this->info(self::TAB2.'Interval: ' . $output->getColoredString($worker->getInterval(), "yellow"));
            $this->info(self::TAB2.'Started: ' . $output->getColoredString($carbon, "yellow"));
            $this->info(self::TAB2.'Uptime: ' . $output->getColoredString($carbon->diffForHumans(), "yellow"));
            $this->info(self::TAB2.'Processed Jobs: ' . $output->getColoredString($worker->getStat('processed'), "yellow"));
            $this->info(self::TAB2.'Failed Jobs: ' . $output->getColoredString($worker->getStat('failed'), "yellow"));
        }
    }

    protected function getOptions()
    {
        return [
        ];
    }
}