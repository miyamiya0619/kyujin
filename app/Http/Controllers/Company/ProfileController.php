<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\ProfileRequest;
use App\Models\City;
use App\Models\Company;
use App\Models\Prefecture;
use App\Services\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 掲載企業が自社の情報を編集する。
 *
 * **URL に企業 ID を含めない。** 対象は常にログイン中の担当者が所属する企業。
 * ID を受け取らなければ、他社の ID を指定して覗く余地が構造的に無くなる。
 */
class ProfileController extends Controller
{
    public function __construct(private readonly ImageUploadService $images) {}

    public function edit(): View
    {
        $company = $this->company();

        return view('company.profile.edit', [
            'company' => $company,
            'prefectures' => Prefecture::selectable()->get(),
            'cities' => $company->prefecture_id
                ? City::selectable()->where('prefecture_id', $company->prefecture_id)->get()
                : collect(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $company = $this->company();

        $company->fill($request->safe()->except('logo'));

        if ($request->hasFile('logo')) {
            $company->logo_path = $this->images->store(
                $request->file('logo'),
                'companies/logos',
                replacing: $company->logo_path,
            );
        }

        $company->save();

        return redirect()
            ->route('company.profile.edit')
            ->with('status', '企業情報を更新しました。');
    }

    /**
     * ログイン中の担当者が所属する企業。ここ以外から企業を取得しないこと。
     */
    private function company(): Company
    {
        return auth('company')->user()->company;
    }
}
