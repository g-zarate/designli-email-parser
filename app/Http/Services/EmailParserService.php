<?php

namespace App\Http\Services;

use PhpMimeMailParser\Parser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Utils\JsonUtils;
use App\Utils\UrlUtils;


class EmailParserService
{
    public function parseEmail(string $filePath, bool $useStorage): ?array
    {
        $parser = new Parser();
        if ($useStorage) {
            $parser->setPath(storage_path('app/' . $filePath));
        } else {
            $parser->setPath($filePath);
        }

        $contentFromAttachments = $this->getJsonFromAttachments($parser);
        if ($contentFromAttachments ) {
            $attachmentJson = $this->processValidateJsonData($this->getJsonFromAttachments($parser));
        }

        $textBody = $this->processValidateJsonData($parser->getMessageBody('text'));

        $urls = UrlUtils::extractUrls($parser->getMessageBody('text'));
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

    public function fetchJsonFromUrls(array $urls): ?string
    {
        $jsonData = [];
        foreach ($urls as $url) {
            $response = Http::get($url);
            if ($response->successful()) {
                $body = $response->body();
                if (JsonUtils::isJson($body)) {
                    $jsonData[] = $body;
                } else {
                    $jsonLinks = $this->scrapeJsonLinksFromPage($body, $url);
                    foreach ($jsonLinks as $link) {
                        $response = Http::get($link);
                        if ($response->successful()) {
                            $body = $response->body();
                            if (JsonUtils::isJson($body)) {
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

        $crawler->filter('a')->each(function (Crawler $node) use (&$jsonLinks, $baseUrl) {
            $href = $node->attr('href');
            $absoluteUrl = UrlUtils::toAbsoluteUrl($href, $baseUrl);
            if (UrlUtils::isValidUrl($absoluteUrl)) {
                $jsonLinks[] = $absoluteUrl;
            }
        });
        return $jsonLinks;
    }
}
