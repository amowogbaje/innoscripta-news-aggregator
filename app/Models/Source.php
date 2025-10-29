<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = ['name','slug','provider','config'];

    protected $casts = [
        'config' => 'array',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
