<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\WorkplaceRequest;
use App\Models\City;
use App\Models\Company;
use App\Models\FacilityType;
use App\Models\Prefecture;
use App\Models\Workplace;
use App\Services\ImageUploadService;
use App\Services\PostingPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 事業所 CRUD の共通処理。
 *
 * 運営者(Admin\WorkplaceController、URL が `{company}/workplaces/{workplace}`)と
 * 掲載企業(Company\WorkplaceController、URL が `workplaces/{workplace}` のみ。CLAUDE.md 3.8)
 * の双方で使う。
 *
 * ⚠ **ここに書くのは実処理(`do*` メソッド)だけにすること。**
 * Laravel の暗黙のルートモデルバインディングは、型が一致する引数を
 * 「ルートパラメータの URL 登録順」で位置合わせして渡す。
 * 運営者側は `{company}/{workplace}`、掲載企業側は `{workplace}` のみと
 * ルートパラメータの数が違うため、**公開アクション(index/edit など)は
 * 各コントローラ側で route の並びに合わせて定義し、ここには委譲するだけ**にする。
 * トレイト側で `edit(Workplace $workplace, ?Company $company)` のような
 * 固定シグネチャを公開アクションとして直接ルートに晒すと、
 * 引数の型が両方とも一致するために暗黙バインディングが位置ズレを起こし、
 * 実行時に TypeError になる(実際にこの実装で発生した)。
 */
trait ManagesWorkplaces
{
    private ImageUploadService $images;

    /**
     * 操作対象の企業を決める。
     * 運営者側は $routeCompany(URL のパラメータ)をそのまま返す。
     * 掲載企業側は $routeCompany を無視し、常にログイン中の担当者の所属企業を返す。
     */
    abstract protected function targetCompany(?Company $routeCompany = null): Company;

    abstract protected function viewPrefix(): string;

    abstract protected function redirectRoute(Company $company): string;

    protected function doIndex(?Company $routeCompany): View
    {
        $company = $this->targetCompany($routeCompany);

        return view("{$this->viewPrefix()}.index", [
            'company' => $company,
            'workplaces' => $company->workplaces()->with('facilityType', 'prefecture', 'city')->orderBy('id')->get(),
        ]);
    }

    protected function doCreate(?Company $routeCompany): View
    {
        $company = $this->targetCompany($routeCompany);

        return view("{$this->viewPrefix()}.create", [
            'company' => $company,
            'workplace' => new Workplace,
            ...$this->masterOptions(),
        ]);
    }

    protected function doStore(WorkplaceRequest $request, ?Company $routeCompany): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);

        if (! app(PostingPlanLimitService::class)->canAddWorkplace($company)) {
            throw ValidationException::withMessages([
                'plan' => '掲載プランの事業所登録数の上限に達しているため、これ以上登録できません。',
            ]);
        }

        $workplace = new Workplace($request->safe()->except('photo'));
        $workplace->company_id = $company->id;

        if ($request->hasFile('photo')) {
            $workplace->photo_path = $this->images->store($request->file('photo'), 'workplaces/photos');
        }

        $workplace->save();

        return redirect($this->redirectRoute($company))->with('status', "事業所「{$workplace->name}」を登録しました。");
    }

    protected function doEdit(?Company $routeCompany, Workplace $workplace): View
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $workplace);

        return view("{$this->viewPrefix()}.edit", [
            'company' => $company,
            'workplace' => $workplace,
            ...$this->masterOptions($workplace->prefecture_id),
        ]);
    }

    protected function doUpdate(WorkplaceRequest $request, ?Company $routeCompany, Workplace $workplace): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $workplace);

        $workplace->fill($request->safe()->except('photo'));

        if ($request->hasFile('photo')) {
            $workplace->photo_path = $this->images->store(
                $request->file('photo'),
                'workplaces/photos',
                replacing: $workplace->photo_path,
            );
        }

        $workplace->save();

        return redirect($this->redirectRoute($company))->with('status', '事業所情報を更新しました。');
    }

    protected function doDestroy(?Company $routeCompany, Workplace $workplace): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $workplace);

        // 求人が紐づく事業所は削除できない。掲載中の求人が孤立するのを防ぐ。
        // job_postings は T-07 で作成する(Workplace::hasJobPostings を参照)。
        if ($workplace->hasJobPostings()) {
            return redirect($this->redirectRoute($company))
                ->with('error', 'この事業所には求人が登録されているため削除できません。');
        }

        $this->images->delete($workplace->photo_path);
        $workplace->delete();

        return redirect($this->redirectRoute($company))->with('status', "事業所「{$workplace->name}」を削除しました。");
    }

    /**
     * URL を組み替えて他社の事業所を操作されるのを防ぐ。
     * 403 ではなく 404 を返す(存在自体を知らせない)。
     */
    private function ensureBelongsTo(Company $company, Workplace $workplace): void
    {
        if ($workplace->company_id !== $company->id) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * 選択肢は必ず selectable() を通す(CLAUDE.md 3.7)。
     *
     * @return array<string, mixed>
     */
    private function masterOptions(?int $prefectureId = null): array
    {
        return [
            'facilityTypes' => FacilityType::selectable()->get(),
            'prefectures' => Prefecture::selectable()->get(),
            'cities' => $prefectureId
                ? City::selectable()->where('prefecture_id', $prefectureId)->get()
                : collect(),
        ];
    }
}
