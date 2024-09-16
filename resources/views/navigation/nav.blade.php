<nav class="lg:hidden">
    <x-rapidez-statamic::nav-layer
        id="navigation"
        is-form
        :children="Statamic::tag('nav:main')->fetch()"
    />
</nav>
@php
    $baseUrl = \Statamic\Facades\Site::current()->absoluteUrl();
@endphp
<nav class="relative border-y max-lg:hidden">
    <ul class="flex items-center justify-center gap-7 font-semibold text-neutral [&>:not(:hover)]:hover:text-inactive">
        @foreach (Statamic::tag('nav:main')->fetch() as $item)
            @php
                $level1Url = isset($item['linked_category']) && ($item['linked_category']?->value()['url_path'] ?? false) ? $baseUrl . '/' . $item['linked_category']->value()['url_path'] : $item['url'] ?? '';
            @endphp
            <li class="group">
                <a class="relative flex py-4 transition" href="{{ $level1Url }}">
                    {{ $item['title'] }}
                    @if ($item['children'])
                        <div class="absolute inset-x-0 bottom-0 z-50 h-0.5 w-full origin-right translate-y-1/2 scale-x-0 bg-primary transition group-hover:origin-left group-hover:scale-x-100"></div>
                    @endif
                </a>
                @if ($item['children'])
                    <div class="pointer-events-none absolute inset-x-0 top-full -translate-y-1 border-t bg-white opacity-0 transition group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100">
                        <div class="pointer-events-none absolute inset-x-0 top-full h-screen bg-neutral/50"></div>
                        <div class="container relative flex overflow-hidden">
                            <ul class="columns-3 flex-col gap-x-12 py-10 text-base font-bold xl:columns-4 w-full">
                                @foreach ($item['children'] as $level2)
                                    @php
                                        $level2Url = isset($level2['linked_category']) && ($level2['linked_category']?->value()['url_path'] ?? false) ? $baseUrl . '/' . $level2['linked_category']->value()['url_path'] : $level2['url'] ?? '';
                                    @endphp
                                    <li class="flex break-inside-avoid flex-col gap-1 pb-5">
                                        <a href="{{ $level2Url }}">{{ $level2['title'] }}</a>
                                        <ul class="flex flex-col font-medium [&>:not(:hover)]:hover:text-inactive">
                                            @foreach ($level2['children'] as $level3)
                                                @php
                                                    $level3Url = isset($level3['linked_category']) && ($level3['linked_category']?->value()['url_path'] ?? false) ? $baseUrl . '/' . $level3['linked_category']->value()['url_path'] : $level3['url'] ?? '';
                                                @endphp
                                                <li class="transition">
                                                    <a href="{{ $level3Url }}">{{ $level3['title'] }}</a>
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
