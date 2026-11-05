<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStockAdjustmentItem extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function adjustment()
    {
        return $this->belongsTo(InventoryStockAdjustment::class, 'adjustment_id');
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function unit()
    {
        return $this->belongsTo(InventoryUnit::class, 'unit_id');
    }
}
