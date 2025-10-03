<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrainingRecordCertificateTracking;

class FixTrainingDateColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:training-date-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing training_date column in training_record_certificate_tracking table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing missing training_date column...');
        
        try {
            // Use your existing model method
            $result = TrainingRecordCertificateTracking::fixMissingTrainingDateColumn();
            
            if ($result['success']) {
                $this->info('✅ SUCCESS: ' . $result['message']);
                return Command::SUCCESS;
            } else {
                $this->error('❌ ERROR: ' . $result['message']);
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ ERROR: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
