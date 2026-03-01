<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'type', // lost|found
        'title',
        'description',
        'category',
        'brand',
        'color',
        'location',
        'date_incident',
        'status', // pending|matched|claimed|archived
        'image_path',
        'qr_token',
        'qr_generated_at',
        'reported_by',
        'found_by',
    ];

    protected $casts = [
        'date_incident' => 'date',
        'qr_generated_at' => 'datetime',
    ];

    // Relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function finder()
    {
        return $this->belongsTo(User::class, 'found_by');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function lostMatches()
    {
        return $this->hasMany(MatchModel::class, 'lost_item_id');
    }

    public function foundMatches()
    {
        return $this->hasMany(MatchModel::class, 'found_item_id');
    }

    // Optional helper scopes (nice for API filtering)
    public function scopeLost($query)
    {
        return $query->where('type', 'lost');
    }

    public function scopeFound($query)
    {
        return $query->where('type', 'found');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}