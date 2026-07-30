<?php

namespace Database\Factories;

use App\Models\JobSeeker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobSeeker>
 */
class JobSeekerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ja_JP')->name(),
            'name_kana' => 'てすとたろう',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'tel' => fake('ja_JP')->phoneNumber(),
            'remember_token' => Str::random(10),
        ];
    }
}
