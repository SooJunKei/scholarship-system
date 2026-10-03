<?php

namespace App\Events;

use App\Models\Scholarship;
use Illuminate\Foundation\Events\Dispatchable;

class ScholarshipCreated
{
    use Dispatchable;

    public function __construct(public Scholarship $scholarship) {}
}
