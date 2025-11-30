<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimpleGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'participant_id',
        'name',
        'image_path',
        'price',
        'link_url',
        'is_ordered',
        'is_received',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_ordered' => 'boolean',
        'is_received' => 'boolean',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
    }
}
