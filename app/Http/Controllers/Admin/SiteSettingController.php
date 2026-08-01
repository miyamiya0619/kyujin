<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use App\Services\ImageUploadService;
use App\Support\Theme\ThemeManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * サイト設定(運営者。SPEC.md 5.3)。
 *
 * 対象は常に `SiteSetting::current()` の 1 行だけなので、URL に ID を含めない
 * (単数リソース。CLAUDE.md 3.8 と同じ考え方)。
 */
class SiteSettingController extends Controller
{
    public function __construct(private readonly ImageUploadService $images) {}

    public function edit(ThemeManager $themes): View
    {
        return view('admin.site-settings.edit', [
            'siteSetting' => SiteSetting::current(),
            'themes' => $themes->available(),
        ]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $siteSetting = SiteSetting::current();

        $siteSetting->fill($request->safe()->except(['logo', 'key_visual']));

        if ($request->hasFile('logo')) {
            $siteSetting->logo_path = $this->images->store(
                $request->file('logo'),
                'site',
                replacing: $siteSetting->logo_path,
            );
        }

        if ($request->hasFile('key_visual')) {
            $siteSetting->key_visual_path = $this->images->store(
                $request->file('key_visual'),
                'site',
                replacing: $siteSetting->key_visual_path,
            );
        }

        $siteSetting->save();

        AuditLog::record('admin', auth('admin')->id(), 'site_settings.update', $siteSetting, request()->ip());

        return redirect()->route('admin.site-settings.edit')->with('status', 'サイト設定を更新しました。');
    }
}
