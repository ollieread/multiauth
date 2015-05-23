<?php namespace Ollieread\Multiauth\Passwords;

use Illuminate\Support\ServiceProvider;
use Ollieread\Multiauth\Console\PasswordResetsTableCommand;
use Ollieread\Multiauth\Console\ClearPasswordResetsCommand;
use Ollieread\Multiauth\Passwords\DatabaseTokenRepository as DbRepository;

class PasswordResetServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPasswordBroker();

        $this->registerTokenRepository();

        $this->registerCommands();
    }

    /**
     * Register the password broker instance.
     *
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            // The reminder repository is responsible for storing the user e-mail addresses
            // and password reset tokens. It will be used to verify the tokens are valid
            // for the given e-mail addresses. We will resolve an implementation here.
            $tokens = $app['auth.password.tokens'];

            $providers = $views = array();

            foreach ($app['config']['auth.multi'] as $type => $config) {
                $providers[$type] = $app['auth']->$type()->driver()->getProvider();
                $views[$type] = isset($config['email']) ? $config['email'] : $app['config']['auth.password']['email'];
            }


            // The password broker uses the reminder repository to validate tokens and send
            // reminder e-mails, as well as validating that password reset process as an
            // aggregate service of sorts providing a convenient interface for resets.
            return new PasswordBrokerManager(

                $tokens, $app['mailer'], $views, $providers

            );
        });
    }

    /**
     * Register the reminder repository implementation.
     *
     * @return void
     */
    protected function registerTokenRepository()
    {
        $this->app->singleton('auth.password.tokens', function ($app) {
            $connection = $app['db']->connection();

            // The database reminder repository is an implementation of the reminder repo
            // interface, and is responsible for the actual storing of auth tokens and
            // their e-mail addresses. We will inject this table and hash key to it.
            $table = $app['config']['auth.password.table'];

            $key = $app['config']['app.key'];

            $expire = $app['config']->get('auth.password.expire', 60);

            return new DbRepository($connection, $table, $key, $expire);
        });
    }

    /**
     * Register the multiauth related console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->bindShared('command.multiauth.resets', function ($app) {
            return new PasswordResetsTableCommand($app['files']);
        });

        $this->app->bindShared('command.multiauth.resets.clear', function ($app) {
            return new ClearPasswordResetsCommand;
        });

        $this->commands(
            'command.multiauth.resets', 'command.multiauth.resets.clear'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth.password', 'auth.password.tokens'];
    }

}
