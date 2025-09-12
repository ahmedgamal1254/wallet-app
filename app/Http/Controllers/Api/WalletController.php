<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TopupRequestResource;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function balance(Request $request)
    {
        $wallet = $request->user()->getOrCreateWallet();

        return response()->json([
            'success' => true,
            'wallet' => [
                'balance' => $wallet->balance,
                'held_balance' => $wallet->held_balance,
                'available_balance' => $wallet->available_balance,
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'transactions' => TransactionResource::collection($transactions),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }

    public function requestTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
        ]);

        try {
            $topupRequest = $this->walletService->createTopupRequest(
                $request->user()->id,
                $request->amount
            );

            return response()->json([
                'success' => true,
                'message' => 'Top-up request created successfully',
                'topup_request' => new TopupRequestResource($topupRequest)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create top-up request',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function topupRequests(Request $request)
    {
        $topupRequests = $request->user()
            ->topupRequests()
            ->with('processedBy')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'topup_requests' => TopupRequestResource::collection($topupRequests),
            'pagination' => [
                'current_page' => $topupRequests->currentPage(),
                'last_page' => $topupRequests->lastPage(),
                'per_page' => $topupRequests->perPage(),
                'total' => $topupRequests->total(),
            ]
        ]);
    }
}
