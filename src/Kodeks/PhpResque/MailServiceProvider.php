<?php namespace Kodeks\PhpResque;

class MailServiceProvider extends \Illuminate\Mail\MailServiceProvider
{
	public function register()
	{
		$me = $this;

		$this->app->bindShared('mailer', function($app) use ($me)
		{
			$me->registerSwiftMailer();

			// Устанавливаем собственный класс
			$mailer = new \Kodeks\PhpResque\Mailer\Mailer(
				$app['view'], $app['swift.mailer'], $app['events']
			);
			/*
			$mailer = new Mailer(
				$app['view'], $app['swift.mailer'], $app['events']
			);
			*/
			$this->setMailerDependencies($mailer, $app);

			// If a "from" address is set, we will set it on the mailer so that all mail
			// messages sent by the applications will utilize the same "from" address
			// on each one, which makes the developer's life a lot more convenient.
			$from = $app['config']['mail.from'];

			if (is_array($from) && isset($from['address']))
			{
				$mailer->alwaysFrom($from['address'], $from['name']);
			}

			// Here we will determine if the mailer should be in "pretend" mode for this
			// environment, which will simply write out e-mail to the logs instead of
			// sending it over the web, which is useful for local dev environments.
			$pretend = $app['config']->get('mail.pretend', false);

			$mailer->pretend($pretend);

			return $mailer;
		});
	}
}
