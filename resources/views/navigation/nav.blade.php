@props(['nav' => 'nav:main', 'mobileNav' => 'nav:main'])
@php
    // This combines multiple navigations into one for desktop and mobile seperately, only neccessary if you have multiple navigations
    $navData = array_merge(...Arr::map(Arr::wrap($nav), fn($item) => Statamic::tag($item)->fetch()));
    $mobileNavData = array_merge(...Arr::map(Arr::wrap($mobileNav), fn($item) => Statamic::tag($item)->fetch()));
@endphp
<nav class="lg:hidden">
    <x-rapidez-statamic::nav-layer
        id="navigation"
        is-form
        :children="$mobileNavData"
    >
    </x-rapidez-statamic::nav-layer>
</nav>
@php
    $baseUrl = \Statamic\Facades\Site::current()->absoluteUrl();
@endphp
<nav class="relative border-y max-lg:hidden">
    <ul class="text-neutral [&>:not(:hover)]:hover:text-inactive flex items-center justify-center gap-8 text-sm font-semibold">
        @foreach ($navData as $item)
            @php
                $itemUrl = getItemUrl($item, $baseUrl);
            @endphp
            <li class="group">
                <a class="relative flex py-4 transition" href="{{ $itemUrl }}">
                    {{ $item['title'] }}
                    @if ($item['children'])
                        <div class="bg-primary absolute inset-x-0 bottom-0 z-50 h-0.5 w-full origin-right translate-y-1/2 scale-x-0 transition group-hover:origin-left group-hover:scale-x-100"></div>
                    @endif
                </a>
                @if ($item['children'])
                    <div class="pointer-events-none absolute inset-x-0 top-full -translate-y-1 border-t bg-white opacity-0 transition group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100">
                        <div class="bg-neutral/50 pointer-events-none absolute inset-x-0 top-full h-screen"></div>
                        <div class="container relative flex overflow-hidden">
                            <ul class="w-full columns-3 flex-col gap-x-12 py-10 font-bold xl:columns-4">
                                @foreach ($item['children'] as $item)
                                    @php
                                        $itemUrl = getItemUrl($item, $baseUrl);
                                    @endphp
                                    <li class="flex break-inside-avoid flex-col gap-1 pb-5">
                                        <a href="{{ $itemUrl }}">{{ $item['title'] }}</a>
                                        <ul class="[&>:not(:hover)]:hover:text-inactive flex flex-col font-medium">
                                            @foreach ($item['children'] as $item)
                                                @php
                                                    $itemUrl = getItemUrl($item, $baseUrl);
                                                @endphp
                                                <li class="transition">
                                                    <a href="{{ $itemUrl }}">{{ $item['title'] }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
