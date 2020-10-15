<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function source(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            "referral_source_id",
        );
    }
}
