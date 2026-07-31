<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory()->published(),
            'job_seeker_id' => JobSeeker::factory(),
            // company_id は job_posting から決まる非正規化カラム(applications マイグレーション参照)。
            'company_id' => fn (array $attributes) => JobPosting::find($attributes['job_posting_id'])->company_id,
            'status' => Application::STATUS_NEW,
            'referrer_source' => 'direct',
            'applied_at' => now(),
        ];
    }
}
