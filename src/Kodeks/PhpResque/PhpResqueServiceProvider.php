<?php namespace Kodeks\PhpResque;

use Illuminate\Queue\QueueServiceProvider;
use Config;
use Kodeks\PhpResque\Connectors\ResqueConnector;
use Kodeks\PhpResque\Console\ListenCommand;
use Kodeks\PhpResque\Console\SchedulerCommand;
use Kodeks\PhpResque\Console\StatCommand;
use Kodeks\PhpResque\Console\StopCommand;
use Kodeks\PhpResque\Console\PauseCommand;
use Kodeks\PhpResque\Console\ResumeCommand;
use Kodeks\PhpResque\Console\RestartCommand;
use Kodeks\PhpResque\Console\PushCommand;

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

            $this->app->bind('kodeks::command.resque.stat', function($app) {
                return new StatCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.stat'
            ));

            $this->app->bind('kodeks::command.resque.resume', function($app) {
                return new ResumeCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.resume'
            ));

            $this->app->bind('kodeks::command.resque.pause', function($app) {
                return new PauseCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.pause'
            ));

            $this->app->bind('kodeks::command.resque.stop', function($app) {
                return new StopCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.stop'
            ));


            $this->app->bind('kodeks::command.resque.restart', function($app) {
                return new RestartCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.restart'
            ));

            $this->app->bind('kodeks::command.resque.push', function($app) {
                return new PushCommand();
            });
            $this->commands(array(
                'kodeks::command.resque.push'
            ));

            $this->package('kodeks/php-resque');

			/* В собственный provider
			$this->app['mailer'] = $this->app->share( function ($app){
				return new \Kodeks\PhpResque\Mailer\Mailer($app['view'], $app['swift.mailer'], $app['events']);
			});
			*/

			$this->app->booting( function (){
				$loader = \Illuminate\Foundation\AliasLoader::getInstance();
				$loader->alias('Mailer', '\Kodeks\PhpResque\Mailer\Mailer');
			});

	}

}
