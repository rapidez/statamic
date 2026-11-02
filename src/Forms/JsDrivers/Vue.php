<?php

namespace Rapidez\Statamic\Forms\JsDrivers;

use Statamic\Forms\JsDrivers\AbstractJsDriver;
use Statamic\Statamic;

class Vue extends AbstractJsDriver
{
    /**
     * Add `show_field` javascript to renderable field data, with reference to `formData` object in the vue data
     */
    public function addToRenderableFieldData($field, $data)
    {
        $conditions = Statamic::modify($field->conditions())->toJson()->entities();

        return [
            'show_field' => 'window.Statamic.$conditions.showField('.$conditions.', formData, \''.$field->handle().'\')',
        ];
    }

    /**
     * Add `v-model` to the Statamic fallback field views, file inputs can't have a `v-model` in Vue
     * and nested group fields can't bind as their parent isn't an object in the `formData`
     */
    public function addToRenderableFieldAttributes($field)
    {
        if (
            ! $field->fieldtype()->hasJsDriverDataBinding()
            || in_array($field->type(), ['assets', 'files'])
            || str_contains($field->handle(), '.')
        ) {
            return [];
        }

        return [
            'v-model' => 'formData.'.$field->handle(),
        ];
    }
}
