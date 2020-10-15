<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'deposit_amount' => $this->faker->optional(0.75)->passthrough(
                $this->faker->randomElement(array_values(User::DEPOSIT_TYPES))
            )
        ];
    }

    public function admin()
    {
        return $this->state([
           "level" => User::LEVEL_ADMIN,
        ]);
    }

    public function regular()
    {
        return $this->state([
            "level" => User::LEVEL_REGULAR,
        ]);
    }

    public function root()
    {
        return $this->state([
            "is_root" => 1,
        ]);
    }
}
