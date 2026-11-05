<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; 

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_role_id',
        'permissions',
        'hotel_id'
    ];

    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password' => 'hashed',
    //     ];
    // }

    public function isAdmin(): bool
{
        return (int) $this->user_role_id === 1;
    }

    public function isDeveloper(): bool
    {
        return (int) $this->user_role_id === 2;
    }

    public function isClient(): bool
    {
        return (int) $this->user_role_id > 3;
    }

    public function isSupport(): bool
    {
        return  false;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'sender_id');
    }

    // User.php
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function hasPermission($module, $action)
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Role 1: Use permissions stored directly in the users table.
        if ((int) $user->user_role_id === 1 || (int) $user->user_role_id === 2) {
            $permissions = $user->permissions;
        }

        // Role 3: Use permissions from the plan assigned to the user's hotel.
        elseif ((int) $user->user_role_id === 3) {
            $hotel = \App\Models\Hotel::with('subscription.plan')
                ->find($user->hotel_id);

            $permissions = $hotel?->subscription?->plan?->permissions;
        } else {
            return false;
        }

        // Decode JSON permissions if necessary.
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }

        if (!is_array($permissions)) {
            return false;
        }

        return isset($permissions[$module])
            && is_array($permissions[$module])
            && in_array($action, $permissions[$module]);
    }

    public function hasAnyPermission(array $modules, $action = 'view')
    {
        foreach ($modules as $module) {
            if ($this->hasPermission($module, $action)) {
                return true;
            }
        }

        return false;
    }
}
