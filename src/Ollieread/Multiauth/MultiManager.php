<?php namespace Ollieread\Multiauth;

use Illuminate\Foundation\Application;

class MultiManager {
	
	
	/**
	 * @var Illuminate\Foundation\Application $app
	 */
	 
	protected $app;
	
	protected $config;
	
	protected $providers = array();
	
	public function __construct(Application $app) {
		$this->app = $app;
		$this->config = $this->app['config']['auth.multi'];
		
		foreach($this->config as $key => $config) {
			$this->providers[$key] = new AuthManager($this->app, $key, $config);
		}
	}
	
	public function __call($name, $arguments = array()) {
		if(array_key_exists($name, $this->providers)) {
			return $this->providers[$name];
		}
	}
	
}
