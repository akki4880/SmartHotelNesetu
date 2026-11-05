<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryGoodsReceipt extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'grn_date' => 'date',
        'invoice_date' => 'date',
    ];


    public function items()
    {
        return $this->hasMany(
            InventoryGoodsReceiptItem::class,
            'goods_receipt_id' // 👈 IMPORTANT
        );
    }

    public function vendor()
    {
        return $this->belongsTo(InventoryVendor::class, 'vendor_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(InventoryPurchaseOrder::class, 'purchase_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
