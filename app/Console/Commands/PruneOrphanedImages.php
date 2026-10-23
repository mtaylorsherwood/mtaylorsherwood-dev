<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneOrphanedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete uploaded images that were never attached to a post or project';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $orphans = Image::query()
            ->whereNull('post_id')
            ->whereNull('project_id')
            ->where('created_at', '<', Carbon::now()->subDay())
            ->get();

        // Deleted one at a time (rather than a bulk query) so the model's
        // `deleted` event fires and removes the underlying storage object.
        $orphans->each(fn(Image $image) => $image->delete());

        $this->info("Pruned {$orphans->count()} orphaned image(s).");

        return self::SUCCESS;
    }
}
