<?php

if (!function_exists('getItemUrl')) {
    function getItemUrl($item, $baseUrl)
    {
        $types = ['linked_product', 'linked_category', 'linked_brand'];

        foreach ($types as $type) {
            if (isset($item[$type]) && ($item[$type]?->value()['url_path'] ?? false)) {
                return $baseUrl . '/' . $item[$type]->value()['url_path'];
            }
        }

        return $item['url'] ?? '';
    }
}
