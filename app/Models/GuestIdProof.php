<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestIdProof extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
