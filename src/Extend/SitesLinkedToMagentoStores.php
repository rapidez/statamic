<?php

namespace Rapidez\Statamic\Extend;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Sites\Sites;

class SitesLinkedToMagentoStores extends Sites
{
    public function findByUrl($url)
    {
        if ($site = once(fn() => $this->findByMageRunCode(request()->server('MAGE_RUN_CODE')))) {
            return $site;
        }

        return once(fn() => parent::findByUrl($url));
    }


    public function findByMageRunCode($code)
    {
        if (!$code || !($this->sites instanceof Collection)) {
            return null;
        }
        return $this->sites->get($code);
    }

    protected function getSavedSites()
    {
        return Cache::rememberForever('statamic_sites', function () {
            $sites = [];
            $stores = Rapidez::getStores();
            $configModel = config('rapidez.models.config');

            foreach ($stores as $store) {
                if (in_array($store['code'], config('rapidez.statamic.disabled_sites'))) {
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
