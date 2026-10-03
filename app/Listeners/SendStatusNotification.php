<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use Illuminate\Support\Facades\Log;

class SendStatusNotification
{
    public function handle(ApplicationStatusChanged $event): void
    {
        $app = $event->application->load('student', 'scholarship');

        Log::info('Application status notification', [
            'application_id' => $app->id,
            'status' => $event->newStatus,
            'student' => $app->student->full_name,
            'scholarship' => $app->scholarship->scholarship_name,
        ]);
    }
}
