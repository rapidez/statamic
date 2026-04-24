<?php

namespace Rapidez\Statamic\Actions;

use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
use Statamic\Entries\Entry;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Fieldtypes\Replicator;

class ConvertField
{
    public function __construct(
        private readonly CleanHtml $cleanHtml, 
        private readonly Pipeline $pipeline
    ) {}

    /**
     * Transform HTML to statamic compatible content
     */
    public function execute(Entry $entry, string $html, ?string $fieldName = 'page_builder'): Entry
    {
        $fieldType = $entry->blueprint()->field($fieldName)?->fieldType();
        if ($fieldType === null) {
            throw new InvalidArgumentException(__('Entry with blueprint ":blueprint" does not contain field ":fieldname"', ['blueprint' => $entry->blueprint()->handle, 'fieldname' => $fieldName]));
        }

        if (!$fieldType instanceof Replicator || (!$fieldType instanceof Bard && !$fieldType->flattenedSetsConfig()->hasAny('content'))) {
            throw new InvalidArgumentException(__('":fieldname" on Blueprint ":blueprint" is not a Bard field or is not a replicator containing a Bard field', ['blueprint' => $entry->blueprint()->handle, 'fieldname' => $fieldName]));
        }

        $isReplicator = false;
        if (!($fieldType instanceof Bard) && $fieldType instanceof Replicator) {
            $isReplicator = true;
        }

        $html = $this->cleanHtml->execute($html);
        if (trim($html) === '') {
            return $entry;
        }

        $augmentor = new Augmentor($fieldType instanceof Bard ? $fieldType : resolve(Bard::class));
        $data = $augmentor->renderHtmlToProsemirror($html);
        $content = $this->pipeline
            ->send($data['content'])
            ->via('handle')
            ->through(config('rapidez.statamic.migration.pipelines.convert_field', []))
            ->thenReturn();

        $currentContent = $isReplicator ? data_get($entry->get($fieldName), '0.content.content') : $entry->get($fieldName);
        if (
            $currentContent
            // We must check by rendering to html since the array structure changes, but the html output does not.
            && $augmentor->renderProsemirrorToHtml(['content' => $currentContent]) == $augmentor->renderProsemirrorToHtml(['content' => $content])
        ) {
            return $entry;
        }

        if ($isReplicator) {
            // TODO: Currently we only support a fieldset named "content", containing a field called "content" which is a Bard field.
            // Ideally we should determine the needed path by nesting fields in the Replicator until we find a Bard field.
            $content = [[
                'type' => 'content',
                'content' => ['content' => $content]
            ]];
        }

        $entry->set($fieldName, $content);

        return $entry;
    }
}
