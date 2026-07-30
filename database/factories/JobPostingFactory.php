<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Workplace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'workplace_id' => Workplace::factory(),
            'title' => '介護職員(正社員・未経験歓迎)',
            'status' => JobPosting::STATUS_DRAFT,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => JobPosting::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
