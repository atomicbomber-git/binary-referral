<?php

namespace Database\Seeders;

use App\Models\Path;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        // Create root user
        $root_user = UserFactory::new()
            ->regular()
            ->root()
            ->create();

        Path::query()->create([
            "ancestor_id" => $root_user->id,
            "descendant_id" => $root_user->id,
            "tree_depth" => 0,
        ]);

        // Create regular users
        UserFactory::new()
            ->regular()
            ->create([
                "email" => "regular@regular.com",
                "password" => Hash::make("regular@regular.com"),
                'deposit_amount' => null,
                'deposited_at' => null,
            ]);

        UserFactory::new()
            ->regular()
            ->count(31)
            ->create([
                'deposit_amount' => null,
                'deposited_at' => null,
            ]);

        DB::commit();
    }
}
