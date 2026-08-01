<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmploymentType;
use App\Models\FacilityType;
use App\Models\JobCategory;
use App\Models\JobFeature;
use App\Models\Qualification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * マスタ管理(運営者。SPEC.md 11章)。
 *
 * マスタの内容(`code` / `name` 等)は製品がシーダーで配布する。運営者が
 * ここで変更できるのは `is_enabled`(有効/無効)と `sort_order`(並び順)だけ
 * (CLAUDE.md 3.7)。バージョンアップのたびにシーダーを再実行してもこの 2 つは
 * 上書きされない設計と対になっている。
 *
 * 5 種のマスタは列構成が微妙に異なる(category / short_name の有無)だけで
 * 操作(有効/無効の切替・並び替え)は共通のため、1 つのコントローラで扱う。
 */
class MasterController extends Controller
{
    /**
     * @var array<string, array{model: class-string, label: string, has_category: bool}>
     */
    private const TYPES = [
        'qualifications' => ['model' => Qualification::class, 'label' => '保有資格', 'has_category' => true],
        'facility-types' => ['model' => FacilityType::class, 'label' => '施設形態', 'has_category' => true],
        'job-categories' => ['model' => JobCategory::class, 'label' => '職種', 'has_category' => false],
        'employment-types' => ['model' => EmploymentType::class, 'label' => '雇用形態', 'has_category' => false],
        'job-features' => ['model' => JobFeature::class, 'label' => 'こだわり条件', 'has_category' => true],
    ];

    public function home(): View
    {
        return view('admin.masters.home', [
            'types' => collect(self::TYPES)->map(fn ($config, $slug) => [
                'slug' => $slug,
                'label' => $config['label'],
                'count' => $config['model']::count(),
            ]),
        ]);
    }

    public function index(string $type): View
    {
        $config = $this->config($type);

        return view('admin.masters.index', [
            'type' => $type,
            'label' => $config['label'],
            'hasCategory' => $config['has_category'],
            'rows' => $config['model']::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function toggle(string $type, int $id): RedirectResponse
    {
        $config = $this->config($type);
        $row = $config['model']::findOrFail($id);

        $row->update(['is_enabled' => ! $row->is_enabled]);

        AuditLog::record('admin', auth('admin')->id(), 'masters.toggle', $row, request()->ip());

        return redirect()->route('admin.masters.index', $type)->with('status', '有効/無効を切り替えました。');
    }

    /**
     * 並び替え。フロントから並び替え後の ID 配列を受け取り、10 刻みの sort_order を振り直す。
     */
    public function reorder(Request $request, string $type): RedirectResponse
    {
        $config = $this->config($type);
        $ids = array_map('intval', (array) $request->input('ids', []));

        // 実在する ID 以外が混ざっていても無視する
        $validIds = $config['model']::whereIn('id', $ids)->pluck('id')->all();

        $order = 0;
        foreach ($ids as $id) {
            if (! in_array($id, $validIds, true)) {
                continue;
            }

            $order += 10;
            $config['model']::whereKey($id)->update(['sort_order' => $order]);
        }

        AuditLog::record('admin', auth('admin')->id(), 'masters.reorder', null, request()->ip());

        return redirect()->route('admin.masters.index', $type)->with('status', '並び順を更新しました。');
    }

    /**
     * @return array{model: class-string, label: string, has_category: bool}
     */
    private function config(string $type): array
    {
        return self::TYPES[$type] ?? throw new NotFoundHttpException;
    }
}
