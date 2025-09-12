<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
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
        $withdrawals = WithdrawalRequest::with(['requester', 'processedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function show(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load(['requester', 'processedBy']);
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function create()
    {
        return view('admin.withdrawals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $admin = Auth::guard('admin')->user();

        try {
            $withdrawal = $this->walletService->createWithdrawalRequest($admin, $request->amount);
            return redirect()->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal request created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        $admin = Auth::guard('admin')->user();

        if (!$this->permissionService->canAcceptWithdrawals($admin)) {
            abort(403, 'You do not have permission to approve withdrawal requests.');
        }

        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        try {
            $this->walletService->processWithdrawalRequest($withdrawal, $admin, 'approve');
            return back()->with('success', 'Withdrawal request approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $admin = Auth::guard('admin')->user();

        if (!$this->permissionService->canRejectWithdrawals($admin)) {
            abort(403, 'You do not have permission to reject withdrawal requests.');
        }

        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->walletService->processWithdrawalRequest($withdrawal, $admin, 'reject', $request->reason);
            return back()->with('success', 'Withdrawal request rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
