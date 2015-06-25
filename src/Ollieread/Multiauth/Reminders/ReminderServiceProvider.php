<?php namespace Ollieread\Multiauth\Reminders;

use Illuminate\Support\ServiceProvider;
use Ollieread\Multiauth\Console\RemindersTableCommand;
use Ollieread\Multiauth\Console\ClearRemindersCommand;
use Ollieread\Multiauth\Reminders\DatabaseReminderRepository as DbRepository;

class ReminderServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerPasswordBroker();

		$this->registerReminderRepository();

		$this->registerCommands();
	}

	/**
	 * Register the password broker instance.
	 *
	 * @return void
	 */
	protected function registerPasswordBroker()
	{
		$this->app->bindShared('auth.reminder', function($app)
		{
			// The reminder repository is responsible for storing the user e-mail addresses
			// and password reset tokens. It will be used to verify the tokens are valid
			// for the given e-mail addresses. We will resolve an implementation here.
			$reminders = $app['auth.reminder.repository'];
			
			$providers = $views = array();
			
			foreach($app['config']['auth.multi'] as $type => $config) {
				$providers[$type] = $app['auth']->$type()->driver()->getProvider();
                $views[$type] = isset($config['email']) ? $config['email'] : $app['config']['auth.reminder']['email'];
			}

			// The password broker uses the reminder repository to validate tokens and send
			// reminder e-mails, as well as validating that password reset process as an
			// aggregate service of sorts providing a convenient interface for resets.
			return new PasswordBrokerManager(

				$reminders, $app['mailer'], $views, $providers

			);
		});
	}

	/**
	 * Register the reminder repository implementation.
	 *
	 * @return void
	 */
	protected function registerReminderRepository()
	{
		$this->app->bindShared('auth.reminder.repository', function($app)
		{
			$connection = $app['db']->connection();

			// The database reminder repository is an implementation of the reminder repo
			// interface, and is responsible for the actual storing of auth tokens and
			// their e-mail addresses. We will inject this table and hash key to it.
			$table = $app['config']['auth.reminder.table'];

			$key = $app['config']['app.key'];

			$expire = $app['config']->get('auth.reminder.expire', 60);

			return new DbRepository($connection, $table, $key, $expire);
		});
	}

	/**
	 * Register the multiauth related console commands.
	 *
	 * @return void
	 */
	protected function registerCommands()
	{
		$this->app->bindShared('command.multiauth.reminders', function($app)
		{
			return new RemindersTableCommand($app['files']);
		});

		$this->app->bindShared('command.multiauth.reminders.clear', function($app)
		{
			return new ClearRemindersCommand;
		});

		$this->commands(
			'command.multiauth.reminders', 'command.multiauth.reminders.clear'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('auth.reminder');
	}

}
