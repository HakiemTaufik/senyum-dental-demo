<?php

namespace App\Support;

use App\Models\Faq;
use App\Settings\ClinicSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

/**
 * schema.org data that helps Google understand the clinic (name, address, phone, hours)
 * and show richer search results. Output as JSON-LD inside the page <head>.
 */
class StructuredData
{
    public static function dentist(): array
    {
        $clinic = app(ClinicSettings::class);
        $addressLines = array_values(array_filter(array_map('trim', explode("\n", (string) $clinic->address))));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Dentist',
            '@id' => url('/').'#clinic',
            'name' => $clinic->name,
            'description' => $clinic->tagline,
            'url' => url('/'),
            'image' => asset(config('seo.default_image')),
            'logo' => asset('images/apple-touch-icon.png'),
            'telephone' => $clinic->phone,
            'email' => $clinic->email,
            'address' => $addressLines ? [
                '@type' => 'PostalAddress',
                'streetAddress' => implode(', ', array_slice($addressLines, 0, -1)) ?: $addressLines[0],
                'addressLocality' => self::locality(end($addressLines)),
                'postalCode' => preg_match('/\b(\d{5})\b/', (string) $clinic->address, $m) ? $m[1] : null,
                'addressCountry' => 'MY',
            ] : null,
            'hasMap' => $clinic->maps_url,
            'sameAs' => array_values(array_filter([$clinic->facebook_url, $clinic->instagram_url])),
            // Opening hours are free text in Settings, so they're given as a description Google can read.
            'openingHours' => collect($clinic->opening_hours)->map(fn (array $row): string => $row['days'].': '.$row['hours'])->implode('; ') ?: null,
            'currenciesAccepted' => 'MYR',
        ]);
    }

    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $i): array => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    /** @param  Collection<int, Faq>  $faqs */
    public static function faqPage(Collection $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn (Faq $faq): array => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags(Html::rich($faq->answer)))],
            ])->values()->all(),
        ];
    }

    /** A safe <script type="application/ld+json"> tag (JSON is escaped so it can't break out of the tag). */
    public static function script(array $data): HtmlString
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        return new HtmlString('<script type="application/ld+json" nonce="'.e(Vite::cspNonce()).'">'.$json.'</script>');
    }

    private static function locality(string $lastLine): string
    {
        // "40000 Shah Alam, Selangor" → "Shah Alam"
        return trim(explode(',', preg_replace('/^\d{5}\s*/', '', $lastLine))[0]);
    }
}
