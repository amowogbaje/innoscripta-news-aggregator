<?php

namespace App\Services\News\Adapters;

use App\Services\News\Contracts\NewsSourceInterface;
use GuzzleHttp\Client;
use Carbon\Carbon;

class GuardianAdapter implements NewsSourceInterface
{
    protected Client $http;
    protected string $apiKey;
    protected string $endpoint = 'https://content.guardianapis.com/search';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
        $this->apiKey = config('services.guardian.key');
    }

    public function fetch(array $params = []): array
    {
        $now = Carbon::now()->toDateString();
        $fiveMinutesAgo = Carbon::now()->subMinutes(5)->toDateString();

        $query = array_merge([
            'api-key' => $this->apiKey,
            'show-fields' => 'headline,body,thumbnail,byline',
            'page-size' => 50,
            'from-date' => $fiveMinutesAgo,
            'to-date' => $now,
        ], $params);

        $resp = $this->http->get($this->endpoint, ['query' => $query]);
        $body = json_decode((string)$resp->getBody(), true);
        return $body['response']['results'] ?? [];
    }

    public function normalize(array $raw): array
    {
        $fields = $raw['fields'] ?? [];
        return [
            'external_id' => $raw['webUrl'],
            'title' => $fields['headline'] ?? $raw['webTitle'] ?? null,
            'description' => $fields['trailText'] ?? null,
            'content' => $fields['body'] ?? null,
            'url' => $raw['webUrl'] ?? null,
            'image_url' => $fields['thumbnail'] ?? null,
            'published_at' => isset($raw['webPublicationDate']) ? Carbon::parse($raw['webPublicationDate']) : null,
            'author' => $fields['byline'] ?? null,
            'category' => $raw['sectionName'] ?? null,
            'language' => $raw['lang'] ?? null,
            'raw' => $raw,
        ];
    }
}

