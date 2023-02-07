<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Rapidez\Statamic\Actions\Products\ImportProducts;

class ImportProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public ?Carbon $updatedAt = null,
        public ?string $store = null,
        public ?bool $addHidden = false,
    ){
    }

    public function handle(ImportProducts $importProducts): void
    {
        $importProducts->import($this->updatedAt, $this->store, $this->addHidden);
    }
}
