<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Workplace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workplace>
 */
class WorkplaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake('ja_JP')->city().'デイサービスセンター',
        ];
    }
}
