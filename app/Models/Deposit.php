<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $guarded = [];

    const TYPE_REGULAR = "regular";
    const TYPE_REFERRAL_BONUS = "referral_bonus";
    const TYPE_UPLINE_BONUS = "upline_bonus";
}
