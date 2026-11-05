<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryGoodsReceiptItem extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function goodsReceipt()
    {
        return $this->belongsTo(InventoryGoodsReceipt::class);
    }

    public function items()
    {
        return $this->hasMany(
            InventoryGoodsReceiptItem::class,
            'goods_receipt_id' // 👈 IMPORTANT
        );
    }
}
