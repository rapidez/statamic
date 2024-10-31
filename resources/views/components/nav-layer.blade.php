{{--
    This is a mobile navigation slideover component for handling multi-level navgiations It makes use of recursion to handle multiple levels of navgation dynamically.

    Properties:
        id:             Unique identifier for the current navigation layer.
        title:          Title displayed in the header. Defaults to "Menu".
        hasParent:      Indicates if the current menu is a child of another menu. Defaults to false.
        tag:            Base HTML tag for the slideover component. Set this to 'form' for the main navigation or 'div' for nested menus.
        parentUrl:      URL used for navigating to the parent item when in a nested menu. Displayed with a "Go to" link.

    Slots:
        headerbutton:   This slot allows the customization of the button placed at the left in the header for navigation.

    The component uses recursion to handle submenus. When a menu item has children, it creates another instance of this slideover component for the nested navigation layer.
--}}
@props(['id', 'children', 'title' => __('Menu'), 'hasParent' => false, 'tag' => 'form', 'parentUrl' => ''])
@slots(['headerbutton'])

<x-rapidez::slideover.mobile
    :title="(string) $title"
    :$id
    :$hasParent
    :$tag
>
    <x-slot:headerbutton>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 cursor-pointer text-white">
            @include('rapidez-statamic::navigation.header-button')
        </div>
    </x-slot:headerbutton>
    <div class="bg-inactive-100 flex w-full flex-1 flex-col">
        <ul class="mt-5 flex flex-col divide-y border-y bg-white">
            @if ($hasParent && $parentUrl)
                <li>
                    <a href="{{ $parentUrl }}" class="normal flex items-center justify-between p-5 font-semibold">
                        @lang('Go to :item', ['item' => strtolower($title)])
                    </a>
                </li>
            @endif
            @foreach ($children ?: [] as $child)
                <li class="relative">
                    @if ($child['title'] ?? '')
                        <a href="{{ $child['url'] }}" class="flex items-center justify-between p-5 font-semibold">
                            {{ $child['title'] }}
                            @if ($child['children'])
                                <x-heroicon-o-chevron-right class="size-4" />
                            @endif
                        </a>
                    @endif
                    @if ($child['children'])
                        @php($childId = uniqid(Str::snake("{$child['title']}" ?? '')))
                        <label class="absolute inset-0 cursor-pointer" for="{{ $childId }}"></label>
                        <x-rapidez-statamic::nav-layer
                            :id="$childId"
                            :children="$child['children'] ?? []"
                            :title="$child['title'] ?? ''"
                            :parent-url="$child['url']"
                            has-parent
                            tag="div"
                        ></x-rapidez-statamic::nav-layer>
                    @endif
                </li>
            @endforeach
        </ul>
        {{ $slot }}
    </div>
</x-rapidez::slideover.mobile>
