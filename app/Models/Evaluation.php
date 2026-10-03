<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = ['application_id', 'user_id', 'score', 'remarks', 'evaluation_date'];

    protected $casts = ['evaluation_date' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
