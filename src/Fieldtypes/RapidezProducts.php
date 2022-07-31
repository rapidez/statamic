<?php

namespace Rapidez\Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Illuminate\Support\Collection;

class RapidezProducts extends Fieldtype
{
    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return new Collection();
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }
}
