<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryStockAdjustment extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function items()
    {
        return $this->hasMany(InventoryStockAdjustmentItem::class, 'adjustment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
