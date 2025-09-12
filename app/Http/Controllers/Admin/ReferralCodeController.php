<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use Illuminate\Http\Request;

class ReferralCodeController extends Controller
{
    public function index()
    {
        $codes = ReferralCode::latest()->paginate(10);
        return view('admin.referral_codes.index', compact('codes'));
    }

    public function create()
    {
        return view('admin.referral_codes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'max_usage' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $referralCode = auth('admin')->user()
        ->referralCodes()
        ->create([
            'code'       => ReferralCode::generateUniqueCode(),
            'max_usage'  => $request->max_usage,
            'expires_at' => $request->expires_at,
            'is_active'  => true,
        ]);

        if (!$referralCode) {
            return redirect()->route('admin.referral_codes.index')->with('error', 'حدث خطأ في انشاء كود الدعوة');
        }


        return redirect()->route('admin.referral_codes.index')->with('success', 'تم إنشاء كود الدعوة بنجاح');
    }

    public function edit(ReferralCode $referralCode)
    {
        return view('admin.referral_codes.edit', compact('referralCode'));
    }

    public function update(Request $request, ReferralCode $referralCode)
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'max_usage' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $referralCode->update($request->only('is_active', 'max_usage', 'expires_at'));

        return redirect()->route('admin.referral_codes.index')->with('success', 'تم تحديث الكود بنجاح');
    }

    public function destroy(ReferralCode $referralCode)
    {
        $referralCode->delete();
        return redirect()->route('admin.referral_codes.index')->with('success', 'تم حذف الكود بنجاح');
    }
}
