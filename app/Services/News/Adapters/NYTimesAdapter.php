<?php

namespace App\Services\News\Adapters;

use App\Services\News\Contracts\NewsSourceInterface;
use GuzzleHttp\Client;
use Carbon\Carbon;

class NYTimesAdapter implements NewsSourceInterface
{
    protected Client $http;
    protected string $apiKey;
    protected string $endpoint = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 25]);
        $this->apiKey = config('services.nytimes.key');
    }

    public function fetch(array $params = []): array
    {
        $now = Carbon::now()->format('Ymd');
        $fiveMinutesAgo = Carbon::now()->subMinutes(5)->format('Ymd');

        $query = array_merge([
            'api-key' => $this->apiKey,
            'begin_date' => $fiveMinutesAgo,
            'end-date' => $now,
        ], $params);
        $resp = $this->http->get($this->endpoint, ['query' => $query]);
        $body = json_decode((string)$resp->getBody(), true);
        return $body['response']['docs'] ?? [];
    }

    public function normalize(array $raw): array
    {
        $byline = $raw['byline']['original'] ?? null;
        $multimedia = is_array($raw['multimedia'] ?? null)
            ? collect($raw['multimedia'])
            : collect();

        $firstMedia = $multimedia->first();

        $image = is_array($firstMedia) && isset($firstMedia['url'])
            ? 'https://www.nytimes.com/' . ltrim($firstMedia['url'], '/')
            : null;
        return [
            'external_id' => $raw['web_url'],
            'title' => $raw['headline']['main'] ?? null,
            'description' => $raw['abstract'] ?? null,
            'content' => $raw['lead_paragraph'] ?? null,
            'url' => $raw['web_url'] ?? null,
            'image_url' => $image,
            'published_at' => isset($raw['pub_date']) ? Carbon::parse($raw['pub_date']) : null,
            'author' => $byline,
            'category' => $raw['section_name'] ?? null,
            'language' => null,
            'raw' => $raw,
        ];
    }
}
