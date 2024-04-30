@php
    $brands = \Statamic\Facades\Entry::query()
        ->where('collection', 'brands')
        ->where('site', \Statamic\Facades\Site::current()->handle())
        ->orderBy('title')
        ->get();

    $grouped = $brands->groupBy(function(\Statamic\Eloquent\Entries\Entry $item, int $key) {
        return $item->title[0];
    });
@endphp

<div class="container mb-16">
    <h1 class="text-3xl font-bold mb-4">@lang('All brands')</h1>
    <div class="bg-inactive-100 p-4 rounded-xl">
        @foreach(array_merge(['0-9'], range('A','Z')) as $letter)
            <a class="px-1 text-inactive hover:text-neutral transition" href="#{{ $letter }}" v-smooth-scroll="{}">{{ $letter }}</a>
        @endforeach
    </div>
    
    <div class="mt-6">
        @foreach(array_merge(['0-9'], range('A','Z')) as $letter)
            @php
                if ($letter === '0-9') {
                    $brandsByLetter = $brands->filter(function ($brand) {
                        return !ctype_alpha(substr($brand->title, 0, 1));
                    });
                } else {
                    $brandsByLetter = $brands->filter(function ($brand) use ($letter) {
                        return strtoupper(substr($brand->title, 0, 1)) === $letter;
                    });
                }
            @endphp
            @if ($brandsByLetter->count() > 0)
                <div id="{{ $letter }}" class="mt-6">
                    <div class="mb-1 text-xl font-bold">{{ $letter }}</div>
                    <ul class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-6 gap-3 text-neutral text-sm">
                        @foreach($brandsByLetter as $brand)
                            <li class="col-span-1">
                                <a
                                    href="{{ $brand->url() }}"
                                    class="flex flex-1 items-center justify-center text-sm w-full border rounded-xl p-4 hover:border-primary h-32 md:text-base lg:h-40"
                                >
                                    @if($brand->image)
                                        <div class="flex w-full h-full *:flex *:flex-1">
                                            @responsive($brand->image, ['class' => 'object-contain w-full h-full max-h-44'])
                                        </div>
                                    @else
                                        {{ $brand->title }}
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
</div>
