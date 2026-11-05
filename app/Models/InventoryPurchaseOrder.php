<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryPurchaseOrder extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    // ================= RELATIONS =================

    public function vendor()
    {
        return $this->belongsTo(InventoryVendor::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryPurchaseOrderItem::class, 'purchase_order_id');
    }

    public function hotel()
    {
        return $this->belongsTo(\App\Models\Hotel::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ================= ACCESSORS =================

    // ✅ Status badge HTML (for blade)
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'draft' => '<span class="badge bg-warning text-dark">Draft</span>',
            'approved' => '<span class="badge bg-primary">Approved</span>',
            'sent' => '<span class="badge bg-info">Sent</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    // ✅ Human readable status
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    // ================= HELPERS =================

    // ✅ Check editable
    public function isEditable()
    {
        return $this->status === 'draft';
    }

    // ✅ Can approve
    public function canApprove()
    {
        return $this->status === 'draft';
    }

    // ✅ Can send
    public function canSend()
    {
        return $this->status === 'approved';
    }

    // ✅ Can cancel
    public function canCancel()
    {
        return in_array($this->status, ['draft', 'approved']);
    }
}
