<?php

namespace Rapidez\Statamic\Extend;

use Illuminate\Support\Facades\Cache;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Sites\Sites;

class SitesLinkedToMagentoStores extends Sites
{
    public function findByUrl($url)
    {
        if ($site = $this->findByMageRunCode(request()->server('MAGE_RUN_CODE'))) {
            return $site;
        }

        return parent::findByUrl($url);
    }


    public function findByMageRunCode($code)
    {
        return collect($this->sites)->get($code);
    }

    protected function getSavedSites()
    {
        return Cache::rememberForever('statamic_sites', function () {
            $sites = [];
            $stores = Rapidez::getStores();
            $configModel = config('rapidez.models.config');

            foreach ($stores as $store) {
                if (config('rapidez.statamic.sites.' . $store['code'] . '.attributes.disabled')) {
                    continue;
                }

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
                        'group' => $store['website_code'] ?? ''
                    ]
                ];
            }

            return $sites ?: $this->getFallbackConfig();
        });
    }
}
