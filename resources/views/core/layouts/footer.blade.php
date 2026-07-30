{{-- コアの共通フッター。テーマで差し替え可能。 --}}
<footer class="border-t border-gray-200 bg-gray-50">
    <div class="mx-auto max-w-5xl px-4 py-8 text-sm text-gray-600">
        <p class="font-semibold">{{ $site->site_name }}</p>

        @if ($site->contact_tel || $site->contact_email)
            <p class="mt-2">
                @if ($site->contact_tel)
                    <span>TEL: {{ $site->contact_tel }}</span>
                @endif
                @if ($site->contact_email)
                    <span class="ml-3">{{ $site->contact_email }}</span>
                @endif
            </p>
        @endif

        <p class="mt-4 text-xs text-gray-500">&copy; {{ now()->year }} {{ $site->site_name }}</p>
    </div>
</footer>
