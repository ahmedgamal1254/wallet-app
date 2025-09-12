<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\TopupRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        $stats = [
            'total_users' => User::count(),
            'total_admins' => Admin::count(),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'pending_topups' => TopupRequest::where('status', 'pending')->count(),
            'total_transactions_today' => Transaction::whereDate('created_at', today())->count(),
        ];

        $recentWithdrawals = WithdrawalRequest::with(['requester', 'processedBy'])
            ->latest()
            ->take(5)
            ->get();

        $recentTopups = TopupRequest::with(['user', 'processedBy'])
            ->latest()
            ->take(5)
            ->get();

        // Get notifications for the authenticated admin
        $notifications = $admin->notifications()->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentWithdrawals', 'recentTopups', 'notifications'));
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        $notification = $admin->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->back();
    }
}
