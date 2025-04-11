<?php

namespace Rapidez\Statamic\Commands;

use Exception;
use Statamic\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InstallCommand extends Command
{
    protected $signature = 'rapidez-statamic:install';

    protected $description = 'Install Rapidez Statamic';

    public function handle(): void
    {
        $this->setupStatamic();
        $this->setupUser();
        $this->setupEloquentDriver();

        $this->info('Running migrations...');
        $this->call('migrate');

        if ($this->confirm('Would you like to configure a multisite? Note: Configuring a multisite requires a Statamic PRO license.')) {
            $this->call('statamic:multisite');
        }

        if ($this->confirm('Would you like to publish Collections, Blueprints, Fieldsets & Views?', 1)) {
            $this->call('vendor:publish', [
                '--provider' => 'Rapidez\Statamic\RapidezStatamicServiceProvider',
                '--tag' => 'rapidez-statamic-content',
            ]);

            $this->call('vendor:publish', [
                '--provider' => 'Rapidez\Statamic\RapidezStatamicServiceProvider',
                '--tag' => 'views',
            ]);
        }

        if ($this->confirm('Would you like to make a user?', 1)) {
            $this->call('statamic:make:user');
        }

        $this->info('Running `yarn run prod`...');
        passthru('yarn run prod');

        $this->info('Done 🚀');
    }

    protected function setupStatamic(): void
    {
        $this->info('Starting Statamic Setup...');
        $this->call('statamic:install');
        
        $composerFile = base_path('composer.json');
        
        if (file_exists($composerFile)) {
            $composerFileContents = File::get($composerFile);
            $composer = json_decode($composerFileContents, true);

            if (!isset($composer['scripts'])) {
                $composer['scripts'] = [];
            }

            if (!isset($composer['scripts']['post-autoload-dump'])) {
                $composer['scripts']['post-autoload-dump'] = [];
            }

            if (!in_array("@php artisan statamic:install --ansi", $composer['scripts']['post-autoload-dump'])) {
                $composer['scripts']['post-autoload-dump'][] = "@php artisan statamic:install --ansi";
            }

            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    protected function setupUser(): void
    {
        $this->info('Starting User Setup...');
        $authConfig = config_path('auth.php');
        $userModel = app_path('Models/User.php');

        if(class_exists('\App\Models\User') && file_exists($userModel)) {
            $userModelFile = Str::of(File::get($userModel));
            $userModelFile = $userModelFile->replace(
                'use Illuminate\Foundation\Auth\User',
                'use Rapidez\Statamic\Models\BaseUser'
            );

            File::put(
                $userModel,
                $userModelFile->__toString()
            );
        } else if (!class_exists('\App\Models\User') && file_exists($authConfig)) {
            $this->call('vendor:publish', [
                '--provider' => 'Rapidez\Statamic\RapidezStatamicServiceProvider',
                '--tag' => 'rapidez-user-model',
            ]);
        }

        $migrationPath = database_path('migrations/0001_01_01_000000_create_users_table.php');

        if (!file_exists($migrationPath)) {
            try {
                $userTable = file_get_contents('https://raw.githubusercontent.com/laravel/laravel/refs/heads/11.x/database/migrations/0001_01_01_000000_create_users_table.php');

                if (!$userTable) {
                    throw new Exception('Failed to download the migration file.');
                }

                file_put_contents($migrationPath, $userTable);
            } catch (Exception $e) {
                Log::error('Error downloading migration file: ' . $e->getMessage());
            }
        }

        if(!config('auth.passwords.activations') && file_exists($authConfig)) {
            $authFile = Str::of(File::get($authConfig));
            $authFile = $authFile->replace(
                "'passwords' => [\n",
                "'passwords' => [
        'activations' => [
            'provider' => 'users',
            'table' => 'password_activation_tokens',
            'expire' => 4320,
            'throttle' => 60,
        ],\n"
            );

            File::put(
                $authConfig,
                $authFile->__toString()
            );
        }

        $usersConfig = config_path('statamic/users.php');

        if ((config('statamic.users.passwords.resets') !== 'users' || config('statamic.users.passwords.activations') !== 'activations') && file_exists($usersConfig)) {
            $usersFile = Str::of(File::get($usersConfig));
            
            if(config('statamic.users.passwords.resets') !== 'users') {
                $usersFile = $usersFile->replace(
                    "'resets' => config('auth.defaults.passwords'),",
                    "'resets' => 'users',"
                );
            }

            if(config('statamic.users.passwords.activations') !== 'activations') {
                $usersFile = $usersFile->replace(
                    "'activations' => config('auth.defaults.passwords'),",
                    "'activations' => 'activations',"
                );
            }
            
            File::put(
                $usersConfig,
                $usersFile->__toString()
            );
        }

        $files = glob(database_path('migrations/*_statamic_auth_tables.php'));

        if (!$files) {
            $this->call('statamic:auth:migration');
        }
    }

    protected function setupEloquentDriver(): void
    {
        $this->info('Starting Eloquent Driver Setup...');

        $this->call('vendor:publish', [
            '--tag' => 'runway-config',
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Statamic\Eloquent\ServiceProvider',
            '--tag' => 'statamic-eloquent-config',
        ]);

        $eloquentConfig = config_path('statamic/eloquent-driver.php');

        if (file_exists($eloquentConfig)) {
            File::put(
                $eloquentConfig,
                Str::of(File::get($eloquentConfig))
                    ->replace(
                        "'table_prefix' => env('STATAMIC_ELOQUENT_PREFIX', ''),",
                        "'table_prefix' => env('STATAMIC_ELOQUENT_PREFIX', 'statamic_'),"
                    )
                    ->__toString()
            );
        }

        if (!$this->isEloquentDriverConfigured()) {
            $this->call('statamic:install:eloquent-driver', [
                '--repositories' => 'assets,collection_trees,entries,forms,form_submissions,global_variables,nav_trees,terms,tokens'
            ]);
        }
    }

    protected function isEloquentDriverConfigured(): bool
    {
        $eloquentRepositories = collect([
            'assets',
            'collection_trees',
            'entries',
            'forms',
            'form_submissions',
            'global_set_variables',
            'navigation_trees',
            'terms',
            'tokens',
        ]);

        $remainingRepositories = $eloquentRepositories->filter(fn($repository) => config("statamic.eloquent-driver.{$repository}.driver") !== 'eloquent');

        return $remainingRepositories->count() === 0;
    }
}
