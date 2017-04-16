<?php namespace Ollieread\Multiauth;

use Illuminate\Support\ServiceProvider;
use Ollieread\Multiauth\Console\RemindersTableCommand;

class MultiauthServiceProvider extends ServiceProvider
{

    protected $defer = false;

    public function register()
    {
        $this->app->bindShared('multiauth', function ($app) {
            $app['multiauth.loaded'] = true;

            return new MultiManager($app);
        });
    }

    public function provides()
    {
        return array('multiauth');
    }

}
