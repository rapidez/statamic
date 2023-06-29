<x-rapidez::breadcrumbs>
    @foreach(Statamic::tag('nav:breadcrumbs')->param('include_home', false) as $item)
        <x-rapidez::breadcrumb :active="$item->is_current" :url="to($item->url)" :position="$loop->iteration + 1">
            {{ $item->title }}
        </x-rapidez::breadcrumb>
    @endforeach
</x-rapidez::breadcrumbs>
