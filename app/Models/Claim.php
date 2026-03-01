<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'item_id',
        'claimer_id',
        'status', // pending|approved|denied|released
        'proof_details',
        'proof_image_path',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'released_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimer_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}