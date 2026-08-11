<?php

namespace Rapidez\Statamic\Extend\Link;

use Illuminate\Database\Eloquent\Model;

/**
 * Adapts a Magento Eloquent model for Statamic's Link fieldtype.
 *
 * ArrayableLink / ResolveRedirect call url() as a method, while Magento
 * exposes the storefront path via an Eloquent Attribute (property access).
 */
class MagentoModelLink
{
    public function __construct(private Model $model) {}

    public function model(): Model
    {
        return $this->model;
    }

    public function url(): ?string
    {
        $url = $this->model->url;

        return $url !== null && $url !== '' ? (string) $url : null;
    }

    public function absoluteUrl(): ?string
    {
        $url = $this->url();

        return $url ? url($url) : null;
    }

    public function toAugmentedArray(?array $keys = null): array
    {
        if (method_exists($this->model, 'toAugmentedArray')) {
            return $this->model->toAugmentedArray($keys);
        }

        return ['url' => $this->url()];
    }

    public function toShallowAugmentedArray(): array
    {
        if (method_exists($this->model, 'toShallowAugmentedArray')) {
            return $this->model->toShallowAugmentedArray();
        }

        return ['url' => $this->url()];
    }
}
