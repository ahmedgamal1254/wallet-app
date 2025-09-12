<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopupRequest;
use App\Services\WalletService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    protected $walletService;
    protected $permissionService;

    public function __construct(WalletService $walletService, PermissionService $permissionService)
    {
        $this->walletService = $walletService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $topups = TopupRequest::with(['user', 'processedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.topups.index', compact('topups'));
    }

    public function show(TopupRequest $topup)
    {
        $topup->load(['user', 'processedBy']);
        return view('admin.topups.show', compact('topup'));
    }

    public function approve(Request $request, TopupRequest $topup)
    {
        $admin = Auth::guard('admin')->user();

        if (!$this->permissionService->canAcceptTopups($admin)) {
            abort(403, 'You do not have permission to approve top-up requests.');
        }

        if ($topup->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        try {
            $this->walletService->processTopupRequest($topup, $admin, 'approve');
            return back()->with('success', 'Top-up request approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, TopupRequest $topup)
    {
        $admin = Auth::guard('admin')->user();

        if (!$this->permissionService->canRejectTopups($admin)) {
            abort(403, 'You do not have permission to reject top-up requests.');
        }

        if ($topup->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->walletService->processTopupRequest($topup, $admin, 'reject', $request->reason);
            return back()->with('success', 'Top-up request rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
