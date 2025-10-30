<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'source'        => new SourceResource($this->whenLoaded('source')),
            'publisher'     => new PublisherResource($this->whenLoaded('publisher')),
            'author'        => $this->author?->name,
            'category'      => $this->category?->name,
            'title'         => $this->title,
            'description'   => $this->description,
            'content'       => $this->content,
            'url'           => $this->url,
            'image_url'     => $this->image_url,
            'published_at'  => optional($this->published_at)->toIso8601String(),
            'language'      => $this->language,
        ];
    }
}
