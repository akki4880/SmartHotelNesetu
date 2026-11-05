<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'hotel_id',
        'employee_id',
        'shift_id',
        'attendance_date',
        'check_in',
        'check_out',
        'working_hours',
        'overtime_hours',
        'status',
        'late_minutes',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'working_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
