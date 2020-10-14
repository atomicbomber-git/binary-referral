<?php

namespace Database\Seeders;

use App\Models\Path;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PathSeeder extends Seeder
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

        Path::query()->create([
            "ancestor_id" => $root->id,
            "descendant_id" => $root->id,
            "tree_depth" => 0,
        ]);

        Collection::times(200, function () use ($root) {
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
