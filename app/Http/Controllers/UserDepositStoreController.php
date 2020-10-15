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
                "depositor_id" =>
                    User::query()
                        ->select("id")
                        ->whereNotNull("deposit_amount")
                        ->orderBy("deposited_at")
                        ->whereNotIn("id",
                            UsedUplinkBonus::query()
                                ->select("used_uplink_bonuses.id")
                                ->whereColumn("used_uplink_bonuses.uplink_id", "paths.ancestor_id")
                        )
                        ->whereIn(
                            "id",
                            Path::query()->from(DB::raw("paths sub_1"))
                                ->select("sub_1.descendant_id")
                                ->whereColumn("sub_1.ancestor_id", "<>", "sub_1.descendant_id")
                                ->whereIn(
                                    "sub_1.ancestor_id",
                                    Path::query()->from(DB::raw("paths sub_2"))
                                        ->selectRaw("sub_2.descendant_id")
                                        ->whereColumn("sub_2.ancestor_id", "paths.ancestor_id")
                                        ->whereNotIn("sub_2.descendant_id",
                                            Path::query()->from(DB::raw("paths sub_3"))
                                                ->select("sub_3.ancestor_id")
                                                ->whereColumn("sub_3.descendant_id", "paths.descendant_id")
                                        )
                                        ->where("sub_2.tree_depth", 1)
                                )
                        )
                        ->limit(1),
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
                "amount" => $user->deposited_amount * 5.0 / 100,
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
