<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Events\ApplicationSubmitted;
use App\Events\ApplicationStatusChanged;
use Illuminate\Support\Facades\Auth;

class LogApplicationActivity
{
    public function handleSubmitted(ApplicationSubmitted $event): void
    {
        if (Auth::id() === null) {
            return;
        }

        $app = $event->application->load('student', 'scholarship');

        ActivityLog::create([
            'event'        => 'ApplicationSubmitted',
            'description'  => "Application submitted by {$app->student->full_name} for {$app->scholarship->scholarship_name}.",
            'subject_type' => Application::class,
            'subject_id'   => $app->id,
            'user_id'      => Auth::id(),
        ]);
    }

    public function handleStatusChanged(ApplicationStatusChanged $event): void
    {
        if (Auth::id() === null) {
            return;
        }

        $app = $event->application->load('student', 'scholarship');

        ActivityLog::create([
            'event'        => 'ApplicationStatusChanged',
            'description'  => "Application of {$app->student->full_name} for {$app->scholarship->scholarship_name} changed from {$event->oldStatus} to {$event->newStatus}.",
            'subject_type' => Application::class,
            'subject_id'   => $app->id,
            'user_id'      => Auth::id(),
        ]);
    }
}
