<?php

namespace Rapidez\Statamic\Http\Controllers;

use Statamic\Facades\CP\Toast;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Rapidez\Statamic\Jobs\ImportBrandsJob;
use Statamic\Http\Controllers\Controller;

class ImportsController extends Controller
{
    public function __invoke() : View
    {
        return view('rapidez-statamic::utilities.import_utility.imports');
    }

    public function importBrands() : RedirectResponse
    {
        ImportBrandsJob::dispatch();

        Toast::success(__('The import of brands has started!'))->duration(5000);

        return redirect(cp_route('utilities.imports'));
    }
}
