<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function idProofs()
    {
        return $this->hasMany(GuestIdProof::class);
        
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
