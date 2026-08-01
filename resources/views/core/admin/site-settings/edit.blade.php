@extends('layouts.manage')

@section('title', 'サイト設定')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">サイト設定</h1>
    <p class="mt-1 text-sm text-gray-600">変更は保存すると即座にサイト全体へ反映されます。</p>

    <form method="POST" action="{{ route('admin.site-settings.update') }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl space-y-8">
        @csrf
        @method('PUT')

        <div class="rounded border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-gray-700">メディアの基本情報</h2>

            <div class="mt-4 space-y-5">
                <x-form.field name="site_name" label="メディア名" required>
                    <x-form.input name="site_name" :value="$siteSetting->site_name" required />
                </x-form.field>

                <x-form.field name="catch_copy" label="キャッチコピー">
                    <x-form.input name="catch_copy" :value="$siteSetting->catch_copy" />
                </x-form.field>

                <x-form.field name="meta_description" label="メタディスクリプション" help="検索結果に表示される説明文です。">
                    <x-form.textarea name="meta_description" :value="$siteSetting->meta_description" :rows="3" />
                </x-form.field>

                <x-form.field name="logo" label="ロゴ画像" help="JPEG・PNG・WebP、2MB まで。">
                    @if ($siteSetting->logo_path)
                        <img src="{{ \App\Services\ImageUploadService::url($siteSetting->logo_path) }}"
                             alt="現在のロゴ" class="mt-2 h-16 rounded border border-gray-200 bg-white p-1">
                    @endif
                    <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp"
                           class="mt-2 block w-full text-sm text-gray-700">
                </x-form.field>

                <x-form.field name="key_visual" label="キービジュアル" help="トップページ等で使う画像です。JPEG・PNG・WebP、4MB まで。">
                    @if ($siteSetting->key_visual_path)
                        <img src="{{ \App\Services\ImageUploadService::url($siteSetting->key_visual_path) }}"
                             alt="現在のキービジュアル" class="mt-2 h-24 rounded border border-gray-200 bg-white p-1">
                    @endif
                    <input id="key_visual" name="key_visual" type="file" accept="image/jpeg,image/png,image/webp"
                           class="mt-2 block w-full text-sm text-gray-700">
                </x-form.field>
            </div>
        </div>

        <div class="rounded border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-gray-700">見た目</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.field name="theme" label="テーマ" required>
                    <x-form.select name="theme" :value="$siteSetting->theme"
                                   :options="collect($themes)->mapWithKeys(fn ($t) => [$t => $t])" />
                </x-form.field>

                <x-form.field name="theme_color" label="テーマカラー" required help="例) #2563eb">
                    <x-form.input name="theme_color" type="color" :value="$siteSetting->theme_color" />
                </x-form.field>
            </div>
        </div>

        <div class="rounded border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-gray-700">連絡先</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.field name="contact_email" label="連絡先メールアドレス">
                    <x-form.input name="contact_email" type="email" :value="$siteSetting->contact_email" />
                </x-form.field>

                <x-form.field name="contact_tel" label="連絡先電話番号">
                    <x-form.input name="contact_tel" type="tel" :value="$siteSetting->contact_tel" />
                </x-form.field>
            </div>
        </div>

        <div class="rounded border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-gray-700">機能のオンオフ</h2>

            <div class="mt-4 space-y-3 text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="requires_review" value="1"
                           @checked(old('requires_review', $siteSetting->requires_review)) class="rounded border-gray-300">
                    審査フロー(OFF にすると掲載企業の提出が即公開になります)
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="enables_member" value="1"
                           @checked(old('enables_member', $siteSetting->enables_member)) class="rounded border-gray-300">
                    求職者会員機能(OFF にすると都度応募のみになります)
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="enables_posting_plan" value="1"
                           @checked(old('enables_posting_plan', $siteSetting->enables_posting_plan)) class="rounded border-gray-300">
                    掲載プランによる掲載件数の制限
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="enables_external_feed" value="1"
                           @checked(old('enables_external_feed', $siteSetting->enables_external_feed)) class="rounded border-gray-300">
                    アグリゲーション連携(OFF にすると Indeed 等への配信を全て停止します)
                </label>
            </div>
        </div>

        <div class="rounded border border-gray-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-gray-700">規約類</h2>

            <div class="mt-4 space-y-5">
                <x-form.field name="terms_of_service" label="利用規約">
                    <x-form.textarea name="terms_of_service" :value="$siteSetting->terms_of_service" :rows="8" />
                </x-form.field>

                <x-form.field name="privacy_policy" label="プライバシーポリシー">
                    <x-form.textarea name="privacy_policy" :value="$siteSetting->privacy_policy" :rows="8" />
                </x-form.field>
            </div>
        </div>

        <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                style="background-color: var(--theme-color)">
            保存する
        </button>
    </form>
@endsection
