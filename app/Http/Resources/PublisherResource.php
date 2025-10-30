<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublisherResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'external_id'          => $this->external_id,
            'canonical_source_id'  => $this->canonical_source_id,
        ];
    }
}
