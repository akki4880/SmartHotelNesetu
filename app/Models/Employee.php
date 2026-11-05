<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function userRole()
    {
        return $this->belongsTo(UserRole::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function weeklyOffs()
    {
        return $this->hasMany(WeeklyOff::class);
    }
}
