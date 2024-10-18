<?php

namespace Rapidez\Statamic\Commands;

use Statamic\Facades\Site;
use Illuminate\Console\Command;
use Rapidez\Core\Facades\Rapidez;

class CreateSites extends Command
{
    protected $signature = 'rapidez:statamic:create:sites';

    protected $description = 'Create sites based on the active Magento stores.';

    public function handle(): int
    {
        $sites = [];
        $stores = Rapidez::getStores();
        $configModel = config('rapidez.models.config');

        foreach ($stores as $store) {
            Rapidez::setStore($store['store_id']);

            $locale = $configModel::getCachedByPath('general/locale/code');
            $lang = explode('_', $locale)[0] ?? '';
            $url = $configModel::getCachedByPath('web/secure/base_url');

            $sites[$store['code']] = [
                'name' => $store['name'] ?? $store['code'],
                'locale' => $locale,
                'lang' => $lang,
                'url' => $url,
                'attributes' => [
                    'magento_store_id' => $store['store_id'],
                    'group' => $store['website_code'] ?? '',
                    'disabled' => '{{ config:rapidez.statamic.sites.' . $store['code'] . '.attributes.disabled }}',
                ]
            ];
        }

        Site::setSites($sites);
        Site::save();

        return Command::SUCCESS;
    }
}
