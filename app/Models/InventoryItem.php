<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function unit()
    {
        return $this->belongsTo(InventoryUnit::class);
    }

    public function vendor()
    {
        return $this->belongsTo(InventoryVendor::class);
    }
}
