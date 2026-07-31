<?php

namespace Tests\Feature\Public;

use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_トップページが表示される(): void
    {
        $this->get(route('public.top'))->assertOk();
    }

    public function test_上位表示の求人がおすすめ求人として表示される(): void
    {
        JobPosting::factory()->published()->create(['title' => 'おすすめ求人', 'is_featured' => true]);
        JobPosting::factory()->published()->create(['title' => '通常求人', 'is_featured' => false]);

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('おすすめ求人')
            ->assertSee('通常求人'); // 新着求人としては両方出る
    }

    public function test_審査待ちの求人はトップページに出ない(): void
    {
        JobPosting::factory()->create(['title' => '審査待ち求人', 'status' => JobPosting::STATUS_PENDING]);

        $this->get(route('public.top'))->assertDontSee('審査待ち求人');
    }

    public function test_下書きの求人はトップページに出ない(): void
    {
        JobPosting::factory()->create(['title' => '下書き求人', 'status' => JobPosting::STATUS_DRAFT]);

        $this->get(route('public.top'))->assertDontSee('下書き求人');
    }
}
