<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyRequest;
use App\Models\City;
use App\Models\Company;
use App\Models\Prefecture;
use App\Services\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 掲載企業の管理(運営者)。
 *
 * 運営者は全ての掲載企業を扱える。掲載企業の担当者は自社しか扱えない
 * (Company\ProfileController を参照)。
 */
class CompanyController extends Controller
{
    public function __construct(private readonly ImageUploadService $images) {}

    public function index(Request $request): View
    {
        $companies = Company::query()
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->string('keyword')->toString();
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('name_kana', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->withCount('users')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.companies.index', [
            'companies' => $companies,
            'keyword' => $request->string('keyword')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.companies.create', [
            'company' => new Company(['status' => Company::STATUS_ACTIVE]),
            ...$this->masterOptions(),
        ]);
    }

    public function store(CompanyRequest $request): RedirectResponse
    {
        $company = new Company($request->safe()->except('logo'));

        if ($request->hasFile('logo')) {
            $company->logo_path = $this->images->store($request->file('logo'), 'companies/logos');
        }

        $company->save();

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', "掲載企業「{$company->name}」を登録しました。続けて担当者を追加してください。");
    }

    public function show(Company $company): View
    {
        return view('admin.companies.show', [
            'company' => $company->load('prefecture', 'city'),
            'users' => $company->users()->orderBy('id')->get(),
        ]);
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', [
            'company' => $company,
            ...$this->masterOptions($company->prefecture_id),
        ]);
    }

    public function update(CompanyRequest $request, Company $company): RedirectResponse
    {
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
            ->route('admin.companies.show', $company)
            ->with('status', '企業情報を更新しました。');
    }

    /**
     * 選択肢は必ず selectable() を通す。
     * 運営者が無効にしたマスタを出さないため(CLAUDE.md 3.7)。
     *
     * @return array<string, mixed>
     */
    private function masterOptions(?int $prefectureId = null): array
    {
        return [
            'prefectures' => Prefecture::selectable()->get(),
            'cities' => $prefectureId
                ? City::selectable()->where('prefecture_id', $prefectureId)->get()
                : collect(),
        ];
    }
}
