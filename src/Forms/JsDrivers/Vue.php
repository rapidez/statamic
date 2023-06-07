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
            'show_field' => 'Statamic.$conditions.showField('.$conditions.', formData)',
        ];
    }
}
