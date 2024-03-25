@php
    $brands = \Statamic\Facades\Entry::query()
        ->where('collection', 'brands')
        ->orderBy('title')
        ->get();

    $grouped = $brands->groupBy(function(\Statamic\Eloquent\Entries\Entry $item,$key) {
        return $item->title[0];
    });

@endphp
<div class="lg:container">
    @if($include_brand_list)
        <div>
            <span>@lang('Brands')</span>
            <ul class="flex flex-col">
                @foreach($brands as $brand)
                    <li>
                        <a href="{{ $brand->url() }}">{{ $brand->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="flex flex-col">
        @if($page->title)
            <h1>{{ $page->title }}</h1>
        @endif
        @if($include_table_of_contents)
            <ul class="flex">
                @foreach($grouped->keys() as $letter)
                    <li><a href="#{{ $letter }}">{{ $letter }}</a></li>
                @endforeach
            </ul>
        @endif

        <div class="flex flex-col">
            @foreach($grouped as $letter => $group)
                <div class="flex w-full">
                    <span id="{{ $letter }}" class="text-xl">
                        {{ $letter }}
                    </span>
                    <ul class="flex row-wrap">
                        @foreach($group as $brand)
                            <li>{{ $brand->title }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div>
