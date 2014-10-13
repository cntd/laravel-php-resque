laravel-php-resque
=============================

##Запуск тестов из под workbench

./vendor/bin/phpunit workbench/kodeks/php-resque/tests/LogOutputTest

./vendor/bin/phpunit workbench/kodeks/php-resque/tests/ResqueQueueTest

./vendor/bin/phpunit workbench/kodeks/php-resque/tests/ListenCommandTest

./vendor/bin/phpunit workbench/kodeks/php-resque/tests/StopCommandTest

./vendor/bin/phpunit workbench/kodeks/php-resque/tests/PauseResumeCommandTest

./vendor/bin/phpunit workbench/kodeks/php-resque/tests/RestartCommandTest


##Installation

1) in **composer.json**:
>```
"require": {
		...
                "kodeks/php-resque": "dev-master"
	}
```

2) in **queue.php**:
>```
    'connections' => array(
            'resque' => array(
                    'driver' => 'resque',
                    'queue'  => 'default',
                    'log_time' => 60,
            ),
            ...
    ),
```

3) in **app.php**:
>```
    'providers' => array(
		...
                'Kodeks\PhpResque\PhpResqueServiceProvider',
	),
```

##Usage

>```
    Queue::push("NameOfJobClass",["someDataKey"=>"someData"]);
```