<?php

namespace Nbj\RequestLog\Commands;

use Illuminate\Console\Command;

class InstallRequestLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:request-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs RequestLog';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Installing RequestLog');

        // Publish files
        $this->call('vendor:publish', [
            '--provider' => "Nbj\RequestLog\RequestLogServiceProvider",
            '--force'    => true
        ]);

        // Add Files to gitignore if not already present
        $pathToGitIgnoreFile = base_path() . DIRECTORY_SEPARATOR . '.gitignore';

        $filesThatNeedsToBeAdded = collect([
            '/app/Http/Middleware/LogRequest.php',
            '/app/RequestLog.php',
            '/config/request-log.php',
        ]);

        // We need to make sure we do not add existing lines to gitignore
        $currentGitIgnoreContents = file_get_contents($pathToGitIgnoreFile);
        $currentFiles = collect(explode("\n", $currentGitIgnoreContents));

        // Reject files that already exist in gitignore
        $filesThatNeedsToBeAdded = $filesThatNeedsToBeAdded
            ->reject(function ($file) use ($currentFiles) {
                foreach ($currentFiles as $currentFile) {
                    if ($file == $currentFile) {
                        return true;
                    }
                }

                $this->info(sprintf('Adding file to .gitignore <comment>[%s]</comment>', $file));

                return false;
            });

        // Write files to gitignore files
        file_put_contents($pathToGitIgnoreFile, $filesThatNeedsToBeAdded->implode("\n"), FILE_APPEND);

        return 0;
    }
}
