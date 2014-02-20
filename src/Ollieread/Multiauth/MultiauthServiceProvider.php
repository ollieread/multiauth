<?php namespace Ollieread\Multiauth;

use Illuminate\Support\ServiceProvider;
use Ollieread\Multiauth\Console\RemindersTableCommand;

class MultiauthServiceProvider extends ServiceProvider {
	
	protected $defer = false;

	public function register() {
		$this->app->bindShared('auth', function($app) {
			$app['auth.loaded'] = true;
			
			return new MultiManager($app);
		});
		
		$this->app['command.multiauth.reminders-table'] = $this->app->share(function($app) {
			return new RemindersTableCommand($app['files']);
		});
		
		$this->commands('command.multiauth.reminders-table');
	}

}
