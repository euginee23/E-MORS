<?php

namespace Database\Factories;

use App\Enums\AdminStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Admin,
            // Mirror the table defaults explicitly. Column defaults are applied by the
            // database, so they never reach the in-memory model a test acts as — leaving
            // these unset makes EnsureAdminIsVerified read a null is_active and log the
            // admin straight back out.
            'status' => AdminStatus::Verified,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * An admin still awaiting Super Admin review.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatus::Pending,
            'is_active' => true,
        ]);
    }

    /**
     * An admin whose registration the Super Admin turned down.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatus::Rejected,
            'rejection_reason' => 'Credentials could not be verified.',
        ]);
    }

    /**
     * A verified admin the Super Admin has since deactivated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatus::Verified,
            'is_active' => false,
        ]);
    }
}
