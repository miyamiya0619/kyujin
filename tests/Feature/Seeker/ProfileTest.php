<?php

namespace Tests\Feature\Seeker;

use App\Models\City;
use App\Models\JobSeeker;
use App\Models\Prefecture;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_プロフィールを更新できる(): void
    {
        $jobSeeker = JobSeeker::factory()->create(['name' => '編集前']);

        $this->actingAs($jobSeeker, 'seeker')
            ->put(route('seeker.profile.update'), ['name' => '編集後'])
            ->assertRedirect(route('seeker.profile.edit'));

        $this->assertSame('編集後', $jobSeeker->fresh()->name);

        // リダイレクト先で更新完了のメッセージが実際に画面に見えることを確認する。
        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.profile.edit'))
            ->assertSee('プロフィールを更新しました。');
    }

    public function test_保有資格を複数選択して保存できる(): void
    {
        $this->seed();

        $jobSeeker = JobSeeker::factory()->create();
        $qualifications = Qualification::selectable()->take(2)->pluck('id');

        $this->actingAs($jobSeeker, 'seeker')->put(route('seeker.profile.update'), [
            'name' => $jobSeeker->name,
            'qualification_ids' => $qualifications->all(),
        ]);

        $this->assertSame(
            $qualifications->sort()->values()->all(),
            $jobSeeker->qualifications->pluck('id')->sort()->values()->all()
        );
    }

    public function test_市区町村が都道府県と一致しないと更新できない(): void
    {
        $this->seed();

        $jobSeeker = JobSeeker::factory()->create();
        $tokyo = Prefecture::where('code', '13')->firstOrFail();
        $osakaCity = City::where('code', '27100')->firstOrFail();

        $this->actingAs($jobSeeker, 'seeker')->put(route('seeker.profile.update'), [
            'name' => $jobSeeker->name,
            'prefecture_id' => $tokyo->id,
            'city_id' => $osakaCity->id,
        ])->assertSessionHasErrors('city_id');
    }

    public function test_他人のプロフィールは編集できない(): void
    {
        $mine = JobSeeker::factory()->create(['name' => '自分の名前']);
        $other = JobSeeker::factory()->create(['name' => '他人の名前']);

        $this->actingAs($mine, 'seeker')->put(route('seeker.profile.update'), ['name' => '書き換え']);

        $this->assertSame('書き換え', $mine->fresh()->name);
        $this->assertSame('他人の名前', $other->fresh()->name, '他人には影響しない');
    }

    public function test_職務経歴を追加できる(): void
    {
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')->post(route('seeker.experiences.store'), [
            'organization_name' => 'さくら介護サービス',
            'job_title' => '介護職員',
        ])->assertRedirect(route('seeker.profile.edit'));

        $this->assertDatabaseHas('job_seeker_experiences', [
            'job_seeker_id' => $jobSeeker->id,
            'organization_name' => 'さくら介護サービス',
        ]);
    }

    public function test_他人の職務経歴は編集できない(): void
    {
        $mine = JobSeeker::factory()->create();
        $other = JobSeeker::factory()->create();
        $otherExperience = $other->experiences()->create(['organization_name' => '他人の経歴']);

        $this->actingAs($mine, 'seeker')
            ->put(route('seeker.experiences.update', $otherExperience), ['organization_name' => '書き換え'])
            ->assertNotFound();

        $this->assertSame('他人の経歴', $otherExperience->fresh()->organization_name);
    }

    public function test_他人の職務経歴は削除できない(): void
    {
        $mine = JobSeeker::factory()->create();
        $other = JobSeeker::factory()->create();
        $otherExperience = $other->experiences()->create(['organization_name' => '他人の経歴']);

        $this->actingAs($mine, 'seeker')
            ->delete(route('seeker.experiences.destroy', $otherExperience))
            ->assertNotFound();

        $this->assertModelExists($otherExperience);
    }

    public function test_自分の職務経歴は削除できる(): void
    {
        $jobSeeker = JobSeeker::factory()->create();
        $experience = $jobSeeker->experiences()->create(['organization_name' => '削除対象']);

        $this->actingAs($jobSeeker, 'seeker')
            ->delete(route('seeker.experiences.destroy', $experience))
            ->assertRedirect(route('seeker.profile.edit'));

        $this->assertModelMissing($experience);
    }

    public function test_職務経歴を並び替えられる(): void
    {
        $jobSeeker = JobSeeker::factory()->create();
        $first = $jobSeeker->experiences()->create(['organization_name' => '1件目', 'sort_order' => 10]);
        $second = $jobSeeker->experiences()->create(['organization_name' => '2件目', 'sort_order' => 20]);

        $this->actingAs($jobSeeker, 'seeker')->post(route('seeker.experiences.reorder'), [
            'experience_ids' => [$second->id, $first->id],
        ]);

        $this->assertTrue(
            $second->fresh()->sort_order < $first->fresh()->sort_order,
            '2件目が先頭に来ていること'
        );
    }

    public function test_並び替えに他人の経歴_i_dを混ぜても他人のsort_orderは変わらない(): void
    {
        $mine = JobSeeker::factory()->create();
        $other = JobSeeker::factory()->create();
        $mine->experiences()->create(['organization_name' => '自分の経歴', 'sort_order' => 10]);
        $otherExperience = $other->experiences()->create(['organization_name' => '他人の経歴', 'sort_order' => 10]);

        $this->actingAs($mine, 'seeker')->post(route('seeker.experiences.reorder'), [
            'experience_ids' => [$otherExperience->id],
        ]);

        $this->assertSame(10, $otherExperience->fresh()->sort_order, '他人の経歴の並び順は変わらない');
    }
}
