<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('external_id')->nullable()->comment('unique id from provider');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('url')->unique();
            $table->string('image_url')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('language', 10)->nullable();
            $table->json('raw')->nullable()->comment('raw provider payload');
            $table->timestamps();

            $table->unique(['source_id', 'external_id']);
            $table->index(['published_at', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
