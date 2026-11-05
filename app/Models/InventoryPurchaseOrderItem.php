<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryPurchaseOrderItem extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function purchaseOrder()
    {
        return $this->belongsTo(InventoryPurchaseOrder::class);
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
