<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryGoodsReceiptItem extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $appends = [
        'item_name',
        'ordered_quantity',
        'unit_name',
        'remaining_quantity',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(InventoryGoodsReceipt::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function unit()
    {
        return $this->belongsTo(InventoryUnit::class, 'unit_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(InventoryPurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function items()
    {
        return $this->hasMany(
            InventoryGoodsReceiptItem::class,
            'goods_receipt_id' // 👈 IMPORTANT
        );
    }

    public function getItemNameAttribute()
    {
        return $this->item?->name ?? $this->purchaseOrderItem?->item?->name ?? null;
    }

    public function getOrderedQuantityAttribute()
    {
        return (float) ($this->purchaseOrderItem?->quantity ?? $this->received_quantity ?? 0);
    }

    public function getUnitNameAttribute()
    {
        return $this->unit?->short_name ?? $this->purchaseOrderItem?->unit?->short_name ?? null;
    }

    public function getRemainingQuantityAttribute()
    {
        $ordered = (float) ($this->purchaseOrderItem?->quantity ?? $this->ordered_quantity ?? 0);
        $received = (float) ($this->purchaseOrderItem?->received_quantity ?? $this->received_quantity ?? 0);

        return max($ordered - $received, 0);
    }
}
