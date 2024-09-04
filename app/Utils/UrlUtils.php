<?php

namespace App\Utils;

class UrlUtils
{
    public static function extractUrls(string $content): array
    {
        $pattern = '/https?:\/\/[^\s"]+/i';
        preg_match_all($pattern, $content, $matches);
        return $matches[0] ?? [];
    }

    public static function isValidUrl(string $href): bool
    {
        return filter_var($href, FILTER_VALIDATE_URL) !== false;
    }

    public static function toAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        $parsedBaseUrl = parse_url($baseUrl);
        if (!isset($parsedBaseUrl['scheme']) || !isset($parsedBaseUrl['host'])) {
            throw new InvalidArgumentException('Base URL must be valid.');
        }
        return rtrim($parsedBaseUrl['scheme'] . '://' . $parsedBaseUrl['host'], '/') . '/' . ltrim($url, '/');
    }
}