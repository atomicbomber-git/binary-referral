<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\Path;
use App\Models\UsedUplinkBonus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserDepositStoreController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function __invoke(Request $request, User $user)
    {
        $data = $request->validate([
            "deposit_type" => ["required", Rule::in(array_keys(User::DEPOSIT_TYPES))]
        ]);

        DB::beginTransaction();

        $user->update([
            "deposit_amount" => User::DEPOSIT_TYPES[$data["deposit_type"]],
            "deposited_at" => now(),
        ]);

        // Mencari sumber referral
        $referred_user = User::query()
            ->whereHas("referrals", function (Builder $builder) use ($user) {
                $builder->where("user_id", $user->id);
            })
            ->first();

        // Berikan bonus 10% jika sumber referral telah melakukan deposit
        if ($referred_user->deposit_amount ?? false) {
            $referred_user->bonuses()->create([
                "type" => Bonus::TYPE_REFERRAL,
                "amount" => User::DEPOSIT_TYPES[$data["deposit_type"]] * 0.1,
            ]);
        }

        // Kandidat pemberian bonus kaki 5%
        $parents = $user
            ->ancestor_refs()
            ->select("ancestor_id", "descendant_id", "tree_depth")
            ->whereColumn("ancestor_id", "<>", "descendant_id")

            // Hanya yang sudah deposit yang dijadikan kandidat
            ->whereHas("ancestor", function (Builder $builder) {
                $builder->whereNotNull("deposited_at");
            })
            ->orderByDesc("tree_depth")
            ->addSelect([
                "depositor_id" => User::query()->from(\DB::raw("users user_sub"))
                    ->select("user_sub.id")
                    ->whereNotNull("deposit_amount")
                    ->orderBy("deposited_at")
                    ->whereNotIn("id",
                        UsedUplinkBonus::query()
                            ->select("used_uplink_bonuses.user_id")
                            ->whereColumn("used_uplink_bonuses.uplink_id", "paths.ancestor_id")
                    )
                    ->whereIn("user_sub.id",
                        Path::query()
                            ->from(\DB::raw("paths as master"))
                            ->selectRaw("master.descendant_id")
                            ->whereIn(
                                "ancestor_id",
                                Path::query()->from(\DB::raw("paths as lvl1"))
                                    ->selectRaw("descendant_id")
                                    ->whereColumn("lvl1.ancestor_id", "<>", "lvl1.descendant_id")
                                    ->whereColumn("lvl1.ancestor_id", "=", "paths.ancestor_id")
                                    ->where("tree_depth", 1)
                                    ->whereNotIn(
                                        "lvl1.descendant_id",
                                        Path::query()->from(\DB::raw("paths as lvl11"))
                                            ->select("lvl11.ancestor_id")
                                            ->whereColumn("lvl11.descendant_id", "=", "paths.descendant_id")
                                    )


                            )

                    )
                    ->limit(1)
            ])
            ->with("depositor")
            ->get();

        foreach ($parents as $parent) {
            if ($parent->depositor === null) {
                continue;
            }

            Bonus::query()->create([
                "type" => Bonus::TYPE_UPLINK,
                "user_id" => $parent->ancestor_id,
                "amount" => $user->deposit_amount * 5.0 / 100,
            ]);

            // Kaki sisi user yang melakukan deposit sekarang
            UsedUplinkBonus::query()->create([
                "uplink_id" => $parent->ancestor_id,
                "user_id" => $user->id,
            ]);

            // Kaki sisi lain
            UsedUplinkBonus::query()->create([
                "uplink_id" => $parent->ancestor_id,
                "user_id" => $parent->depositor_id,
            ]);
        }

        DB::commit();

        return back();
    }
}
