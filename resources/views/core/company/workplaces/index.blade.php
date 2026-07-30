@extends('layouts.manage')

@section('title', '事業所')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">事業所</h1>

        <a href="{{ route('company.workplaces.create') }}"
           class="rounded px-4 py-2 text-sm font-semibold text-white"
           style="background-color: var(--theme-color)">
            事業所を追加
        </a>
    </div>

    @if (session('error'))
        <div class="mt-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 overflow-x-auto rounded border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs text-gray-600">
                <tr>
                    <th class="px-4 py-3">事業所名</th>
                    <th class="px-4 py-3">施設形態</th>
                    <th class="px-4 py-3">所在地</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($workplaces as $workplace)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $workplace->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $workplace->facilityType?->displayName() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $workplace->locationLabel() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('company.workplaces.edit', $workplace) }}"
                                   class="text-gray-600 hover:underline">編集</a>
                                <form method="POST" action="{{ route('company.workplaces.destroy', $workplace) }}"
                                      onsubmit="return confirm('この事業所を削除しますか?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            事業所がまだ登録されていません。求人を掲載するにはまず事業所を登録してください。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
