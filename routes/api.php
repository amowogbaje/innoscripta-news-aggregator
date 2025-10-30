<?php

use App\Http\Controllers\Api\ArticleController;

Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
    Route::get('sources', [ArticleController::class, 'sources']);
    Route::get('categories', [ArticleController::class, 'categories']);
});