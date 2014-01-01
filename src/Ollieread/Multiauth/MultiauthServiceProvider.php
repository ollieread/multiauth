<?php namespace Ollieread\Multiauth;

use Illuminate\Support\ServiceProvider;

class MultiauthServiceProvider extends ServiceProvider {
	
	protected $defer = false;

	public function register() {
		$this->app->bindShared('auth', function($app) {
			$app['auth.loaded'] = true;
			
			return new MultiManager($app);
		});
	}

}
