<?php namespace Kodeks\PhpResque\Lib;

class ResqueJobEx extends \Resque_Job {
    public function fail($exception)
    {
            Resque_Event::trigger('onFailure', array(
                    'exception' => $exception,
                    'job' => $this,
            ));

            $this->updateStatus(Resque_Job_Status::STATUS_FAILED);

            Resque_Stat::incr('failed');
            Resque_Stat::incr('failed:' . $this->worker);
    }
}