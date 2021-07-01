<?php

namespace Cego\RequestLog\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Cego\RequestLog\Models\RequestLog;
use Illuminate\Support\Facades\Config;

class AutomaticLogCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:request-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs a clean up of logs deemed irrelevant';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Bail out if automatic clean up is not enabled
        if ( ! Config::get('request-log.automaticLogCleanUpEnabled', true)) {
            return 0;
        }

        $numberOfRetentionDays = Config::get('request-log.logRetentionNumberOfDays', 14);

        RequestLog::query()
            ->where('created_at', '<', Carbon::now()->subDays($numberOfRetentionDays))
            ->delete();

        return 0;
    }
}
