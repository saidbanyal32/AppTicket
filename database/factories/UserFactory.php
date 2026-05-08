<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Master\RefJabatan;
use App\Models\Master\RefUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        $unit = RefUnit::query()->firstOrCreate(['code' => 'HO'], ['name' => 'Head Office', 'is_active' => true]);
        $jabatan = RefJabatan::query()->firstOrCreate(['code' => 'ADM'], ['name' => 'Administrator', 'level' => 1, 'is_active' => true]);

        return [
            'unit_id' => $unit->id,
            'jabatan_id' => $jabatan->id,
            'employee_no' => fake()->unique()->bothify('EMP-####'),
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
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
}
