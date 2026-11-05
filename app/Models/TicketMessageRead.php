<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessageRead extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }
}
