<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'source_id','author_id','category_id','external_id','title','description',
        'content','url','image_url','published_at','language','raw'
    ];

    protected $casts = [
        'raw' => 'array',
        'published_at' => 'datetime',
    ];

    public function source() { return $this->belongsTo(Source::class); }
    public function author() { return $this->belongsTo(Author::class); }
    public function category() { return $this->belongsTo(Category::class); }
}
