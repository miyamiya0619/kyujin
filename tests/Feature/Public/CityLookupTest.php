<?php

namespace Tests\Feature\Public;

use App\Models\City;
use App\Models\Prefecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 都道府県セレクトの補助 API(市区町村の動的読み込み)。
 */
class CityLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_都道府県に紐づく市区町村が_jso_nで返る(): void
    {
        $this->seed();

        $tokyo = Prefecture::where('code', '13')->firstOrFail();

        $response = $this->getJson(route('cities.by-prefecture', $tokyo));

        $response->assertOk();
        $this->assertGreaterThan(0, count($response->json()));
        $this->assertSame(
            City::where('prefecture_id', $tokyo->id)->where('is_enabled', true)->orderBy('sort_order')->orderBy('id')->pluck('name')->all(),
            collect($response->json())->pluck('name')->all()
        );
    }

    public function test_無効化された市区町村は含まれない(): void
    {
        $this->seed();

        $tokyo = Prefecture::where('code', '13')->firstOrFail();
        $city = City::where('prefecture_id', $tokyo->id)->firstOrFail();
        $city->update(['is_enabled' => false]);

        $response = $this->getJson(route('cities.by-prefecture', $tokyo));

        $this->assertNotContains($city->id, collect($response->json())->pluck('id')->all());
    }
}
