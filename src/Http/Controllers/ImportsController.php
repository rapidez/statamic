<?php

namespace Rapidez\Statamic\Http\Controllers;

use Statamic\Facades\CP\Toast;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Statamic\Http\Controllers\Controller;

class ImportsController extends Controller
{
    public function __invoke() : View
    {
        return view('rapidez-statamic::utilities.import_utility.imports');
    }

    public function importCategories() : RedirectResponse
    {
        Artisan::queue('rapidez:statamic:import:categories --all')
            ->onQueue('imports');

        Toast::success(__('The import of categories has started!'))->duration(5000);

        return redirect(cp_route('utilities.imports'));
    }

    public function importProducts() : RedirectResponse
    {
        Artisan::queue('rapidez:statamic:import:products')
            ->onQueue('imports');

        Toast::success(__('The import of products has started!'))->duration(5000);

        return redirect(cp_route('utilities.imports'));
    }

    public function importBrands() : RedirectResponse
    {
        Artisan::queue('rapidez:statamic:import:brands')
            ->onQueue('imports');

        Toast::success(__('The import of brands has started!'))->duration(5000);

        return redirect(cp_route('utilities.imports'));
    }
}
