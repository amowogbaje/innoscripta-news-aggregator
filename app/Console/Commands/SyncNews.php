<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Source;
use App\Services\News\NewsSyncService;
use App\Services\News\Adapters\NewsApiAdapter;
use App\Services\News\Adapters\GuardianAdapter;
use App\Services\News\Adapters\NYTimesAdapter;

class SyncNews extends Command
{
    protected $signature = 'news:sync {source?}';
    protected $description = 'Sync news from configured sources or single source slug';

    public function handle()
    {
        $slug = $this->argument('source'); 
        $sources = $slug ? Source::where('slug', $slug)->get() : Source::all();

        foreach ($sources as $source) {
            $this->info("Syncing: {$source->name} ({$source->provider})");
            $adapter = match ($source->provider) {
                'newsapi' => new NewsApiAdapter(),
                'guardian' => new GuardianAdapter(),
                'nytimes' => new NYTimesAdapter(),
                default => null,
            };

            if (! $adapter) {
                $this->warn("No adapter found for provider: {$source->provider}");
                continue;
            }

            $service = new NewsSyncService($adapter, $source);
            $result = $service->sync();
            $this->info("Done: created {$result['created']} updated {$result['updated']} skipped {$result['skipped']}");
        }

        return 0;
    }
}
