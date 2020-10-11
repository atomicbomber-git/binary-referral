<?php

namespace Database\Seeders;

use App\Models\ReferralPath;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);

        ReferralPath::query()->create([
            "ancestor_id" => 1,
            "descendant_id" => 1,
            "tree_depth" => 0,
        ]);

        Collection::times(20, function () {
            $parent = User::query()
                ->whereHas("ancestor_refs")
                ->where(function (Builder $query) {
                    $query
                        ->whereHas("descendant_refs", function ($query) {
                            $query->where("tree_depth", 1);
                        }, ">=", 0)
                        ->whereHas("descendant_refs", function ($query) {
                            $query->where("tree_depth", 1);
                        }, "<", 2);
                })
                ->inRandomOrder()
                ->first();

            if ($parent === null) {
                return;
            }

            $child = User::query()
                ->where("id", "<>", $parent->id)
                ->whereDoesntHave("ancestor_refs")
                ->whereDoesntHave("descendant_refs")
                ->inRandomOrder()
                ->first();

            DB::insert(
                "
                    INSERT INTO referral_paths (ancestor_id, descendant_id, tree_depth, created_at, updated_at) (
                        SELECT ancestor_id, ?, tree_depth + 1, NOW(), NOW() FROM referral_paths WHERE descendant_id = ?
                    )
                ",
                [
                    $child->id,
                    $parent->id,
                ]);

            ReferralPath::query()->create([
                "ancestor_id" => $child->id,
                "descendant_id" => $child->id,
                "tree_depth" => 0,
            ]);
        });

//        SELECT ancestor_id, GROUP_CONCAT(descendant_id) FROM referral_paths
//    WHERE tree_depth = 1 AND ancestor_id <> descendant_id
//    GROUP BY ancestor_id
//    ORDER BY ancestor_id;
//
//SELECT ancestor_id, descendant_id
//       ,(
//    SELECT COUNT(*) < 2 FROM referral_paths rp WHERE rp.ancestor_id = referral_paths.descendant_id
//    AND rp.tree_depth = 1
//           ) AS children
//       FROM referral_paths
//    WHERE ancestor_id = 1
    }
}
