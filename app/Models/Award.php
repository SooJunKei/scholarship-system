<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $fillable = ['application_id', 'date_awarded', 'amount_granted', 'status'];

    protected $casts = ['date_awarded' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
