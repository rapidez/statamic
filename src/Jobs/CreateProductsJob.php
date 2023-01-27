<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use Rapidez\Statamic\Actions\Products\CreateProducts;
use Rapidez\Statamic\Actions\Products\ImportProducts;

class CreateProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public Enumerable $products,
        public ?string $siteHandle = null,
    ){
    }

    public function handle(CreateProducts $createProducts): void
    {
        if (!$this->products || !$this->siteHandle) {
            return;
        }

        $createProducts->create($this->products, $this->siteHandle);
    }
}
