<?php

namespace App\Services\News\Adapters;

use App\Services\News\Contracts\NewsSourceInterface;
use GuzzleHttp\Client;
use Carbon\Carbon;

class NewsApiAdapter implements NewsSourceInterface
{
    protected Client $http;
    protected string $apiKey;
    protected string $endpoint = 'https://newsapi.org/v2/everything';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
        $this->apiKey = config('services.newsapi.key'); // put in services.php and .env
    }

    public function fetch(array $params = []): array
    {
        $now = Carbon::now()->toIso8601String();
        $fiveMinutesAgo = Carbon::now()->subMinutes(5)->toIso8601String();
        $query = array_merge([
            'apiKey'   => $this->apiKey, 
            'language' => 'en',
            'domains'  => "techcrunch.com,thenextweb.com",
            'from'     => $fiveMinutesAgo, 
            'to'       => $now,   
            'pageSize' => 100,
        ], $params);
        $resp = $this->http->get($this->endpoint, ['query' => $query]);
        $body = json_decode((string)$resp->getBody(), true);
        return $body['articles'] ?? [];
    }

    public function normalize(array $raw): array
    {
        return [
            'external_id' => $raw['url'],
            'title' => $raw['title'] ?? null,
            'description' => $raw['description'] ?? null,
            'content' => $raw['content'] ?? null,
            'url' => $raw['url'] ?? null,
            'image_url' => $raw['urlToImage'] ?? null,
            'published_at' => isset($raw['publishedAt']) ? Carbon::parse($raw['publishedAt']) : null,
            'author' => $raw['author'] ?? null,
            'category' => $raw['source']['name'] ?? null,
            'language' => $raw['language'] ?? null, // newsapi may not return
            'raw' => $raw,
            'publisher' => [
                'external_id' => $raw['source']['id'] ?? null,
                'name' => $raw['source']['name'] ?? null,
            ],
        ];
    }
}
