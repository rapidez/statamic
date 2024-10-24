<?php

namespace Rapidez\Statamic\Actions;

class GenerateSitemapAction
{
    public function createSitemap(string $sitemapUrls) : string
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        $sitemap .= $sitemapUrls;
        $sitemap .= '</urlset>';

        return $sitemap;
    }

    public function addUrl($url = null, $lastMod = null) : string
    {
        $sitemap = '<url>';

        if ($url) {
            $sitemap .= '<loc>' . url($url) . '</loc>';
        }

        if ($lastMod) {
            $sitemap .= '<lastmod>' . substr($lastMod, 0, 10) . '</lastmod>';
        }

        $sitemap .= '</url>';

        return $sitemap;
    }
}
