<?php namespace Ollieread\Multiauth\Console;

use Illuminate\Console\Command;

class ClearPasswordResetsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'multiauth:clear-resets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush expired reminders.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->laravel['auth.password.tokens']->deleteExpired();

        $this->info('Expired reminders cleared!');
    }

}