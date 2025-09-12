<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferralCodeResource;
use App\Models\ReferralCode;
use App\Services\WalletService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function myCodes(Request $request)
    {
        $codes = $request->user()
            ->generatedReferralCodes()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'referral_codes' => ReferralCodeResource::collection($codes),
            'pagination' => [
                'current_page' => $codes->currentPage(),
                'last_page' => $codes->lastPage(),
                'per_page' => $codes->perPage(),
                'total' => $codes->total(),
            ]
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'max_usage' => 'nullable|integer|min:1|max:100',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            $expiresAt = $request->expires_in_days
                ? now()->addDays($request->expires_in_days)
                : null;

            $referralCode = $this->walletService->generateReferralCode(
                $request->user(),
                $request->max_usage,
                $expiresAt
            );

            return response()->json([
                'success' => true,
                'message' => 'Referral code generated successfully',
                'referral_code' => new ReferralCodeResource($referralCode)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate referral code',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function validateCode(Request $request, string $code)
    {
        $referralCode = ReferralCode::where('code', $code)->first();

        if (!$referralCode) {
            return response()->json([
                'success' => false,
                'message' => 'Referral code not found',
                'valid' => false
            ], 404);
        }

        $isValid = $referralCode->isUsable();

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'message' => $isValid ? 'Referral code is valid' : 'Referral code is expired or inactive',
            'referral_code' => new ReferralCodeResource($referralCode->load('generator'))
        ]);
    }
}

