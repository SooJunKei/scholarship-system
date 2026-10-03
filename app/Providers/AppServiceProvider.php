<?php

namespace App\Providers;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use App\Events\ScholarshipCreated;
use App\Events\StudentRegistered;
use App\Listeners\CreateAwardForApprovedApplication;
use App\Listeners\EventConsoleLogListener;
use App\Listeners\LogApplicationActivity;
use App\Listeners\LogScholarshipActivity;
use App\Listeners\LogStudentActivity;
use App\Listeners\SendStatusNotification;
use App\Listeners\UpdateApplicationStatistics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Event Console Logging - Register listener if enabled
        if (config('event_console.enabled')) {
            Event::listen(
                array_values(config('event_console.events_to_log', [])),
                EventConsoleLogListener::class
            );
        }

        // Existing events
        Event::listen(ApplicationSubmitted::class, [LogApplicationActivity::class, 'handleSubmitted']);
        Event::listen(StudentRegistered::class, [LogStudentActivity::class, 'handle']);
        Event::listen(ScholarshipCreated::class, [LogScholarshipActivity::class, 'handle']);

        // ApplicationStatusChanged delegates
        Event::listen(ApplicationStatusChanged::class, SendStatusNotification::class);
        Event::listen(ApplicationStatusChanged::class, UpdateApplicationStatistics::class);
        Event::listen(ApplicationStatusChanged::class, CreateAwardForApprovedApplication::class);
        Event::listen(ApplicationStatusChanged::class, [LogApplicationActivity::class, 'handleStatusChanged']);

        // Cache recent status changes for admin dashboard
        Event::listen(ApplicationStatusChanged::class, function (ApplicationStatusChanged $event) {
            $recentChanges = Cache::get('recent_status_changes', []);
            $recentChanges[] = [
                'application_id' => $event->application->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'timestamp' => now()->toDateTimeString()
            ];

            Cache::put('recent_status_changes', array_slice($recentChanges, -20), 86400);

            Log::info('Application status changed', [
                'application_id' => $event->application->id,
                'status' => $event->oldStatus . ' → ' . $event->newStatus,
            ]);
        });
    }
}
