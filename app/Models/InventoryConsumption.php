<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryConsumption extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function items()
    {
        return $this->hasMany(InventoryConsumptionItem::class, 'consumption_id');
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
