@extends('admin.layouts.app')

@section('title', 'Top-up Request Details')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Top-up Request Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><strong>User:</strong> {{ $topup->user->name ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> {{ $topup->amount }}</p>
            <p><strong>Status:</strong> {!! status_badge($topup->status) !!}</p>
            <p><strong>Processed By:</strong> {{ $topup->processedBy->name ?? 'N/A' }}</p>
            <p><strong>Created At:</strong> {{ $topup->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    @if ($topup->status === 'pending')
        <div class="mt-6 flex space-x-4">
            <form action="{{ route('admin.topups.approve', $topup) }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Approve</button>
            </form>
            <form action="{{ route('admin.topups.reject', $topup) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Rejection</label>
                    <textarea name="reason" id="reason" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required></textarea>
                </div>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Reject</button>
            </form>
        </div>
    @endif
</div>
@endsection
