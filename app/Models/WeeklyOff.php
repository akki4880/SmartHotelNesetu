<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyOff extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
