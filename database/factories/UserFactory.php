<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'firstname'          => fake()->firstName(),
            'lastname'           => fake()->lastName(),
            'username'           => fake()->unique()->userName(),
            'password'           => 'password', // model's hashed cast bcrypts this automatically
            'remember_token'     => Str::random(10),
            'project_permission' => [],
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            // No email_verified_at attribute in this application
        ]);
    }
}
