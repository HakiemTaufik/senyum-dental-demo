<?php

namespace App\Support;

use App\Settings\ClinicSettings;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Facades\Storage;

/** Small helpers used by the public website templates. */
class Html
{
    /**
     * Placeholders the owner can type in any text block. They are replaced with the
     * current value from Settings → Clinic details, so e.g. the privacy policy always
     * shows the right contact details.
     */
    public const TOKENS = [
        '[clinic-name]' => 'Clinic name',
        '[clinic-address]' => 'Clinic address',
        '[clinic-phone]' => 'Clinic phone',
        '[clinic-email]' => 'Clinic email',
        '[dpo-name]' => 'Data Protection Officer name',
        '[dpo-email]' => 'Data Protection Officer email',
        '[dpo-phone]' => 'Data Protection Officer phone',
        '[retention-months]' => 'Months booking requests are kept',
    ];

    /** Turns rich text from the admin panel into safe HTML (removes scripts and other dangerous code). */
    public static function rich(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return self::replaceTokens(RichContentRenderer::make($html)->toHtml());
    }

    /** Replaces [placeholders] with escaped values from clinic settings. */
    public static function replaceTokens(string $html): string
    {
        if (! str_contains($html, '[')) {
            return $html;
        }

        $clinic = app(ClinicSettings::class);
        $values = [
            '[clinic-name]' => $clinic->name,
            '[clinic-address]' => str_replace("\n", ', ', (string) $clinic->address),
            '[clinic-phone]' => $clinic->phone,
            '[clinic-email]' => $clinic->email,
            '[dpo-name]' => $clinic->dpo_name,
            '[dpo-email]' => $clinic->dpo_email,
            '[dpo-phone]' => $clinic->dpo_phone ?: $clinic->phone,
            '[retention-months]' => (string) $clinic->booking_retention_months,
        ];

        return strtr($html, array_map(fn (?string $value): string => e((string) $value), $values));
    }

    /**
     * Only allows safe link types in buttons (blocks "javascript:" tricks). A link starting
     * with "//" or "/\" is not a page on this site: browsers treat it as another website.
     */
    public static function safeUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return preg_match('/^(\/(?![\/\\\\])|#|https:\/\/|tel:|mailto:)/i', $url) ? $url : null;
    }

    public static function image(?string $path): ?string
    {
        return blank($path) ? null : Storage::disk('public')->url($path);
    }
}
