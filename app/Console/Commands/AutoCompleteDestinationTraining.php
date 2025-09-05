<?php

namespace App\Console\Commands;

use App\Jobs\AutoCompleteDestinationTrainingJob;
use Illuminate\Console\Command;

class AutoCompleteDestinationTraining extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'training:auto-complete-destination';

    /**
     * The console command description.
     */
    protected $description = 'Auto-complete destination knowledge training that has been in progress for 1 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-completion of destination knowledge training...');
        
        // Dispatch the job
        AutoCompleteDestinationTrainingJob::dispatch();
        
        $this->info('Auto-completion job dispatched successfully.');
        
        return 0;
    }
}
