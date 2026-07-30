<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CompanyUser>
 */
class CompanyUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake('ja_JP')->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => CompanyUser::ROLE_MEMBER,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => CompanyUser::ROLE_OWNER]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
