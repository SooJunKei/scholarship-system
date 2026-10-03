<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmittedRequirement extends Model
{
    protected $fillable = ['application_id', 'requirement_id', 'submission_date', 'status'];

    protected $casts = ['submission_date' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }
}
