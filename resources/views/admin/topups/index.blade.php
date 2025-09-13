@extends('admin.layouts.app')

@section('title', 'Top-up Requests')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Top-up Requests</h3>

    <table class="w-full border border-gray-200 rounded-lg overflow-hidden shadow mt-4 text-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="px-4 py-2 text-left">User</th>
                <th class="px-4 py-2 text-left">Amount</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($topups as $topup)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">
                        {{ $topup->user->name ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-2 font-mono text-gray-600">
                        {{ number_format($topup->amount, 2) }} EGP
                    </td>
                    <td class="px-4 py-2">
                        {!! status_badge($topup->status) !!}
                    </td>
                    <td class="px-4 py-2 text-center">
                        <a href="{{ route('admin.topups.show', $topup) }}"
                        class="inline-flex items-center px-2 py-1 text-base font-semibold text-blue-600 hover:text-blue-800">
                         View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                        No top-up requests found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $topups->links() }}
    </div>
</div>
@endsection
