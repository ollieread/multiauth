<?php namespace Ollieread\Multiauth;

use Illuminate\Auth\AuthManager as OriginalAuthManager;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Auth\EloquentUserProvider;

class AuthManager extends OriginalAuthManager {
	
	protected $config;
	protected $name;
	
	public function __construct($app, $name, $config) {
		parent::__construct($app);
		
		$this->config = $config;
		$this->name = $name;
	}
	
	protected function createDriver($driver) {
		$guard = parent::createDriver($driver);
		
		$guard->setCookieJar($this->app['cookie']);
		$guard->setDispatcher($this->app['events']);

		return $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
	}
	
	protected function callCustomCreator($driver) {
		$custom = parent::callCustomCreator($driver);

		if ($custom instanceof Guard) return $custom;

		return new Guard($custom, $this->app['session.store'], $this->name);
	}
	
	public function createDatabaseDriver() {
		$provider = $this->createDatabaseProvider();

		return new Guard($provider, $this->app['session.store'], $this->name);
	}
	
	protected function createDatabaseProvider() {
		$connection = $this->app['db']->connection();
		$table = $this->config['table'];

		return new DatabaseUserProvider($connection, $this->app['hash'], $table);
	}
	
	public function createEloquentDriver() {
		$provider = $this->createEloquentProvider();

		return new Guard($provider, $this->app['session.store'], $this->name);
	}
	
	protected function createEloquentProvider() {
		$model = $this->config['model'];

		return new EloquentUserProvider($this->app['hash'], $model);
	}
	
	public function getDefaultDriver() {
		return $this->config['driver'];
	}

	public function setDefaultDriver($name)
	{
		$this->config['driver'] = $name;
	}

}
