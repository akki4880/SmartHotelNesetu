<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
