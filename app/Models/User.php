<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Convenience role checks
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // Relationships
    public function reportedItems()
    {
        return $this->hasMany(Item::class , 'reported_by');
    }

    public function foundItems()
    {
        return $this->hasMany(Item::class , 'found_by');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class , 'claimer_id');
    }

    public function reviewedClaims()
    {
        return $this->hasMany(Claim::class , 'reviewed_by');
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class , 'matched_by');
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class , 'actor_id');
    }
}