<?php

namespace App\Services\News\Contracts;

interface NewsSourceInterface
{
    /**
     * Fetch latest raw articles from the source.
     * Return array of raw items (provider payload).
     *
     * $params can include category, q, from, to, page, pageSize
     */
    public function fetch(array $params = []): array;

    /**
     * Normalize a raw provider item to our canonical shape:
     * [
     *   'external_id' => string|null,
     *   'title' => string,
     *   'description' => string|null,
     *   'content' => string|null,
     *   'url' => string,
     *   'image_url' => string|null,
     *   'published_at' => Carbon|datetime|null,
     *   'author' => string|null,
     *   'category' => string|null,
     *   'language' => string|null,
     *   'publisher' => [
     *       'external_id' => string|null,
     *       'name' => string|null,
     *   ]|null,
     *   'raw' => array
     * ]
     */
    public function normalize(array $raw): array;
}
