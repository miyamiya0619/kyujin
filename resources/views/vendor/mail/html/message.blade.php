{{--
    メールの共通レイアウト(TASKS.md T-15)。

    ThemeServiceProvider が `View::composer('*', ...)` で $site をどのビューにも
    共有しているため、ここでも $site->site_name / logo_path をそのまま参照できる
    (テーマがモデルを直接触らずに済む仕組みと同じ。CLAUDE.md 3.2)。
--}}
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ $site->site_name }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $site->site_name }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
