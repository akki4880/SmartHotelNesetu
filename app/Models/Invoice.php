<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
