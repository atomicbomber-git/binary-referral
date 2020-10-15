<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function ancestor()
    {
        return $this->belongsTo(User::class, "ancestor_id");
    }

    public function descendant()
    {
        return $this->belongsTo(User::class, "descendant_id");
    }

    public function depositor()
    {
        return $this->belongsTo(User::class, "depositor_id");
    }
}
