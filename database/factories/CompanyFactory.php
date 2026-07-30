<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ja_JP')->company().'介護サービス',
            'name_kana' => 'かいごさーびす',
            'tel' => fake('ja_JP')->phoneNumber(),
            'status' => Company::STATUS_ACTIVE,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Company::STATUS_SUSPENDED]);
    }
}
