<?php

namespace Database\Factories;

use App\Models\Prefecture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prefecture>
 */
class PrefectureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('##'),
            'name' => 'テスト県'.$this->faker->unique()->numberBetween(1, 999),
            'name_kana' => 'てすとけん',
            'region' => '関東',
        ];
    }
}
