<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'recipient_id',
        'name',
        'price',
        'is_ordered',
        'is_received',
        'note',
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

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class, 'recipient_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'gift_assignments');
    }
}

