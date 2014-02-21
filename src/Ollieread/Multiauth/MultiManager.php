<?php namespace Ollieread\Multiauth;

class MultiManager {
	
	protected $app;
	
	protected $config;
	
	protected $providers = array();
	
	public function __construct($app) {
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
