<?php

namespace Database\Seeders;

use App\Models\ReferralPath;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReferralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        /** @var User $root */
        $root = User::query()->first();

        ReferralPath::query()->create([
            "ancestor_id" => $root->id,
            "descendant_id" => $root->id,
            "tree_depth" => 0,
        ]);

        Collection::times(50, function () use ($root) {
            $parent = $root->nextEligibleDescendant();

            $child = User::query()
                ->where("id", "<>", $parent->id)
                ->whereDoesntHave("ancestor_refs")
                ->whereDoesntHave("descendant_refs")
                ->first();

            User::attachDirectly(
                $parent->id,
                $child->id,
            );
        });

        DB::commit();
    }
}
