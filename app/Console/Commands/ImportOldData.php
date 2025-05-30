<?php

namespace App\Console\Commands;

use App\Models\VoipRecord;
use Illuminate\Console\Command;

class ImportOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Requests user to specify path to old data and then loops through all log files in that directory, reads them, and saves the data to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->ask('Please specify the path to the old data directory');
        
        if (!is_dir($path)) {
            $this->error('The specified path is not a directory.');
            return;
        }

        $files = glob($path . '/*.log');
        if (empty($files)) {
            $this->error('No log files found in the specified directory.');
            return;
        }

        foreach ($files as $file) {
            if (!is_readable($file)) {
                $this->error("Cannot read file: $file");
                continue;
            }

            $contents = file_get_contents($file);
            if ($contents === false) {
                $this->error("Failed to read file: $file");
                continue;
            }

            $handle = fopen($file, "r") or die("Couldn't get handle");
            if ($handle) {
                while (!feof($handle)) {
                    $line = fgets($handle);
                    // Process line here..
                    VoipRecord::create($line);
                }
                fclose($handle);
            }
        }
        
        $this->info('Data import completed.');
        $this->info('All log files have been processed.');
    }
}
