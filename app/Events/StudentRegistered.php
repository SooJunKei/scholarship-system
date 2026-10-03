<?php

namespace App\Events;

use App\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;

class StudentRegistered
{
    use Dispatchable;

    public function __construct(public Student $student) {}
}
