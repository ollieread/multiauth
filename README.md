# Laravel Multi Auth #

- **Laravel**: 5
- **Author**: Ramon Ackermann
- **Author Homepage**: https://github.com/sboo
- **Author**: Ollie Read
- **Author Homepage**: http://ollieread.com

For Laravel 4.2 version, see https://github.com/ollieread/multiauth

This package is not a replacement for laravels default Auth library, but instead something
that sits between your code and the library.

Think of it as a factory class for Auth. Now, instead of having a single table/model to
authenticate users against, you can now have multiple, and unlike the previous version of
this package, you have access to all functions, and can even use a different driver 
for each user type.

On top of that, you can use multiple authentication types, simultaneously, so you can be logged
in as a user, a master account and an admin, without conflicts!

## Custom Auth Drivers ##

At this current moment in time, custom Auth drivers written for the base Auth class will not work. I'm currently looking into this particular issue but for the meantime, you can work around this by changing your closure to return an instance of `Ollieread\Multiauth\Guard` instead of the default.

## Installation ##

Firstly you want to include this package in your composer.json file.
```javascript
    "require": {
    		"sboo/multiauth" : "4.0.*"
    }
```
   
Now you'll want to update or install via composer.

    composer update

Next you open up app/config/app.php and replace the 'Illuminate\Auth\AuthServiceProvider', with
```php
    'Ollieread\Multiauth\MultiauthServiceProvider',
```

and 'Illuminate\Auth\Passwords\PasswordResetServiceProvider' with
```php
	'Ollieread\Multiauth\Passwords\PasswordResetServiceProvider',
```	

**NOTE** It is very important that you replace the default service providers.

Remove the original database migration for password_resets.

##Configuration##

is pretty easy too, take config/auth.php with its default values:
```php
    return [

		'driver' => 'eloquent',

		'model' => 'App\User',

		'table' => 'users',

		'password' => [
        		'email' => 'emails.password',
        		'table' => 'password_resets',
        		'expire' => 60,
        	],

	];
```

Now remove the first three options (driver, model and table) and replace as follows:
```php
    return [

		'multi'	=> [
			'admin' => [
				'driver' => 'eloquent',
				'model'	=> 'App\Admin',
			],
			'client' => [
				'driver' => 'database',
				'table' => 'clients',
				'email' => 'client.emails.password',
			]
		],

		'password' => [
        		'email' => 'emails.password',
        		'table' => 'password_resets',
        		'expire' => 60,
        	],

	];
```
	
This is an example configuration. Note that you will have to create Models and migrations for each type of user. 
Use App\User.php and 2014_10_12_000000_create_users_table.php as an example. 

If you wish to use a reminders email view per usertype, simply add an email option to the type, as shown in the above example.


To generate the reminders table you will need to run the following command.

	php artisan multiauth:resets-table

Likewise, if you want to clear all reminders, you have to run the following command.

	php artisan multiauth:clear-resets


You will also need to change the existing default Laravel 5 files to accommodate multiple auth and password types. 
Do as described in this gist:

https://gist.github.com/sboo/10943f39429b001dd9d0

## Usage ##

Everything is done the exact same way as the original library, the one exception being
that all method calls are prefixed with the key (account or user in the above examples)
as a method itself.
```php
    Auth::admin()->attempt(array(
    	'email'		=> $attributes['email'],
    	'password'	=> $attributes['password'],
    ));
    Auth::client()->attempt(array(
    	'email'		=> $attributes['email'],
    	'password'	=> $attributes['password'],
    ));
    Auth::admin()->check();
    Auth::client()->check();
```

I found that have to call the user() method on a user type called user() looked messy, so
I have added in a nice get method to wrap around it.
```php
	Auth::admin()->get();
```

In the instance where you have a user type that can impersonate another user type, example being
an admin impersonating a user to recreate or check something, I added in an impersonate() method
which simply wraps loginUsingId() on the request user type.
```php
	Auth::admin()->impersonate('client', 1, true);
```

The first argument is the user type, the second is the id of said user, and the third is
whether or not to remember the user, which will default to false, so can be left out
more often than not.

And so on and so forth.


There we go, done! Enjoy yourselves.


## Testing ##

Laravel integration/controller testing implements `$this->be($user)` to the base TestCase class. The implementation of #be() does not work correctly with Multiauth. To get around this, implement your own version of #be() as follows:
```php
    public function authenticateAs($type, $user) {
      $this->app['auth']->$type()->setUser($user);
    }
```

### License

This package inherits the licensing of its parent framework, Laravel, and as such is open-sourced 
software licensed under the [MIT license](http://opensource.org/licenses/MIT)
