<?php namespace Kodeks\PhpResque\Jobs;

use Kodeks\PhpResque\Lib\ResqueJobInterface;

class SendMail implements ResqueJobInterface
{
	public function perform()
	{
		\Log::info('SendMail params:' . json_encode($this->args));
		$data = $this->args["data"];
		$view = $this->args["view"];
		$callback = $this->args['callback'];
		\Mail::send($view, $data, $callback);
	}
}
