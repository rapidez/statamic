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

<div class="lg:container">
    <div class="flex flex-col">
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
                            <a href="{{ $brand->url() }}">
                                @if(!$brand->image->isEmpty())
                                    @responsive($brand->image->first())
                                @else
                                    <li>{{ $brand->title }}</li>
                                @endif
                            </a>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div>
