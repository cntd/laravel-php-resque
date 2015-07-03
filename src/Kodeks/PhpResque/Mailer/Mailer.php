<?php namespace Kodeks\PhpResque\Mailer;

use Kodeks\PhpResque\Lib\ResqueJobInterface;

class Mailer extends \Illuminate\Mail\Mailer {

	public function queue($view, array $data, $callback, $queue = null)
	{
		$callback = $this->buildQueueCallable($callback);
		return \Queue::push(\Kodeks\PhpResque\Jobs\SendMail::class, compact('view', 'data', 'callback'), $queue);
	}

/*
    public function handleQueuedMessage($job, $data) {
        Log::info('Sending mail data: ',$data);
        try {

            $cb = isset($data["callback"]) ? unserialize($data["callback"]) : null;
            if(!($cb instanceof SerializableClosure)) {
                throw new Exception("Callback function error");
            }

            $callback=false;
            $varibles=$cb->getVariables();
            foreach($varibles as $k=>$varib) {
                $$k=$varib;
            }
            eval('$callback = '.$cb->getCode());
            Config::get('app.debug', []) ? Mail::pretend() : null;
            Mail::send($data['view'], $data['data'], $callback);
        } catch (Exception $e) {
            Log::error('Send mail error:'.$e->getMessage(), $e->getTrace());
        }

    }
*/
}
