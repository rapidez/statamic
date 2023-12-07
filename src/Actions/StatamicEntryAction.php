<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use ReflectionClass;

class StatamicEntryAction
{
    public static function createEntry(array $attributes, array $values = []): void
    {
        if (Entry::query()->where($attributes)->count()) {
            // Entry was already created.
            return;
        }

        /** @var \Statamic\Entries\Entry $entry */
        $entry = Entry::make();
        $values = array_merge($attributes, $values);
        
        static::setEntryData($entry, $values)->save();
    }

    public static function setEntryData(\Statamic\Entries\Entry $entry, array $values = []) : \Statamic\Entries\Entry
    {
        $reflectedEntry = new ReflectionClass($entry);
        foreach ($values as $key => $value) {
            // Check if the key is a statamic setter
            if (!$reflectedEntry->hasMethod($key) || $reflectedEntry->getMethod($key)->getNumberOfParameters() < 1) {
                continue;
            }

            $entry->$key($value);
            unset($values[$key]);
        }

        $entry->merge($values);

        return $entry;
    }
}
