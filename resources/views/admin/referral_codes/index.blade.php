@extends('admin.layouts.app')

@section('title', 'Referral Codes')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold mb-4">Referral Codes</h3>
        <a href="{{ route('admin.referral_codes.create') }}"
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">+ New Code</a>
    </div>
    <table class="w-full border border-gray-200 rounded-lg overflow-hidden shadow mt-4">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="px-4 py-2 text-left">Code</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Usage</th>
            <th class="px-4 py-2 text-left">Max Usage</th>
            <th class="px-4 py-2 text-left">Expires At</th>
            <th class="px-4 py-2 text-center">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse ($codes as $code)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-mono text-sm">{{ $code->code }}</td>

                <td class="px-4 py-2">
                    @if ($code->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                            Active
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                            Inactive
                        </span>
                    @endif
                </td>

                <td class="px-4 py-2">{{ $code->usage_count }}</td>
                <td class="px-4 py-2">{{ $code->max_usage ?? '∞' }}</td>
                <td class="px-4 py-2">{{ $code->expires_at ? $code->expires_at->format('Y-m-d') : '-' }}</td>

                <td class="px-4 py-2 text-center space-x-2">
                    <a href="{{ route('admin.referral_codes.edit', $code) }}"
                       class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>

                    <form action="{{ route('admin.referral_codes.destroy', $code) }}"
                          method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('هل أنت متأكد؟')"
                                class="text-red-600 hover:text-red-800 font-medium">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No codes found</td>
            </tr>
        @endforelse
    </tbody>
</table>

    <div class="mt-4">
        {{ $codes->links() }}
    </div>
</div>
@endsection
