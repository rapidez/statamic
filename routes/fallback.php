<?php
use Statamic\Facades\Data;

if ($entry = Statamic\Facades\Entry::findByUri(request()->path())->toResponse(request())) {
    if (View::exists($entry->get('template'))) {
        echo view($entry->get('template'), $entry);
    } else if (View::exists(str($entry->structure()->handle())->singular()->toString())) {
        echo view(str($entry->structure()->handle())->singular()->toString(), $entry);
    }
}
