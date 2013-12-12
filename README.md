# Laravel Multi Auth #

A custom auth driver to allow for multiple authentication models.

Sometimes you'll have a project where you require multiple types of users, and a single table with a column to switch wouldn't suffice.

## Installation ##

Firstly you want to include this package in your composer.json file.

    "require": {
    		"ollieread/multiauth": "1.*"
    }

Next you'll want to modify app/start/global.php to extend the Auth provider.

    Auth::extend('multi', function($app) {
    	$provider = new Ollieread\Multiauth\MultiauthServiceProvider();
    	
    	return new \Illuminate\Auth\Guard($provider, $app['session.store']);
    });

Configuration is pretty easy too, just modify app/config/auth.php as follows:

    return array(
    
    	'driver' => 'multi',
    	
    	'multi'	=> array(
    		'account'	=> 'Account',
    		'user'		=> 'User'
    	)
    
    );

## Usage ##

When you log a user in, just make sure to pass in a provider, like this:

    Auth::attempt(array(
    	'email'		=> $attributes['email'],
    	'password'	=> $attributes['password'],
    	'provider'	=> 'account'
    ));

There we go, done! Enjoy yourselves.