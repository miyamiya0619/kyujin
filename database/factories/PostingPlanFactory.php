<?php

namespace Database\Factories;

use App\Models\PostingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostingPlan>
 */
class PostingPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'スタンダード',
            'is_enabled' => true,
        ];
    }
}
