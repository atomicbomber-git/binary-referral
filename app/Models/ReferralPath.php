<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralPath extends Model
{
    use HasFactory;

    public function direct_child_paths(): HasMany
    {
        return $this->hasMany(self::class, "ancestor_id")
            ->where("tree_depth", 1);
    }

    public function child_user(): BelongsTo
    {
        return $this->belongsTo(User::class, "descendant_id");
    }

    public function parent_user()
    {
        return $this->belongsTo(User::class, "ancestor_id");
    }
}
