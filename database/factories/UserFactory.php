<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'mobile' => '09'.fake()->unique()->numerify('#########'),
            'password' => Hash::make('password'),
            'card_number' => fake()->numerify('################'),
            'card_hash' => hash('sha256', Str::uuid()->toString()),
            'card_last4' => fake()->numerify('####'),
            'invite_code' => strtoupper(fake()->unique()->bothify('????####')),
            'is_active' => true,
            'mobile_verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_verified_at' => null,
        ]);
    }
}
