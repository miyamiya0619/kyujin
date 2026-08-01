@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($site->logo_path ?? null)
<img src="{{ \App\Services\ImageUploadService::url($site->logo_path) }}" class="logo" alt="{{ $site->site_name }}">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
