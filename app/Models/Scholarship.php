<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $fillable = [
        'scholarship_name', 'description', 'sponsor', 'amount', 'application_deadline',
    ];

    protected $casts = ['application_deadline' => 'date'];

    public function requirements()
    {
        return $this->belongsToMany(Requirement::class, 'scholarship_requirements');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
