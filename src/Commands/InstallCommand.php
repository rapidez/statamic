<?php

namespace Rapidez\Statamic\Commands;

use Facades\Statamic\Console\Processes\Composer as ComposerFacade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Process as ProcessFacade;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;
use Statamic\Facades\Site as SiteFacade;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
    protected $signature = 'rapidez-statamic:install
        {--addons= : Comma separated add-ons to install (seo-pro,cloudflare-purge,structured-data,detour,veto,sentry,mail-log) or "none"}
        {--multisite : Convert to a multisite installation (requires Statamic Pro)}
        {--no-user : Skip creating a control panel user}
        {--force : Overwrite files that were already published}';

    protected $description = 'Install and configure Statamic for Rapidez';

    /**
     * Repository names as used by statamic:install:eloquent-driver,
     * mapped to their key in config/statamic/eloquent-driver.php.
     */
    protected const ELOQUENT_REPOSITORIES = [
        'assets' => 'assets',
        'blueprints' => 'blueprints',
        'collection_trees' => 'collection_trees',
        'entries' => 'entries',
        'forms' => 'forms',
        'form_submissions' => 'form_submissions',
        'global_variables' => 'global_set_variables',
        'nav_trees' => 'navigation_trees',
        'revisions' => 'revisions',
        'terms' => 'terms',
        'tokens' => 'tokens',
    ];

    protected const ADDONS = [
        'seo-pro' => [
            'package' => 'statamic/seo-pro',
            'label' => 'SEO Pro',
            'next' => 'Configure SEO Pro in the control panel under Tools > SEO Pro.',
        ],
        'cloudflare-purge' => [
            'package' => 'justbetter/statamic-cloudflare-purge',
            'label' => 'Cloudflare Purge',
            'next' => 'Add your Cloudflare zone and API token to the .env file.',
        ],
        'structured-data' => [
            'package' => 'justbetter/statamic-structured-data',
            'label' => 'Structured Data',
            'next' => null,
        ],
        'detour' => [
            'package' => 'justbetter/statamic-detour',
            'label' => 'Detour (redirects)',
            'next' => null,
        ],
        'veto' => [
            'package' => 'justbetter/statamic-veto',
            'label' => 'Veto',
            'next' => null,
        ],
        'sentry' => [
            'package' => 'sentry/sentry-laravel',
            'label' => 'Sentry',
            'next' => 'Set SENTRY_LARAVEL_DSN in the .env file.',
        ],
        'mail-log' => [
            'package' => 'bobwez98/mail-log-statamic',
            'label' => 'Mail log',
            'next' => null,
        ],
    ];

    /** @var array<int, array{0: string, 1: string}> */
    protected array $summary = [];

    public function handle(): int
    {
        if (! $this->databaseIsReachable()) {
            return self::FAILURE;
        }

        $this->setupStatamic();
        $this->setupFilesystem();
        $this->publishContent();
        $this->setupMultisite();
        $this->setupUsers();
        $this->setupEloquentDriver();
        $addons = $this->installAddons();

        if (! $this->migrate()) {
            return self::FAILURE;
        }

        $this->createUser();
        $this->printSummary($addons);

        return self::SUCCESS;
    }

    protected function databaseIsReachable(): bool
    {
        try {
            DBFacade::connection()->getPdo();
        } catch (Throwable $e) {
            $this->components->error('Could not connect to the database: '.$e->getMessage());

            return false;
        }

        return true;
    }

    protected function setupStatamic(): void
    {
        $this->components->info('Setting up Statamic');

        $composerFile = base_path('composer.json');
        $script = '@php artisan statamic:install --ansi';

        if (FileFacade::exists($composerFile)) {
            /** @var array<string, mixed> $composer */
            $composer = json_decode(FileFacade::get($composerFile), true);
            $scripts = $composer['scripts']['post-autoload-dump'] ?? [];

            if (! in_array($script, $scripts, true)) {
                $composer['scripts']['post-autoload-dump'] = [...$scripts, $script];
                FileFacade::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
                $this->report('Composer post-autoload-dump script', 'added');
            } else {
                $this->report('Composer post-autoload-dump script', 'skipped');
            }
        }

        $this->call('statamic:install');
        $this->report('statamic:install', 'done');
    }

    protected function setupFilesystem(): void
    {
        if (! FileFacade::isDirectory(public_path('assets'))) {
            FileFacade::makeDirectory(public_path('assets'), 0755, true);
        }

        if (config('filesystems.disks.assets')) {
            $this->report('Assets disk', 'skipped');

            return;
        }

        $disk = <<<'PHP'
    'disks' => [

        'assets' => [
            'driver' => 'local',
            'root' => public_path('assets'),
            'url' => '/assets',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

PHP;

        $replaced = $this->replaceInFile(config_path('filesystems.php'), "    'disks' => [\n", $disk);

        $this->report('Assets disk', $replaced ? 'added' : 'failed, add an "assets" disk to config/filesystems.php manually');
    }

    protected function publishContent(): void
    {
        $this->components->info('Publishing content');

        $this->publish('rapidez-statamic-content', $this->option('force'));
        $this->publish('rapidez-statamic-pages', $this->option('force'));

        $this->report('Fieldsets, blueprints and pages collection', 'published');
    }

    protected function setupMultisite(): void
    {
        if (SiteFacade::multiEnabled() && SiteFacade::all()->count() > 1) {
            $this->report('Multisite', 'skipped, already multisite');

            return;
        }

        $multisite = $this->option('multisite') || ($this->input->isInteractive() && confirm(
            label: 'Would you like to configure a multisite?',
            default: false,
            hint: 'Requires a Statamic Pro license.',
        ));

        if (! $multisite) {
            $this->report('Multisite', 'skipped');

            return;
        }

        $this->call('statamic:multisite');
        $this->report('Multisite', 'configured');
    }

    protected function setupUsers(): void
    {
        $this->components->info('Setting up users');

        $this->setupUserModel();

        if (! SchemaFacade::hasTable('users') && ! FileFacade::glob(database_path('migrations/*_create_users_table.php'))) {
            $this->publish('rapidez-statamic-users-migration');
            $this->report('Users migration', 'published');
        }

        $this->setupAuthConfig();
        $this->setupStatamicUsersConfig();

        $hasAuthMigration = (bool) FileFacade::glob(database_path('migrations/*_statamic_auth_tables.php'));
        $usersTableIsUpdated = SchemaFacade::hasTable('users') && SchemaFacade::hasColumn('users', 'super');

        if (! $hasAuthMigration && ! $usersTableIsUpdated) {
            $this->call('statamic:auth:migration');
            $this->report('Statamic auth migration', 'created');
        } else {
            $this->report('Statamic auth migration', 'skipped');
        }

        $this->publish('rapidez-statamic-roles', $this->option('force'));
    }

    protected function setupUserModel(): void
    {
        $userModel = app_path('Models/User.php');

        if (! FileFacade::exists($userModel)) {
            $this->publish('rapidez-user-model');
            $this->report('User model', 'published');

            return;
        }

        if (str_contains(FileFacade::get($userModel), 'Rapidez\Statamic\Models\BaseUser')) {
            $this->report('User model', 'skipped');

            return;
        }

        $replaced = $this->replaceInFile(
            $userModel,
            'use Illuminate\Foundation\Auth\User',
            'use Rapidez\Statamic\Models\BaseUser',
        );

        $this->report('User model', $replaced ? 'now extends BaseUser' : 'failed, extend Rapidez\Statamic\Models\BaseUser manually');
    }

    protected function setupAuthConfig(): void
    {
        $authConfig = config_path('auth.php');

        if (! FileFacade::exists($authConfig)) {
            $this->call('config:publish', ['name' => 'auth']);
        }

        if (str_contains(FileFacade::get($authConfig), "'activations' => [")) {
            $this->report('Activations password broker', 'skipped');

            return;
        }

        $replaced = $this->replaceInFile($authConfig, "    'passwords' => [\n", <<<'PHP'
    'passwords' => [
        'activations' => [
            'provider' => 'users',
            'table' => env('AUTH_ACTIVATION_TOKEN_TABLE', 'password_activation_tokens'),
            'expire' => 4320,
            'throttle' => 60,
        ],

PHP);

        $this->report('Activations password broker', $replaced ? 'added' : 'failed, add an "activations" broker to config/auth.php manually');
    }

    protected function setupStatamicUsersConfig(): void
    {
        $usersConfig = config_path('statamic/users.php');

        if (! FileFacade::exists($usersConfig)) {
            FileFacade::ensureDirectoryExists(dirname($usersConfig));
            FileFacade::copy(base_path('vendor/statamic/cms/config/users.php'), $usersConfig);
        }

        $contents = FileFacade::get($usersConfig);
        $updated = preg_replace(
            [
                "/'repository' => '[^']*'/",
                "/'resets' => [^,\n]+,/",
                "/'activations' => [^,\n]+,/",
            ],
            [
                "'repository' => 'eloquent'",
                "'resets' => 'users',",
                "'activations' => 'activations',",
            ],
            $contents,
            1,
        );

        if ($updated === $contents) {
            $this->report('Statamic users config', 'skipped');

            return;
        }

        FileFacade::put($usersConfig, $updated);
        $this->report('Statamic users config', 'updated');
    }

    protected function setupEloquentDriver(): void
    {
        $this->components->info('Setting up the Eloquent driver');

        $eloquentConfig = config_path('statamic/eloquent-driver.php');

        if (! FileFacade::exists($eloquentConfig)) {
            $this->publish('statamic-eloquent-config');
        }

        $this->replaceInFile(
            $eloquentConfig,
            "'table_prefix' => env('STATAMIC_ELOQUENT_PREFIX', ''),",
            "'table_prefix' => env('STATAMIC_ELOQUENT_PREFIX', 'statamic_'),",
        );

        $remaining = collect(self::ELOQUENT_REPOSITORIES)
            ->reject(fn (string $configKey) => config("statamic.eloquent-driver.{$configKey}.driver") === 'eloquent')
            ->keys();

        if ($remaining->isEmpty()) {
            $this->report('Eloquent driver', 'skipped');

            return;
        }

        $this->call('statamic:install:eloquent-driver', [
            '--repositories' => $remaining->implode(','),
            '--import' => true,
            '--without-messages' => true,
        ]);

        $this->report('Eloquent driver', 'configured for '.$remaining->implode(', '));
    }

    /**
     * @return array<int, string>
     */
    protected function installAddons(): array
    {
        $addons = $this->selectedAddons();

        $packages = collect($addons)
            ->map(fn (string $addon) => self::ADDONS[$addon]['package'])
            ->reject(fn (string $package) => ComposerFacade::isInstalled($package))
            ->values()
            ->all();

        if (! $packages) {
            $this->report('Add-ons', $addons ? 'skipped, already installed' : 'none selected');

            return $addons;
        }

        $this->components->info('Installing add-ons: '.implode(', ', $packages));

        try {
            ComposerFacade::withoutQueue()->throwOnFailure()->requireMultiple($packages);
        } catch (Throwable $e) {
            $this->components->error('Installing add-ons failed: '.$e->getMessage());
            $this->report('Add-ons', 'failed, run "composer require '.implode(' ', $packages).'" manually');

            return [];
        }

        $this->report('Add-ons', 'installed '.implode(', ', $packages));

        return $addons;
    }

    /**
     * @return array<int, string>
     */
    protected function selectedAddons(): array
    {
        $option = $this->option('addons');

        if (is_string($option)) {
            if ($option === 'none') {
                return [];
            }

            $selected = array_map('trim', explode(',', $option));
            $invalid = array_diff($selected, array_keys(self::ADDONS));

            if ($invalid) {
                $this->components->warn('Ignoring unknown add-ons: '.implode(', ', $invalid));
            }

            return array_values(array_intersect($selected, array_keys(self::ADDONS)));
        }

        if (! $this->input->isInteractive()) {
            return [];
        }

        /** @var array<int, string> $selected */
        $selected = multiselect(
            label: 'Which add-ons would you like to install?',
            options: collect(self::ADDONS)->map(fn (array $addon) => $addon['label'])->all(),
            hint: 'All optional, you can always install them later.',
        );

        return $selected;
    }

    protected function migrate(): bool
    {
        $this->components->info('Running migrations');

        $result = ProcessFacade::forever()
            ->path(base_path())
            ->run(
                [PHP_BINARY, 'artisan', 'migrate', '--no-interaction'],
                fn (string $type, string $output) => $this->output->write($output),
            );

        if ($result->failed()) {
            $this->components->error('Migrations failed, fix the error above and run this command again.');

            return false;
        }

        $this->report('Migrations', 'done');

        return true;
    }

    protected function createUser(): void
    {
        if ($this->option('no-user') || ! $this->input->isInteractive()) {
            $this->report('Control panel user', 'skipped');

            return;
        }

        $hasSuperUser = SchemaFacade::hasColumn('users', 'super')
            && DBFacade::table('users')->where('super', '=', true)->exists();

        if (! confirm('Would you like to create a control panel user?', default: ! $hasSuperUser)) {
            $this->report('Control panel user', 'skipped');

            return;
        }

        passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(base_path('artisan')).' statamic:make:user', $exitCode);

        $this->report('Control panel user', $exitCode === 0 ? 'created' : 'failed, run "php artisan statamic:make:user"');
    }

    /**
     * @param  array<int, string>  $addons
     */
    protected function printSummary(array $addons): void
    {
        $this->newLine();
        $this->components->info('Rapidez Statamic installed 🚀');

        foreach ($this->summary as [$step, $status]) {
            $this->components->twoColumnDetail($step, $status);
        }

        $nextSteps = collect($addons)
            ->map(fn (string $addon) => self::ADDONS[$addon]['next'])
            ->filter()
            ->push('Frontend files are not touched; your Rapidez frontend build is unchanged.');

        $this->newLine();
        $this->components->bulletList($nextSteps->all());
    }

    protected function publish(string $tag, bool $force = false): void
    {
        $this->callSilently('vendor:publish', array_filter([
            '--tag' => $tag,
            '--force' => $force,
        ]));
    }

    protected function replaceInFile(string $path, string $search, string $replace): bool
    {
        if (! FileFacade::exists($path)) {
            return false;
        }

        $contents = FileFacade::get($path);

        if (! str_contains($contents, $search)) {
            return false;
        }

        FileFacade::put($path, Str::replaceFirst($search, $replace, $contents));

        return true;
    }

    protected function report(string $step, string $status): void
    {
        $this->summary[] = [$step, $status];
    }
}
