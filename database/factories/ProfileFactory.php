<?php

namespace Database\Factories;

use App\Enums\ProfilStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'first_name' => fake()->name(),
            'image_path' => 'profiles/' . Str::uuid() . '.jpg',
            'status' => $this->faker->randomElement([
                ProfilStatus::ACTIVE->value,
                ProfilStatus::INACTIVE->value,
                ProfilStatus::PENDING->value
            ]),
            'created_by' => User::factory(),
        ];
    }
}
