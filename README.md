# Laravel Multi Auth #

This package is not a replacement for laravels default Auth library, but instead something
that sits between your code and the library.

Think of it as a factory class for Auth. Now, instead of having a single table/model to
authenticate users against, you can now have multiple, and unlike the previous version of
this package, you have access to all functions, and can even use a different driver 
for each user type.

On top of that, you can use multiple authentication types, simultaneously, so you can be logged
in as a user, a master account and an admin, without conflicts!

## Installation ##

Firstly you want to include this package in your composer.json file.

    "require": {
    		"ollieread/multiauth": "2.0.*@dev"
    }

Next you open up app/config/app.php and replace the AuthServerProvider with

    Ollieread\Multiauth\MultiauthServiceProvider

Configuration is pretty easy too, take app/config/auth.php with its default values:

    return array(

		'driver' => 'eloquent',

		'model' => 'User',

		'table' => 'users',

		'reminder' => array(

			'email' => 'emails.auth.reminder',

			'table' => 'password_reminders',

			'expire' => 60,

		),

	);

Now remove the first three options and replace as follows:

   return array(

		'multi'	=> array(
			'account' => array(
				'driver' => 'eloquent',
				'model'	=> 'Account'
			),
			'user' => array(
				'driver' => 'database',
				'table' => 'users'
			)
		),

		'reminder' => array(

			'email' => 'emails.auth.reminder',

			'table' => 'password_reminders',

			'expire' => 60,

		),

	);


## Usage ##

Everything is done the exact same way as the original library, the one exception being
that all method calls are prefixed with the key (account or user in the above examples)
as a method itself.

    Auth::account()->attempt(array(
    	'email'		=> $attributes['email'],
    	'password'	=> $attributes['password'],
    ));
    Auth::user()->attempt(array(
    	'email'		=> $attributes['email'],
    	'password'	=> $attributes['password'],
    ));
    Auth::account()->check();
    Auth::user()->check();

And so on and so forth.

There we go, done! Enjoy yourselves.