<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateApplicationStatistics
{
    public function handle(ApplicationStatusChanged $event): void
    {
        Cache::forget('application_statistics');
        Cache::forget("scholarship_{$event->application->scholarship_id}_stats");

        $stats = [
            'total' => DB::table('applications')->count(),
            'pending' => DB::table('applications')->where('status', 'Pending')->count(),
            'under_review' => DB::table('applications')->where('status', 'Under Review')->count(),
            'approved' => DB::table('applications')->where('status', 'Approved')->count(),
            'rejected' => DB::table('applications')->where('status', 'Rejected')->count(),
        ];

        Cache::put('application_statistics', $stats, 3600);

        Log::info('Application statistics updated', [
            'application_id' => $event->application->id,
            'new_status' => $event->newStatus,
        ]);
    }
}
