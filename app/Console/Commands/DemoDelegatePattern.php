<?php

namespace App\Console\Commands;

use App\Events\ApplicationStatusChanged;
use App\Models\Application;
use Illuminate\Console\Command;

class DemoDelegatePattern extends Command
{
    protected $signature = 'demo:delegates {application_id?}';
    
    protected $description = 'Demonstrate the delegate pattern with ApplicationStatusChanged event';

    public function handle()
    {
        $this->info("╔══════════════════════════════════════════════════════════╗");
        $this->info("║       DELEGATE PATTERN DEMONSTRATION                     ║");
        $this->info("║   One Event → Multiple Handlers (Delegates)              ║");
        $this->info("╚══════════════════════════════════════════════════════════╝");
        $this->newLine();
        
        // Get application ID from argument or use first pending application
        $appId = $this->argument('application_id');
        
        if ($appId) {
            $application = Application::find($appId);
        } else {
            $application = Application::where('status', 'Pending')->first();
        }
        
        if (!$application) {
            $this->error("No application found. Creating a test scenario...");
            $application = Application::first();
        }
        
        if (!$application) {
            $this->error("No applications in database. Please create one first.");
            return 1;
        }
        
        $application->load('student', 'scholarship');
        
        $this->info("📋 APPLICATION DETAILS:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $application->id],
                ['Student', $application->student->full_name],
                ['Scholarship', $application->scholarship->scholarship_name],
                ['Current Status', $application->status],
            ]
        );
        
        $this->newLine();
        $oldStatus = $application->status;
        $newStatus = $this->choice(
            'Select new status to trigger the delegates:',
            ['Pending', 'Under Review', 'Approved', 'Rejected'],
            0
        );
        
        if ($oldStatus === $newStatus) {
            $this->warn("Status unchanged. No event will be triggered.");
            return 0;
        }
        
        $this->newLine();
        $this->info("🚀 TRIGGERING EVENT: ApplicationStatusChanged");
        $this->info("   Status: {$oldStatus} → {$newStatus}");
        $this->newLine();
        $this->info(str_repeat("=", 60));
        $this->newLine();
        
        // Update the application status and dispatch the event
        $application->update(['status' => $newStatus]);
        
        // This will trigger ALL registered delegates
        ApplicationStatusChanged::dispatch($application, $oldStatus, $newStatus);
        
        $this->newLine();
        $this->info(str_repeat("=", 60));
        $this->newLine();
        
        $this->info("✅ EVENT COMPLETED");
        $this->info("   All 5 delegates have been executed:");
        $this->info("   ✓ Delegate #1: SendStatusNotification");
        $this->info("   ✓ Delegate #2: UpdateApplicationStatistics");
        $this->info("   ✓ Delegate #3: CreateAwardForApprovedApplication");
        $this->info("   ✓ Delegate #4: LogApplicationActivity");
        $this->info("   ✓ Delegate #5: Anonymous/Lambda Handler");
        
        $this->newLine();
        $this->info("💡 Check storage/logs/laravel.log for detailed logs");
        
        return 0;
    }
}
