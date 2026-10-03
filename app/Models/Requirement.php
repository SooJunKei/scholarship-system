<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = ['requirement_name', 'description'];

    public function scholarships()
    {
        return $this->belongsToMany(Scholarship::class, 'scholarship_requirements');
    }
}
