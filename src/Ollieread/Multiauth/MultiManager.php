<?php namespace Ollieread\Multiauth;

use Illuminate\Foundation\Application;

class MultiManager {
	
	
	/**
	 * @var Illuminate\Foundation\Application $app
	 */
	 
	protected $app;
	
	protected $config;
	
	protected $providers = array();
	
	protected $globals;
	
	public function __construct(Application $app) {
		$this->app = $app;
		$this->config = $this->app['config']['auth.multi'];
		$this->globals = $this->app['config']['auth.globals'];		
		foreach($this->config as $key => $config) {
			$this->providers[$key] = new AuthManager($this->app, $key, $config);
		}
	}
	
	public function __call($name, $arguments = array()) {
		
		if(array_key_exists($name, $this->providers)) {
			if(null != $this->globals && in_array($name, $this->globals)){
				return $this->providers[$name]->$name();
			}else{
				return $this->providers[$name];
			}
		}
	}
	
}
