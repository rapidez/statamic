<div class="prose max-w-none">
    @foreach($content as $set)
        
            <div class="not-prose">
                @include('rapidez-statamic::page_builder', ['content' => [$set]])
            </div>
        
    @endforeach
</div>
