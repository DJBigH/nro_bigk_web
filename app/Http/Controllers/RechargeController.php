<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\WebRechargePackage;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RechargeController extends Controller
{
    /**
     * Show recharge packages page.
     */
    public function index()
    {
        $account = Auth::user();

        // Auto seed default packages if empty
        if (WebRechargePackage::count() === 0) {
            WebRechargePackage::insert([
                [
                    'name' => 'Gói Nạp 10.000đ',
                    'amount_vnd' => 10000,
                    'bonus_vnd' => 0,
                    'is_popular' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Gói Nạp 20.000đ',
                    'amount_vnd' => 20000,
                    'bonus_vnd' => 1000, // 5% bonus
                    'is_popular' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Gói Nạp 50.000đ',
                    'amount_vnd' => 50000,
                    'bonus_vnd' => 5000, // 10% bonus
                    'is_popular' => 1, // popular
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Gói Nạp 100.000đ',
                    'amount_vnd' => 100000,
                    'bonus_vnd' => 15000, // 15% bonus
                    'is_popular' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Gói Nạp 200.000đ',
                    'amount_vnd' => 200000,
                    'bonus_vnd' => 40000, // 20% bonus
                    'is_popular' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Gói Nạp 500.000đ',
                    'amount_vnd' => 500000,
                    'bonus_vnd' => 125000, // 25% bonus
                    'is_popular' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $packages = WebRechargePackage::where('is_active', 1)->orderBy('amount_vnd', 'asc')->get();

        // Get recent transactions of current user
        $transactions = PaymentTransaction::where('account_id', $account->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('recharge.index', compact('packages', 'transactions', 'account'));
    }

    /**
     * Confirm recharge payment (Demo processing).
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:web_recharge_packages,id'
        ]);

        $account = Auth::user();
        $package = WebRechargePackage::findOrFail($request->package_id);

        $totalAddedVnd = $package->amount_vnd + $package->bonus_vnd;

        DB::beginTransaction();
        try {
            // Update account balance
            // Ensure we treat existing null value as 0
            $currentVnd = $account->vnd ?? 0;
            $newVnd = $currentVnd + $totalAddedVnd;
            $newTongnap = ($account->tongnap ?? 0) + $package->amount_vnd;

            DB::table('account')
                ->where('id', $account->id)
                ->update([
                    'vnd' => $newVnd,
                    'tongnap' => $newTongnap
                ]);

            // Save log
            PaymentTransaction::create([
                'account_id' => $account->id,
                'gateway' => 'demo',
                'transaction_code' => 'DEMO_' . strtoupper(Str::random(10)),
                'amount' => $package->amount_vnd,
                'status' => 'success',
                'response_data' => [
                    'package_name' => $package->name,
                    'bonus_vnd' => $package->bonus_vnd,
                    'note' => 'Đã xác nhận thanh toán thành công (Bản Demo).'
                ],
                'payment_time' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nạp tiền thành công! Bạn được cộng ' . number_format($totalAddedVnd) . ' VND.',
                'new_balance' => number_format($newVnd)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình xử lý: ' . $e->getMessage()
            ]);
        }
    }
}
