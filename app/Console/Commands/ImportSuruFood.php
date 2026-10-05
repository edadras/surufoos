<?php

namespace App\Console\Commands;

use App\Services\Import\SuruFoodImporter;
use Illuminate\Console\Command;

class ImportSuruFood extends Command
{
    protected $signature = 'suru:import
        {--source= : Base URL of the source site (default: config suru.import.source)}
        {--only=* : Limit to sections: settings, categories, products, pages, posts, redirects}';

    protected $description = 'Migrate the real Suru Food content (products, categories, images, pages, articles, brand data) into this site';

    public function handle(SuruFoodImporter $importer): int
    {
        $source = $this->option('source') ?: config('suru.import.source');
        $only = array_values(array_intersect($this->option('only'), SuruFoodImporter::SECTIONS));

        $this->info("Importing from {$source}".($only ? ' ('.implode(', ', $only).')' : ''));
        $counts = $importer->run($source, $only, fn (string $line) => $this->line($line));

        $this->table(['imported', 'updated', 'skipped', 'failed'], [[$counts['imported'], $counts['updated'], $counts['skipped'], $counts['failed']]]);
        $this->comment('Every item is logged in content_import_logs (Admin › درون‌ریزی Suru Food). Run `php artisan queue:work` so image variants are generated.');

        return $counts['failed'] > 0 && array_sum($counts) === $counts['failed'] ? self::FAILURE : self::SUCCESS;
    }
}
