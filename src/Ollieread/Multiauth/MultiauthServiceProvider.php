<?php namespace Ollieread\Multiauth;

use Illuminate\Support\ServiceProvider;
use Ollieread\Multiauth\Console\RemindersTableCommand;

class MultiauthServiceProvider extends ServiceProvider
{

    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('auth', function($app)
        {
            // Once the authentication service has actually been requested by the developer
            // we will set a variable in the application indicating such. This helps us
            // know that we need to set any queued cookies in the after event later.
            $app['auth.loaded'] = true;

            return new MultiManager($app);
        });
    }

    public function provides()
    {
        return array('auth');
    }

}
