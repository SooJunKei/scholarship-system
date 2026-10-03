<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_number', 'first_name', 'middle_name', 'last_name',
        'gender', 'date_of_birth', 'email', 'phone', 'address',
        'course', 'year_level', 'department',
    ];

    public function academicRecords()
    {
        return $this->hasMany(AcademicRecord::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
