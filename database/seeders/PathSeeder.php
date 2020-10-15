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

        $root = User::query()
            ->where("is_root", 1)
            ->first();

        Collection::times(1000, function () use ($root) {
            $parent = $root->nextEligibleDescendant();

            $child = User::query()
                ->where("id", "<>", $parent->id)
                ->whereDoesntHave("ancestor_refs")
                ->whereDoesntHave("descendant_refs")
                ->first();

            if ($child === null) {
                return;
            }

            User::attachDirectly(
                $parent->id,
                $child->id,
            );
        });

        DB::commit();
    }
}
