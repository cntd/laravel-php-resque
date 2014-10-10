<?php namespace Kodeks\PhpResque\Lib;

class ResqueOutputRedis
{
	public static function add($payload, $output, ResqueWorkerEx $worker, $queue, $expire = 3600)
	{
            $data = new \stdClass;
            $data->output_at = strftime('%a %b %d %H:%M:%S %Z %Y');
            $data->payload = $payload;
            $data->output = $output;
            $data->worker = (string)$worker;
            $data->queue = $queue;
            ResqueLog::log($data, $worker, 'output', $expire);
	}
        
        public static function error($payload, $exception, ResqueWorkerEx $worker, $queue, $expire = 3600)
	{
            $data = new \stdClass;
            $data->failed_at = strftime('%a %b %d %H:%M:%S %Z %Y');
            $data->payload = $payload;
            $data->exception = get_class($exception);
            $data->error = $exception->getMessage();
            $data->backtrace = explode("\n", $exception->getTraceAsString());
            $data->worker = (string)$worker;
            $data->queue = $queue;
            ResqueLog::log($data, $worker, 'error', $expire);
	}
}