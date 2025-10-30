<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Http\Resources\ArticleResource;
use App\Models\Source;
use App\Models\Category;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'q',
            'source',
            'category',
            'author',
            'from',
            'to',
            'preferred_sources',
            'per_page'
        ]);

        $pageSize = (int) ($filters['per_page'] ?? 20);

        $query = Article::with(['source', 'author', 'category', 'publisher'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%");
                });
            })
            ->when($filters['source'] ?? null, function ($q, $source) {
                $q->whereHas(
                    'source',
                    fn($qs) =>
                    $qs->where('slug', $source)->orWhere('id', $source)
                );
            })
            ->when($filters['category'] ?? null, function ($q, $category) {
                $q->whereHas(
                    'category',
                    fn($qc) =>
                    $qc->where('slug', $category)->orWhere('id', $category)
                );
            })
            ->when($filters['author'] ?? null, function ($q, $author) {
                $q->whereHas(
                    'author',
                    fn($qa) =>
                    $qa->where('name', 'like', "%{$author}%")->orWhere('id', $author)
                );
            })
            ->when(
                $filters['from'] ?? null,
                fn($q, $from) =>
                $q->whereDate('published_at', '>=', $from)
            )
            ->when(
                $filters['to'] ?? null,
                fn($q, $to) =>
                $q->whereDate('published_at', '<=', $to)
            )
            ->when(!empty($filters['preferred_sources']), function ($q) use ($filters) {
                $prefs = (array) $filters['preferred_sources'];

                $q->where(function ($sub) use ($prefs) {
                    // Match by source slug or name
                    $sub->whereHas(
                        'source',
                        fn($qs) =>
                        $qs->whereIn('slug', $prefs)
                            ->orWhereIn('name', $prefs)
                    )
                        // Match by publisher external_id or name
                        ->orWhereHas(
                            'publisher',
                            fn($qp) =>
                            $qp->whereIn('external_id', $prefs)
                                ->orWhereIn('name', $prefs)
                        );
                });
            })
            ->orderByDesc('published_at');

        $paginated = $query->paginate($pageSize)->appends($request->query());

        return ArticleResource::collection($paginated);
    }


    public function show(Article $article)
    {
        return new ArticleResource($article->load(['source', 'author', 'category']));
    }

    public function sources()
    {
        return response()->json(Source::all());
    }

    public function categories()
    {
        return response()->json(Category::all());
    }
}
