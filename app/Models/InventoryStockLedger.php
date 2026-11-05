<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStockLedger extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
