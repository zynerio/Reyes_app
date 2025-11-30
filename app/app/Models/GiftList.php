<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'notes',
        'finalized_at',
        'theme',
        'uses_people',
        'show_sublist_in_main',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
        'uses_people' => 'boolean',
        'show_sublist_in_main' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class, 'list_id');
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class, 'list_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'list_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ListShare::class, 'list_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'list_id');
    }
}
