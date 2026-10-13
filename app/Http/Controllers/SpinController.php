<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use App\Models\Setting;
use App\Models\SpinWheelItem;
use App\Models\SpinWheelHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    /**
     * Show Spin Wheel page.
     */
    public function index()
    {
        $account = Auth::user();

        // 1. Get settings
        $spinPrice = (int)Setting::get('spin_price_vnd', 10000);
        $spinEnabled = Setting::get('spin_enabled', '1') == '1';

        // 2. Auto-seed items if empty
        if (SpinWheelItem::count() === 0) {
            // Check if Item 457 and 16 exist in game DB
            $hasGold = DB::table('item_template')->where('id', 457)->exists();
            $hasDragonBall = DB::table('item_template')->where('id', 16)->exists();

            SpinWheelItem::insert([
                [
                    'item_template_id' => $hasGold ? 457 : null,
                    'display_name' => 'Thỏi Vàng x1',
                    'image' => null,
                    'reward_type' => 'item',
                    'reward_value' => 1,
                    'ratio' => 30.00,
                    'color' => '#ffcc00',
                    'is_active' => 1,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'item_template_id' => $hasGold ? 457 : null,
                    'display_name' => 'Thỏi Vàng x5',
                    'image' => null,
                    'reward_type' => 'item',
                    'reward_value' => 5,
                    'ratio' => 5.00,
                    'color' => '#ff6b00',
                    'is_active' => 1,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'item_template_id' => $hasDragonBall ? 16 : null,
                    'display_name' => 'Ngọc Rồng 3 Sao',
                    'image' => null,
                    'reward_type' => 'item',
                    'reward_value' => 1,
                    'ratio' => 10.00,
                    'color' => '#e91e63',
                    'is_active' => 1,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'item_template_id' => null,
                    'display_name' => '5.000đ VND',
                    'image' => null,
                    'reward_type' => 'vnd',
                    'reward_value' => 5000,
                    'ratio' => 20.00,
                    'color' => '#4caf50',
                    'is_active' => 1,
                    'sort_order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'item_template_id' => null,
                    'display_name' => '20.000đ VND',
                    'image' => null,
                    'reward_type' => 'vnd',
                    'reward_value' => 20000,
                    'ratio' => 3.00,
                    'color' => '#00bcd4',
                    'is_active' => 1,
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'item_template_id' => null,
                    'display_name' => 'Chúc May Mắn',
                    'image' => null,
                    'reward_type' => 'vnd',
                    'reward_value' => 0,
                    'ratio' => 32.00,
                    'color' => '#607d8b',
                    'is_active' => 1,
                    'sort_order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Fetch active spin wheel items sorted
        $items = SpinWheelItem::where('is_active', 1)->orderBy('sort_order', 'asc')->get();

        // Fetch recent spins across server
        $recentSpins = SpinWheelHistory::with(['account', 'spinItem'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('spin.index', compact('items', 'spinPrice', 'spinEnabled', 'recentSpins', 'account'));
    }

    /**
     * Process spin (AJAX).
     */
    public function spin(Request $request)
    {
        $account = Auth::user();

        // 1. Check feature status
        $spinEnabled = Setting::get('spin_enabled', '1') == '1';
        if (!$spinEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'Tính năng Vòng Quay May Mắn hiện đang tạm đóng.'
            ]);
        }

        // 2. Check price
        $spinPrice = (int)Setting::get('spin_price_vnd', 10000);
        if (($account->vnd ?? 0) < $spinPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Số dư VND không đủ để quay. Giá quay là ' . number_format($spinPrice) . ' VND. Vui lòng nạp thêm.'
            ]);
        }

        // 3. Check character
        $player = Player::where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa tạo nhân vật trong game. Vui lòng vào game tạo nhân vật trước.'
            ]);
        }

        // 4. Load active spin items
        $items = SpinWheelItem::where('is_active', 1)->orderBy('sort_order', 'asc')->get();
        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có phần thưởng nào trên vòng quay lúc này.'
            ]);
        }

        // 5. Weighted random selection
        $totalRatio = $items->sum('ratio');
        $randVal = mt_rand(1, $totalRatio * 100) / 100.0;

        $winningItem = null;
        $winnerIndex = 0;
        $cumulativeRatio = 0.0;

        foreach ($items as $idx => $item) {
            $cumulativeRatio += (float)$item->ratio;
            if ($randVal <= $cumulativeRatio) {
                $winningItem = $item;
                $winnerIndex = $idx;
                break;
            }
        }

        // Fallback in case rounding leaves it select-less
        if (!$winningItem) {
            $winningItem = $items->first();
            $winnerIndex = 0;
        }

        // 6. Deliver rewards and deduct balance
        DB::beginTransaction();
        try {
            // Deduct spin cost
            $currentVnd = $account->vnd ?? 0;
            $newVnd = $currentVnd - $spinPrice;

            // Update account VND
            DB::table('account')
                ->where('id', $account->id)
                ->update(['vnd' => $newVnd]);

            $rewardMsg = "";

            if ($winningItem->reward_type === 'vnd') {
                if ($winningItem->reward_value > 0) {
                    $newVnd += $winningItem->reward_value;
                    DB::table('account')
                        ->where('id', $account->id)
                        ->update(['vnd' => $newVnd]);
                    $rewardMsg = "Bạn đã trúng " . number_format($winningItem->reward_value) . " VND! Tiền đã được cộng vào tài khoản.";
                } else {
                    $rewardMsg = "Chúc bạn may mắn lần sau!";
                }
            } elseif ($winningItem->reward_type === 'coin') {
                $currentCoin = $account->coin ?? 0;
                DB::table('account')
                    ->where('id', $account->id)
                    ->update(['coin' => $currentCoin + $winningItem->reward_value]);
                $rewardMsg = "Bạn đã trúng " . number_format($winningItem->reward_value) . " Xu game! Tiền đã được cộng vào tài khoản.";
            } elseif ($winningItem->reward_type === 'item') {
                // Deliver item to Web Chest
                \App\Models\WebChestItem::create([
                    'account_id' => $account->id,
                    'item_template_id' => (int)$winningItem->item_template_id,
                    'quantity' => (int)$winningItem->reward_value,
                    'options' => [] // Spin items don't have default options from database
                ]);
                $rewardMsg = "Bạn đã trúng " . $winningItem->display_name . "! Vật phẩm đã được chuyển vào Rương đồ Web. Hãy vào Hồ Sơ để rút vào game.";
            }

            // Save history log
            SpinWheelHistory::create([
                'account_id' => $account->id,
                'spin_item_id' => $winningItem->id,
                'cost_vnd' => $spinPrice
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'winner_index' => $winnerIndex,
                'item_name' => $winningItem->display_name,
                'reward_type' => $winningItem->reward_type,
                'reward_value' => $winningItem->reward_value,
                'message' => $rewardMsg,
                'new_balance' => number_format($newVnd)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình quay: ' . $e->getMessage()
            ]);
        }
    }
}
