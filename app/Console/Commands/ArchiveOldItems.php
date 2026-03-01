<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;

class ArchiveOldItems extends Command
{
    protected $signature = 'items:archive';

    protected $description = 'Archive old items';

    public function handle()
    {
        Item::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(30))
            ->update(['status' => 'archived']);

        $this->info('Old items archived.');
    }
}