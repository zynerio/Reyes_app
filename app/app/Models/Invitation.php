<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'email',
        'token',
        'status',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
    }
}

