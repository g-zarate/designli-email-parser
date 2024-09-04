<?php

namespace App\Utils;

class JsonUtils
{
    public static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function isJsonLink(string $url): bool
    {
        return str_ends_with(parse_url($url, PHP_URL_PATH), '.json');
    }
}