<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'rapidez-statamic:install';

    protected $description = 'Install Rapidez Statamic';

    public function handle(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'runway-config',
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Statamic\Eloquent\ServiceProvider',
            '--tag' => 'statamic-eloquent-config',
        ]);

        $this->confirm('Did you set the table_prefix in config/statamic/eloquent-driver.php to "statamic_"?', true);

        $this->call('statamic:install:eloquent-driver');

        $this->info('If you would like to store users in the database please make sure to follow this guide: https://statamic.dev/tips/storing-users-in-a-database#from-a-fresh-statamic-project');

        if ($this->confirm('Would you like to configure a multisite? Note: Configuring a multisite requires a Statamic PRO license.')) {
            $this->call('statamic:multisite');
        }

        if ($this->confirm('Would you like to publish Collections, Blueprints, Fieldsets & Views?', true)) {
            $this->call('vendor:publish', [
                '--provider' => 'Rapidez\Statamic\RapidezStatamicServiceProvider',
                '--tag' => 'rapidez-statamic-content',
            ]);

            $this->call('vendor:publish', [
                '--provider' => 'Rapidez\Statamic\RapidezStatamicServiceProvider',
                '--tag' => 'views',
            ]);
        }

        if ($this->confirm('Would you like to make a user?', true)) {
            $this->call('statamic:make:user');
        }

        $this->info('Done 🚀');
    }
}
