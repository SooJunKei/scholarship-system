<?php

namespace App\Events;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Auth;

class ApplicationStatusChanged
{
    use Dispatchable;

    public function __construct(
        public Application $application,
        public string $oldStatus,
        public string $newStatus,
        public ?User $changedBy = null,
        public ?\DateTime $changedAt = null
    ) {
        // Auto-populate if not provided
        $this->changedBy = $changedBy ?? Auth::user();
        $this->changedAt = $changedAt ?? new \DateTime();
    }
}
