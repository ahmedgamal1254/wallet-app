@extends('admin.layouts.app')

@section('title', 'Create Referral Code')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Create Referral Code</h3>

    <form action="{{ route('admin.referral_codes.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label>Max Usage</label>
            <input type="number" name="max_usage" class="border rounded w-full p-2">
        </div>

        <div class="mb-4">
            <label>Expires At</label>
            <input type="date" name="expires_at" class="border rounded w-full p-2">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Save</button>
    </form>
</div>
@endsection
