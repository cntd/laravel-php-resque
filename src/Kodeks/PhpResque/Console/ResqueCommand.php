<?php namespace Kodeks\PhpResque\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Resque;

abstract class ResqueCommand extends Command {
    
    const DEFAULT_QUEUE = 'default';
    const LOG_EXPIRE_DEFAULT = 3600;
    
    protected $config;

    public function __construct(){
        parent::__construct();
        $this->initRedis();
    }
    
    protected function initRedis() {
        $this->config = array_merge(Config::get('database.redis.default', Config::get('queue.connections.resque', [])));
        if (!isset($this->config['host'])) {
            $this->config['host'] = '127.0.0.1';
        }
        if (!isset($this->config['port'])) {
            $this->config['port'] = 6379;
        }
        if (!isset($this->config['database'])) {
            $this->config['database'] = 0;
        }
        if (!isset($this->config['database'])) {
            $this->config['database'] = 0;
        }
        Resque::setBackend($this->config['host'].':'.$this->config['port'], $this->config['database']);
    }
    abstract function fire();
}