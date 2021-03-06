<?php namespace Kodeks\PhpResque\Connectors;

use Config;
use Resque;
use Kodeks\PhpResque\ResqueQueue;
use Illuminate\Queue\Connectors\ConnectorInterface;

class ResqueConnector implements ConnectorInterface {
    public function connect(array $config) {
        if (!isset($config['host'])) {
            $config['host'] = Config::get('database.redis.default.host');
            if (!isset($config['host'])) {
                $config['host'] = '127.0.0.1';
            }
        }
        if (!isset($config['port'])) {
            $config['port'] = 6379;
        }
        if (!isset($config['database'])){
            $config['database'] = 0;
        }
        if(!isset($config['queue'])) {
            $config['queue']='default';
        }
        Resque::setBackend($config['host'].':'.$config['port']);
        return new ResqueQueue($config['queue']);
    }
} 