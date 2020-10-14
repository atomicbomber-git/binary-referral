<?php

namespace Database\Seeders;

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

        UserFactory::new()
            ->regular()
            ->create([
                "email" => "regular@regular.com",
                "password" => Hash::make("regular@regular.com"),
            ]);

        UserFactory::new()
            ->regular()
            ->count(1000)
            ->create();

        DB::commit();
    }
}
