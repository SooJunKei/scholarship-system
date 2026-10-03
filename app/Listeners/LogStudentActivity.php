<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Events\StudentRegistered;
use Illuminate\Support\Facades\Auth;

class LogStudentActivity
{
    public function handle(StudentRegistered $event): void
    {
        ActivityLog::create([
            'event'        => 'StudentRegistered',
            'description'  => "New student registered: {$event->student->full_name} ({$event->student->student_number}).",
            'subject_type' => get_class($event->student),
            'subject_id'   => $event->student->id,
            'user_id'      => Auth::id(),
        ]);
    }
}
