<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchModel extends Model
{
    protected $table = 'match_models';

    protected $fillable = [
        'lost_item_id',
        'found_item_id',
        'matched_by',
        'matched_at',
        'notes',
    ];

    protected $casts = [
        'matched_at' => 'datetime',
    ];

    public function lostItem()
    {
        return $this->belongsTo(Item::class , 'lost_item_id');
    }

    public function foundItem()
    {
        return $this->belongsTo(Item::class , 'found_item_id');
    }

    public function matcher()
    {
        return $this->belongsTo(User::class , 'matched_by');
    }
}