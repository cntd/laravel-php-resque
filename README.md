laravel-php-resque
=============================


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

4) Remove dafault queue provider *'Illuminate\Queue\QueueServiceProvider'* from **app.php**

##Usage

>```
    Queue::push("NameOfJobClass",["someDataKey"=>"someData"]);
```
