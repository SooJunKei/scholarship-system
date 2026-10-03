<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicRecord extends Model
{
    protected $fillable = ['student_id', 'gpa', 'semester', 'academic_year'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
