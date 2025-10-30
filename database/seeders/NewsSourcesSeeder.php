<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Source;

class NewsSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Source::updateOrCreate(['slug' => 'newsapi'], [
            'name' => 'NewsAPI.org',
            'provider' => 'newsapi',
            'type' => 'aggregator',
            'config' => []
        ]);

        Source::updateOrCreate(['slug' => 'guardian'], [
            'name' => 'The Guardian',
            'provider' => 'guardian',
            'type' => 'direct',
            'config' => []
        ]);

        Source::updateOrCreate(['slug' => 'nytimes'], [
            'name' => 'New York Times',
            'provider' => 'nytimes',
            'type' => 'direct',
            'config' => []
        ]);
    }
}
