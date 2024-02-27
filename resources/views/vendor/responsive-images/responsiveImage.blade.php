<picture>
    @foreach (($breakpoints ?? []) as $breakpoint)
        @foreach($breakpoint->sources() ?? [] as $source)
            @php
                $srcSet = $source->getSrcset();
            @endphp

            @if($srcSet !== null)
                <source
                    @if($type = $source->getMimeType()) type="{{ $type }}" @endif
                    @if($media = $source->getMediaString()) media="{{ $media }}" @endif
                    srcset="{{ $srcSet }}"
                    @if($includePlaceholder ?? false) sizes="1px" @endif
                >
            @endif
        @endforeach
    @endforeach

    <img
        {!! $attributeString ?? '' !!}
        src="{{ $src }}"
        @unless (\Illuminate\Support\Str::contains($attributeString, 'alt'))
        alt="{{ $asset['alt'] ?? $asset['title'] }}"
        @endunless
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        onload="this.onload=null;window.responsiveResizeObserver.observe(this)"
        @if($hasSources)
        data-statamic-responsive-images
        @endif
        @if(isset($asset['has_focus']) && $asset['has_focus'] && isset($asset['focus_css']) && $asset['focus_css'])
            style="object-position: {{ $asset['focus_css'] }}"
        @endif
    >
</picture>
