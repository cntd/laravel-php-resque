<?php namespace Kodeks\PhpResque;

use Illuminate\Queue\QueueServiceProvider;
use Config;
use Kodeks\PhpResque\Connectors\ResqueConnector;

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
                return new ResqueConnector;
            });
        }
    
	protected $defer = false;

	public function boot() {  
            parent::boot();
            $this->package('kodeks/php-resque');
	}

	public function register() {
		
	}

	public function provides() {
            return array();
	}
        

}
