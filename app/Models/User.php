<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $guarded = [];

    const LEVEL_ADMIN = "admin";
    const LEVEL_REGULAR = "regular";

    const DEPOSIT_TYPES = [
        "A" => 100,
        "B" => 200,
        "C" => 300,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, "referral_source_id");
    }

    public function referrer(): HasOne
    {
        return $this->hasOne(Referral::class);
    }

    /**
     * Daftar semua keturunan user ini dalam urutan referral
     */
    public function descendant_refs(): HasMany
    {
        return $this->hasMany(Path::class, "ancestor_id");
    }

    /**
     * Daftar semua nenek moyang user ini dalam urutan referral
     */
    public function ancestor_refs(): HasMany
    {
        return $this->hasMany(Path::class, "descendant_id");
    }

    /** Kedua anak dari user ini dalam urutan referral */
    public function children_refs(): HasMany
    {
        return $this->descendant_refs()
            ->where("tree_depth", 1);
    }

    /** Orang tua dari user ini dalam urutan referral */
    public function parent_ref(): HasOne
    {
        return $this->hasOne(Path::class, "descendant_id")
            ->where("tree_depth", 1);
    }

    /** Cari turunan dari user ini yang kakinya masih < 2 */
    public function nextEligibleDescendant(): self
    {
        /** @var User $candidate */
        $candidate = self::query()
            ->where("level", self::LEVEL_REGULAR)
            ->whereHas("ancestor_refs", function ($query) {
                $query->where("ancestor_id", $this->id);
            })
            ->where(function (Builder $query) {
                $query
                    ->whereHas("descendant_refs", function ($query) {
                        $query->where("tree_depth", 1);
                    }, "<", 2);
            })
            ->first();

        return $candidate ?? $this;
    }

    /** Sambungkan dua user */
    public static function attachDirectly($parent_id, $child_id)
    {
        $paths_table = Path::query()->getModel()->getTable();

        DB::insert(
            "
                    INSERT INTO $paths_table (ancestor_id, descendant_id, tree_depth, created_at, updated_at) (
                        SELECT ancestor_id, ?, tree_depth + 1, NOW(), NOW() FROM $paths_table WHERE descendant_id = ?
                    )
                ",
            [
                $child_id,
                $parent_id,
            ]);

        Path::query()->create([
            "ancestor_id" => $child_id,
            "descendant_id" => $child_id,
            "tree_depth" => 0,
        ]);
    }
}
