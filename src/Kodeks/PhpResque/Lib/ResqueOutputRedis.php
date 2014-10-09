<?php namespace Kodeks\PhpResque\Lib;

class ResqueOutputRedis
{
	public static function add($payload, $output, $worker, $queue)
	{
            $data = new \stdClass;
            $data->output_at = strftime('%a %b %d %H:%M:%S %Z %Y');
            $data->payload = $payload;
            $data->output = $output;
            $data->worker = (string)$worker;
            $data->queue = $queue;
            $data_json = json_encode($data);
            \Resque::redis()->rpush('output', $data_json);
	}
}