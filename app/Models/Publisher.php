<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_id',
        'canonical_source_id',
    ];

    public function canonicalSource()
    {
        return $this->belongsTo(Source::class, 'canonical_source_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
