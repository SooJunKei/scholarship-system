<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['student_id', 'scholarship_id', 'date_applied', 'status'];

    protected $casts = ['date_applied' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function award()
    {
        return $this->hasOne(Award::class);
    }

    public function submittedRequirements()
    {
        return $this->hasMany(SubmittedRequirement::class);
    }
}
