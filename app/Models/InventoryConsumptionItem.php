<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryConsumptionItem extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function unit()
    {
        return $this->belongsTo(InventoryUnit::class);
    }
}
