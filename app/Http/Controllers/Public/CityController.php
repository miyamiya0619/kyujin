<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Prefecture;
use Illuminate\Http\JsonResponse;

/**
 * 都道府県に紐づく市区町村の一覧(JSON)。
 *
 * 都道府県セレクトを変更した瞬間に市区町村の選択肢を更新するための補助エンドポイント
 * (`x-prefecture-city-select-script` から fetch する)。認証不要。
 * 個人情報を含まない配布マスタの参照であり、公開求人検索フォームでも
 * 同様に無認証で都道府県・市区町村を扱っているため、ここも認証を求めない。
 */
class CityController extends Controller
{
    public function byPrefecture(Prefecture $prefecture): JsonResponse
    {
        return response()->json(
            City::selectable()->where('prefecture_id', $prefecture->id)->get(['id', 'name'])
        );
    }
}
