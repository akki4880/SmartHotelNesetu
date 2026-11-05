<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
        protected $guarded = ['id', 'created_at', 'updated_at'];

        public function plan()
        {
                return $this->belongsTo(Plan::class, 'plan_id');
        }

        public function getExpiryTextAttribute()
        {
                if (!$this->end_date) return '-';

                $today = now()->startOfDay();
                $end = \Carbon\Carbon::parse($this->end_date);

                $days = $today->diffInDays($end, false);

                if ($days == 0) return 'Expires Today';
                if ($days > 0) return "Expires in {$days} days";

                return "Expired " . abs($days) . " days ago";
        }
}
