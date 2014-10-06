<?php namespace Kodeks\PhpResque;

use Illuminate\Queue\QueueServiceProvider;
use Config;
use Kodeks\PhpResque\Connectors\ResqueConnector;
use Kodeks\PhpResque\Console\ListenCommand;
use Kodeks\PhpResque\Console\SchedulerCommand;

class PhpResqueServiceProvider extends QueueServiceProvider {

        public function __construct($app) {
            parent::__construct($app); 
        }
        
	public function registerConnectors($manager) {
            parent::registerConnectors($manager);
            $this->registerResqueConnector($manager);
        }
        
        protected function registerResqueConnector($manager) { 
            $connections = Config::get('queue.connections', []);
            foreach ($connections as $connection) {
                if ($connection['driver'] !== 'resque') {
                    $manager->addConnector($connection['driver'], function () {
                        return new ResqueConnector();
                    });
                }
            }
            $manager->addConnector('resque', function () {
                $config = Config::get('database.redis.default');
                Config::set('queue.connections.resque', array_merge($config, ['driver' => 'resque']));
                return new ResqueConnector();
            });
        }
    
	protected $defer = false;

	public function boot() {  
            parent::boot();
            $this->app->bind('kodeks::command.resque.listen', function($app) {
                return new ListenCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.listen'
            ));
            $this->app->bind('kodeks::command.resque.scheduler', function($app) {
                return new SchedulerCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.scheduler'
            ));
   
            $this->package('kodeks/php-resque');
	}

}