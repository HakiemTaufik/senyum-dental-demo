<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Service;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** Tells search engines what they may crawl. The test copy and demo block everything. */
    public function robots(): Response
    {
        $lines = config('seo.indexable')
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /book/thank-you', 'Disallow: /livewire', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** A list of every public page, so Google finds new pages and services quickly. */
    public function sitemap(): Response
    {
        $urls = collect();

        Page::published()->orderBy('menu_order')->get(['slug', 'updated_at'])
            ->each(fn (Page $page) => $urls->push(['loc' => $page->url(), 'lastmod' => $page->updated_at, 'priority' => $page->isHome() ? '1.0' : '0.8']));

        $urls->push(['loc' => route('book'), 'lastmod' => null, 'priority' => '0.9']);

        Service::published()->ordered()->get(['slug', 'updated_at'])
            ->each(fn (Service $service) => $urls->push(['loc' => route('services.show', $service), 'lastmod' => $service->updated_at, 'priority' => '0.7']));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>'.e($url['loc']).'</loc>'
                .($url['lastmod'] ? '<lastmod>'.$url['lastmod']->toAtomString().'</lastmod>' : '')
                .'<priority>'.$url['priority'].'</priority></url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
