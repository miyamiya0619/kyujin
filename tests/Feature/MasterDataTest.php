<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\EmploymentType;
use App\Models\FacilityType;
use App\Models\JobCategory;
use App\Models\JobFeature;
use App\Models\Prefecture;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_全マスタが仕様どおりの件数で投入される(): void
    {
        $this->seed();

        // SPEC.md 11章 の一覧と一致すること
        $this->assertSame(47, Prefecture::count(), '都道府県は 47 件');
        $this->assertSame(22, Qualification::count(), '保有資格は 22 件');
        $this->assertSame(27, FacilityType::count(), '施設形態は 27 件');
        $this->assertSame(12, JobCategory::count(), '職種は 12 件');
        $this->assertSame(6, EmploymentType::count(), '雇用形態は 6 件');
        $this->assertSame(20, JobFeature::count(), 'こだわり条件は 20 件');

        // 市区町村は主要 74 件のみ。全件は masters:import-cities で取り込む
        $this->assertSame(74, City::count(), '主要市区町村は 74 件');
    }

    public function test_介護領域の主要マスタが漏れなく入っている(): void
    {
        $this->seed();

        // 介護求人で必須になる資格
        foreach (['介護福祉士', '介護職員初任者研修', '介護支援専門員(ケアマネジャー)', '看護師'] as $name) {
            $this->assertTrue(
                Qualification::where('name', $name)->exists(),
                "資格「{$name}」が投入されていない"
            );
        }

        // 介護求人で必須になる施設形態
        foreach (['特別養護老人ホーム', 'デイサービス', '訪問介護', 'グループホーム'] as $name) {
            $this->assertTrue(
                FacilityType::where('name', $name)->exists(),
                "施設形態「{$name}」が投入されていない"
            );
        }

        // 略称は検索と表示の双方で使う
        $this->assertSame('特養', FacilityType::where('code', 'tokuyo')->first()->displayName());
        $this->assertSame('介護医療院', FacilityType::where('code', 'kaigo_iryoin')->first()->displayName());
    }

    public function test_都道府県が全地方をカバーしている(): void
    {
        $this->seed();

        $regions = Prefecture::distinct()->pluck('region')->sort()->values()->all();

        $this->assertEqualsCanonicalizing(
            ['北海道', '東北', '関東', '中部', '近畿', '中国', '四国', '九州・沖縄'],
            $regions
        );
    }

    public function test_無効にしたマスタは選択肢に出てこない(): void
    {
        $this->seed();

        $qualification = Qualification::where('code', 'kaigo_fukushishi')->firstOrFail();
        $qualification->update(['is_enabled' => false]);

        $selectableCodes = Qualification::selectable()->pluck('code');

        $this->assertNotContains('kaigo_fukushishi', $selectableCodes);
        $this->assertContains('kangoshi', $selectableCodes, '他のマスタは影響を受けない');
    }

    public function test_選択肢は顧客が指定した並び順で返る(): void
    {
        $this->seed();

        // 「看護師」を先頭に移動する
        Qualification::where('code', 'kangoshi')->update(['sort_order' => 0]);

        $this->assertSame('kangoshi', Qualification::selectable()->first()->code);
    }

    /**
     * バージョンアップのたびに全顧客の環境でシーダーを再実行する。
     * ここが壊れると、更新のたびに全顧客の設定が消える。
     */
    public function test_シーダーを再実行しても行が重複しない(): void
    {
        $this->seed();
        $before = Qualification::count();

        $this->seed();

        $this->assertSame($before, Qualification::count());
        $this->assertSame(47, Prefecture::count());
        $this->assertSame(74, City::count());
    }

    public function test_シーダーを再実行しても顧客が調整した設定が消えない(): void
    {
        $this->seed();

        // 顧客が「保育士は扱わない」「看護師を先頭にしたい」と調整した状態
        Qualification::where('code', 'hoikushi')->update(['is_enabled' => false]);
        Qualification::where('code', 'kangoshi')->update(['sort_order' => 5]);
        FacilityType::where('code', 'hoikuen')->update(['is_enabled' => false]);
        Prefecture::where('code', '47')->update(['is_enabled' => false]);

        $this->seed();

        $this->assertFalse(
            Qualification::where('code', 'hoikushi')->first()->is_enabled,
            '顧客が無効にした資格が復活してはいけない'
        );
        $this->assertSame(
            5,
            Qualification::where('code', 'kangoshi')->first()->sort_order,
            '顧客が変更した並び順が戻ってはいけない'
        );
        $this->assertFalse(FacilityType::where('code', 'hoikuen')->first()->is_enabled);
        $this->assertFalse(Prefecture::where('code', '47')->first()->is_enabled);
    }

    public function test_シーダーを再実行すると製品が管理する項目は最新に戻る(): void
    {
        $this->seed();

        // 名称・カテゴリは製品が管理する。手で書き換えられていても更新で戻す。
        Qualification::where('code', 'kaigo_fukushishi')
            ->update(['name' => '書き換えられた名称', 'category' => '不明']);

        $this->seed();

        $qualification = Qualification::where('code', 'kaigo_fukushishi')->first();

        $this->assertSame('介護福祉士', $qualification->name);
        $this->assertSame('介護', $qualification->category);
    }

    public function test_市区町村は都道府県に正しく紐づく(): void
    {
        $this->seed();

        $shinjuku = City::where('code', '13104')->firstOrFail();

        $this->assertSame('新宿区', $shinjuku->name);
        $this->assertSame('東京都', $shinjuku->prefecture->name);

        $tokyo = Prefecture::where('code', '13')->firstOrFail();

        $this->assertSame(23, $tokyo->cities()->count(), '東京都は 23 区が入っている');
    }
}
