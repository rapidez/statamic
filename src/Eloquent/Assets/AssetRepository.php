<?php

namespace Rapidez\Statamic\Eloquent\Assets;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Support\Str;
use Statamic\Eloquent\Assets\AssetRepository as StatamicAssetRepository;

class AssetRepository extends StatamicAssetRepository
{
    public function findById($id): ?AssetContract
    {
        [$container, $path] = explode('::', $id);

        $filename = Str::afterLast($path, '/');
        $folder = Str::contains($path, '/') ? Str::beforeLast($path, '/') : '/';

        $cacheKey = "eloquent-asset-{$id}";

        return Cache::rememberForever($cacheKey, function () use ($container, $filename, $folder) {
            return $this->query()
                ->where('container', $container)
                ->where('folder', $folder)
                ->where('basename', $filename)
                ->first();
        });
    }
}