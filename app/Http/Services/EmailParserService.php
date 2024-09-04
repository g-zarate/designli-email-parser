<?php

namespace App\Http\Services;

use PhpMimeMailParser\Parser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class EmailParserService
{
    public function parseEmail(string $filePath): ?array
    {
        $parser = new Parser();
        $parser->setPath(storage_path('app/' . $filePath));

        $contentFromAttachments = $this->getJsonFromAttachments($parser);
        if ($contentFromAttachments ) {
            $attachmentJson = $this->processValidateJsonData($this->getJsonFromAttachments($parser));
        }
        
        $textBody = $this->processValidateJsonData($parser->getMessageBody('text'));

        $urls = $this->extractUrls($parser->getMessageBody('text'));
        if (!empty($urls)) {
            $jsonFromUrls = $this->fetchJsonFromUrls($urls);
            if ($jsonFromUrls) {
                $jsonContentFromUrls = $this->processValidateJsonData($jsonFromUrls);
            }
            
        }

        return [
            'attachment' => $attachmentJson ?? "No valid json attachment found",
            'text_body' => $textBody ?? "No valid json found in body",
            'urls' => $jsonContentFromUrls ?? "no json found"
            
        ];
        
    }

    private function getJsonFromAttachments(Parser $parser): ?string
    {
        $attachments = $parser->getAttachments();

        foreach ($attachments as $attachment) {
            
            if ($attachment->getContentType() === 'application/json') {
                return $attachment->getContent();
            }
        }

        return null;
    }

    private function extractUrls(string $content): array
    {
        $pattern = '/https?:\/\/[^\s"]+/i';
        preg_match_all($pattern, $content, $matches);
        return $matches[0] ?? [];
    }

    public function fetchJsonFromUrls(array $urls): ?string
    {
        $jsonData = [];
        
        foreach ($urls as $url) {
            $response = Http::get($url);
            if ($response->successful()) {
                $body = $response->body();
                if ($this->isJson($body)) {
                    $jsonData[] = $body;
                } else {
                    $jsonLinks = $this->scrapeJsonLinksFromPage($body, $url);
                    foreach ($jsonLinks as $link) {
                        $response = Http::get($link);
                        if ($response->successful()) {
                            $body = $response->body();
                            if ($this->isJson($body)) {
                                $jsonData[] = $body;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($jsonData)) {
            return '[' . implode(',', $jsonData) . ']';
        }
        return null;
    }

    private function processValidateJsonData(string $jsonString): ?array
    {
        $jsonString = str_replace(["\r", "\n"], '', $jsonString);
        $jsonString = stripslashes($jsonString); // Remove backslashes
        $jsonArray = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $jsonArray;
    }

    private function scrapeJsonLinksFromPage(string $html, string $baseUrl): array
    {
        $crawler = new Crawler($html);
        $jsonLinks = [];

        // Extract all links from the page
        $crawler->filter('a')->each(function (Crawler $node) use (&$jsonLinks, $baseUrl) {
            $href = $node->attr('href');
            $absoluteUrl = $this->toAbsoluteUrl($href, $baseUrl);
            if ($this->isValidUrl($absoluteUrl)) {
                $jsonLinks[] = $absoluteUrl;
            }
        });

        return $jsonLinks;
    }

    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function isJsonLink(string $url): bool
    {
        return str_ends_with(parse_url($url, PHP_URL_PATH), '.json');
    }

    private function isValidUrl(string $href): bool
    {
        return filter_var($href, FILTER_VALIDATE_URL) !== false;
    }

    private function toAbsoluteUrl(string $url, string $baseUrl): string
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