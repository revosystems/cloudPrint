<?php

namespace App\Console\Commands;

use App\PrintJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearOldPrintJobs extends Command
{
    protected $signature = 'revo:clearOldPrintJobs';

    protected $description = 'Removes print jobs older than a month.';

    public function handle()
    {
        $deletedJobs = PrintJob::where('created_at', '<', now()->subMonth())->delete();
        Log::info("ClearOldPrintJobs: Deleted {$deletedJobs} old print jobs");
    }
}
