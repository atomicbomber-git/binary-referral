<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
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
            "deposit_amount" => User::DEPOSIT_TYPES[$data["deposit_type"]]
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

        DB::commit();

        return back();
    }
}
