<div class="prose max-w-none">
    @foreach($content as $set)
        @if($set['type'] === 'text')
            {!! $set['text'] !!}
        @else
            <div class="not-prose">
                @include('rapidez-statamic::page_builder', ['content' => [$set]])
            </div>
        @endif
    @endforeach
</div>
