<?php

namespace Database\Factories;

use App\Models\JobSeeker;
use App\Models\JobSeekerExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobSeekerExperience>
 */
class JobSeekerExperienceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_seeker_id' => JobSeeker::factory(),
            'organization_name' => fake('ja_JP')->company(),
            'job_title' => '介護職員',
            'started_on' => '2020-04-01',
        ];
    }
}
