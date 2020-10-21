<?php

namespace Cego\RequestLog\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Cego\RequestLog\Models\RequestLog;
use Illuminate\Support\Facades\Config;
use Cego\RequestInsurance\Models\RequestInsurance;

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
    public function handle()
    {
        $isEnabled = Config::get('request-log.automaticLogCleanUpEnabled', false);

        // Bail out if automatic clean up is not enabled
        if ( ! $isEnabled) {
            $this->info('Automatic clean up of logs is not enabled in config');

            return 0;
        }

        $numberOfRetentionDays = Config::get('request-log.logRetentionNumberOfDays', 90);
        $this->info(sprintf('Deleting logs older than %d days', $numberOfRetentionDays));

        RequestLog::query()
            ->where('created_at', '<', Carbon::now()->subDays($numberOfRetentionDays))
            ->delete();

        return 0;
    }
}
