<?php

namespace App\Services\News;

use App\Services\News\Contracts\NewsSourceInterface;
use App\Models\Source;
use App\Models\Article;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsSyncService
{
    protected NewsSourceInterface $adapter;
    protected Source $sourceModel;

    public function __construct(NewsSourceInterface $adapter, Source $sourceModel)
    {
        $this->adapter = $adapter;
        $this->sourceModel = $sourceModel;
    }

    /**
     * Sync latest items.
     * $params forwarded to adapter.fetch (e.g. category,page).
     */
    public function sync(array $params = []): array
    {
        $rawItems = $this->adapter->fetch($params);
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($rawItems as $raw) {
            $item = $this->adapter->normalize($raw);
            if (empty($item['url'])) {
                $results['skipped']++;
                continue;
            }

            $externalId = $item['external_id'] ?? $item['url'];

            DB::beginTransaction();
            try {
                $authorId = null;
                if (!empty($item['author'])) {
                    $author = Author::firstOrCreate(
                        ['name' => trim($item['author'])],
                        ['external_id' => null]
                    );
                    $authorId = $author->id;
                }

                $categoryId = null;
                if (!empty($item['category'])) {
                    $slug = Str::slug($item['category']);
                    $category = Category::firstOrCreate(
                        ['slug' => $slug],
                        ['name' => $item['category']]
                    );
                    $categoryId = $category->id;
                }

                $publisherId = null;
                $publisherData = $this->resolvePublisher($item, $this->sourceModel);

                $publisher = Publisher::firstOrCreate(
                    ['external_id' => $publisherData['external_id']],
                    [
                        'name' => $publisherData['name'],
                        'canonical_source_id' => $this->resolveCanonicalSource($publisherData['name']),
                    ]
                );

                $publisherId = $publisher->id;


                $existing = Article::where('source_id', $this->sourceModel->id)
                    ->where(function ($q) use ($externalId, $item) {
                        $q->where('external_id', $externalId)
                            ->orWhere('url', $item['url']);
                    })->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
                    ->first();

                $payload = [
                    'source_id' => $this->sourceModel->id,
                    'publisher_id' => $publisherId,
                    'author_id' => $authorId,
                    'category_id' => $categoryId,
                    'external_id' => $externalId,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'content' => $item['content'] ?? null,
                    'url' => $item['url'],
                    'image_url' => $item['image_url'] ?? null,
                    'published_at' => isset($item['published_at']) ? Carbon::parse($item['published_at']) : null,
                    'language' => $item['language'] ?? null,
                    'raw' => $item['raw'] ?? null,
                ];

                if ($existing) {
                    $existing->update($payload);
                    $results['updated']++;
                } else {
                    Article::create($payload);
                    $results['created']++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Article sync failed', ['error' => $e->getMessage(), 'item' => $item]);
                $results['skipped']++;
            }
        }

        return $results;
    }

    protected function resolveCanonicalSource(string $publisherName): ?int
    {
        $canonical = Source::where('name', 'like', "%{$publisherName}%")->first();
        return $canonical?->id;
    }

    protected function resolvePublisher(array $normalized, Source $source): array
    {
        if (!empty($normalized['publisher']) && !empty($normalized['publisher']['name'])) {
            return $normalized['publisher'];
        }
        return [
            'external_id' => $source->external_id ?? $source->slug ?? Str::slug($source->name),
            'name' => $source->name,
        ];
    }
}
