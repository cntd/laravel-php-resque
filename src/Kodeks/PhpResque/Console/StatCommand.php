<?php namespace Kodeks\PhpResque\Console;

use Resque;
use Kodeks\PhpResque\Lib\ResqueStat;
use ResqueScheduler;
use Resque_Stat;
use Kodeks\PhpResque\Console\ResqueCommand;
use Carbon;

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
        
        $this->info('Jobs Stats...');
        $this->info(self::TAB1.'Processed Jobs : ' . number_format(Resque_Stat::get('processed')));
        $this->info(self::TAB1.'Failed Jobs : ' . number_format(Resque_Stat::get('failed')));
        $this->info(self::TAB1.'Scheduled Jobs : ' . number_format(ResqueScheduler::getDelayedQueueScheduleSize()));
        $this->info('');
        
        $this->info('Queues stats...');
        $this->info('Queues count: ' . count($queues));
        foreach($queues as $queue) {
            $this->info(self::TAB1."Queue: \"" . $queue . "\" Pending: " . $stat->getQueueLength($queue));
        }
        $this->info('');
        
        $this->info('Workers stats...');
        $this->info('Workers active: ' . count($workers));
        foreach($workers as $worker) {
            $carbon = new Carbon($stat->getWorkerStartDate($worker));
            $this->info(self::TAB1.'Worker ID: ' . $worker);
            $this->info(self::TAB2.'PID: ' . $worker->getPid());
            $this->info(self::TAB2.'Interval: ' . $worker->getInterval());
            $this->info(self::TAB2.'Started: ' . $carbon);
            $this->info(self::TAB2.'Uptime: ' . $carbon->diffForHumans());
            $this->info(self::TAB2.'Processed Jobs: ' . $worker->getStat('processed'));
            $this->info(self::TAB2.'Failed Jobs: ' . $worker->getStat('failed'));
        }
    }

    protected function getOptions()
    {
        return [
        ];
    }
}