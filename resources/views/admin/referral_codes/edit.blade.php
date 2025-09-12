@extends('admin.layouts.app')

@section('title', 'Edit Referral Code')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Edit Referral Code</h3>

    <form action="{{ route('admin.referral_codes.update', $referralCode) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-4">
            <label>Status</label>
            <select name="is_active" class="border rounded w-full p-2">
                <option value="1" @if($referralCode->is_active) selected @endif>Active</option>
                <option value="0" @if(!$referralCode->is_active) selected @endif>Inactive</option>
            </select>
        </div>

        <div class="mb-4">
            <label>Max Usage</label>
            <input type="number" name="max_usage" value="{{ $referralCode->max_usage }}" class="border rounded w-full p-2">
        </div>

        <div class="mb-4">
            <label>Expires At</label>
            <input type="date" name="expires_at" value="{{ optional($referralCode->expires_at)->format('Y-m-d') }}" class="border rounded w-full p-2">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Update</button>
    </form>
</div>
@endsection
