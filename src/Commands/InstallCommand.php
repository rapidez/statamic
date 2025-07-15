<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'rapidez-statamic:install';

    protected $description = 'Install Rapidez Statamic';

    public function handle(): void
    {
        $this->info('Running Statamic Starter Kit...');

        $this->call('statamic:starter-kit:install', [
            'package' => 'justbetter/statamic-starter-kit',
        ]);

        $this->info('Done 🚀');
    }
}
