<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Models\Award;
use Illuminate\Support\Facades\Log;

class CreateAwardForApprovedApplication
{
    public function handle(ApplicationStatusChanged $event): void
    {
        if ($event->newStatus !== 'Approved') {
            return;
        }

        $app = $event->application->load('student', 'scholarship', 'award');

        if ($app->award) {
            return;
        }

        $award = Award::create([
            'application_id' => $app->id,
            'amount_granted' => $app->scholarship->amount,
            'date_awarded' => now(),
            'status' => 'Granted'
        ]);

        Log::info('Award created for approved application', [
            'award_id' => $award->id,
            'application_id' => $app->id,
            'amount' => $award->amount_granted,
        ]);
    }
}
