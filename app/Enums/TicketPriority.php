<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'badge-success',
            self::Medium => 'badge-warning',
            self::High => 'badge-danger',
        };
    }
}
