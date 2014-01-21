# Laravel Multi Auth #

[![Latest Stable Version](https://poser.pugx.org/ollieread/multiauth/v/stable.png)](https://packagist.org/packages/ollieread/multiauth) [![Total Downloads](https://poser.pugx.org/ollieread/multiauth/downloads.png)](https://packagist.org/packages/ollieread/multiauth) [![Latest Unstable Version](https://poser.pugx.org/ollieread/multiauth/v/unstable.png)](https://packagist.org/packages/ollieread/multiauth) [![License](https://poser.pugx.org/ollieread/multiauth/license.png)](https://packagist.org/packages/ollieread/multiauth)


- **Laravel**: 4.1
- **Author**: Ollie Read 
- **Author Homepage**: http://ollieread.com

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
    		"ollieread/multiauth": "dev-master"
    }

Next you open up app/config/app.php and replace the AuthServiceProvider with

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

I found that have to call the user() method on a user type called user() looked messy, so
I have added in a nice get method to wrap around it.

	Auth::user()->get();

In the instance where you have a user type that can impersonate another user type, example being
an admin impersonating a user to recreate or check something, I added in an impersonate() method
which simply wraps loginUsingId() on the request user type.

	Auth::admin()->impersonate('user', 1, true);

The first argument is the user type, the second is the id of said user, and the third is
whether or not to remember the user, which will default to false, so can be left out
more often than not.

And so on and so forth.

There we go, done! Enjoy yourselves.

## Known Bugs ##

Reminders don't currently work.

### License

This package inherits the licensing of its parent framework, Laravel, and as such is open-sourced 
software licensed under the [MIT license](http://opensource.org/licenses/MIT)
