<?php namespace Ollieread\Multiauth;

use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Support\Facades\Config;

class MultiauthServiceProvider implements UserProviderInterface {
	
	protected $defer = false;

	protected $providers;
	
	public function __construct() {
		$providers = Config::get('auth.multi');
		
		$this->providers = $providers;
	}

	public function retrieveByCredentials(array $credentials) {
		$provider = $credentials['provider'];
		
		if($provider && $this->providers[$provider]) {
			$query = $this->createModel($this->providers[$provider])->newQuery();

			foreach($credentials as $key => $value) {
				if(!str_contains($key, 'password') && ! str_contains($key, 'provider')) {
					$query->where($key, $value);
				}
			}

			$user = $query->first();

			if($user) {
				Session::put('user.provider', $provider);
				$user->provider = $provider;
			}

			return $user;
		}
		
		return null;
	}

	public function retrieveById($identifier) {
		$provider = Session::get('user.provider');
		
		if($provider && $this->providers[$provider]) {
			$user = $this->createModel($this->providers[$provider])->newQuery()->find($identifier);
			
			if($user) {
				$user->provider = $provider;
			}
			
			return $user;
		}
		
		return null;
	}

	public function validateCredentials(UserInterface $user, array $credentials) {
        $provider = $credentials['provider'];
		
		if($this->providers[$provider]) {
			$plain = $credentials['password'];

			return Hash::check($plain, $user->getAuthPassword());
		}
		
		return null;
	}
	
	public function createModel($name) {
		$class = '\\' . ltrim($name, '\\');
		
		return new $class;
	}

}