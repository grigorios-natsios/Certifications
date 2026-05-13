<?php

namespace App\Support;

class Branding
{
    /**
     * Resolve the logo URL.
     * Priority: APP_LOGO env (URL or relative path) → auto-detected
     * public/images/logo.{png,svg,jpg} → null.
     */
    public static function logoUrl(): ?string
    {
        return self::resolve(
            (string) config('branding.logo', ''),
            ['images/logo.png', 'images/logo.svg', 'images/logo.jpg']
        );
    }

    /**
     * Absolute filesystem path to the logo for inline email embedding (CID).
     * Returns null if no logo file is present locally.
     */
    public static function logoPath(): ?string
    {
        return self::resolvePath(
            (string) config('branding.logo', ''),
            ['images/logo.png', 'images/logo.svg', 'images/logo.jpg']
        );
    }

    private static function resolvePath(string $configured, array $fallbacks): ?string
    {
        $configured = trim($configured);

        if ($configured !== '' && ! preg_match('#^https?://#i', $configured)) {
            $abs = public_path(ltrim($configured, '/'));
            if (is_file($abs)) {
                return $abs;
            }
        }

        foreach ($fallbacks as $candidate) {
            $abs = public_path($candidate);
            if (is_file($abs)) {
                return $abs;
            }
        }

        return null;
    }

    /**
     * Resolve the favicon URL.
     * Priority: APP_FAVICON env → public/favicon.ico → null.
     */
    public static function faviconUrl(): ?string
    {
        return self::resolve(
            (string) config('branding.favicon', ''),
            ['favicon.ico', 'images/favicon.png', 'images/favicon.ico']
        );
    }

    private static function resolve(string $configured, array $fallbacks): ?string
    {
        $configured = trim($configured);

        if ($configured !== '') {
            // Absolute URL → use as-is
            if (preg_match('#^https?://#i', $configured)) {
                return $configured;
            }
            // Relative path → check public/, return asset URL if it exists
            $rel = ltrim($configured, '/');
            if (is_file(public_path($rel))) {
                return asset($rel);
            }
        }

        foreach ($fallbacks as $candidate) {
            if (is_file(public_path($candidate))) {
                return asset($candidate);
            }
        }

        return null;
    }
}
