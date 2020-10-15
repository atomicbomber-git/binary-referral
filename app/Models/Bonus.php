<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bonus extends Model
{
    use HasFactory;

    protected $guarded = [];

    const TYPE_REFERRAL = "referral";
    const TYPE_UPLINK = "uplink";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
