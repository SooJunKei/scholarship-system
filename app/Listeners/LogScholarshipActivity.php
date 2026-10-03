<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Events\ScholarshipCreated;
use Illuminate\Support\Facades\Auth;

class LogScholarshipActivity
{
    public function handle(ScholarshipCreated $event): void
    {
        ActivityLog::create([
            'event'        => 'ScholarshipCreated',
            'description'  => "New scholarship created: {$event->scholarship->scholarship_name} sponsored by {$event->scholarship->sponsor}.",
            'subject_type' => get_class($event->scholarship),
            'subject_id'   => $event->scholarship->id,
            'user_id'      => Auth::id(),
        ]);
    }
}
