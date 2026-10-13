<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GiftCodeController extends Controller
{
    /**
     * Handle gift code redemption AJAX request.
     */
    public function useCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $account = Auth::user();
        $codeStr = trim($request->code);

        // 1. Find the gift code in 'gift' table
        $gift = DB::table('gift')->where('name', $codeStr)->first();
        if (!$gift) {
            return response()->json([
                'success' => false,
                'message' => 'Mã quà tặng không chính xác.'
            ]);
        }

        // 2. Check list of users who already redeemed
        $listUser = json_decode($gift->list_user, true) ?? [];
        if (in_array($account->username, $listUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã sử dụng mã quà tặng này trước đây.'
            ]);
        }

        // 3. Check player character
        $player = Player::where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa tạo nhân vật trong game. Vui lòng vào game tạo nhân vật trước.'
            ]);
        }

        // 4. Check power requirement
        if ($player->power < $gift->power) {
            return response()->json([
                'success' => false,
                'message' => 'Nhân vật của bạn cần tối thiểu ' . number_format($gift->power) . ' sức mạnh để nhận code này.'
            ]);
        }

        // 5. Parse the item list from code
        $itemIds = json_decode($gift->item_id, true) ?? [];
        $quantities = json_decode($gift->item_quantity, true) ?? [];
        $itemOptions = json_decode($gift->item_options, true) ?? [];

        if (empty($itemIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã quà tặng này không chứa vật phẩm nào.'
            ]);
        }

        // 6. Process redemption and update DB
        DB::beginTransaction();
        try {
            // Deliver each item to Web Chest
            foreach ($itemIds as $index => $itemId) {
                $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 1;
                $optionsArray = isset($itemOptions[$index]) && is_array($itemOptions[$index]) ? $itemOptions[$index] : [];

                \App\Models\WebChestItem::create([
                    'account_id' => $account->id,
                    'item_template_id' => (int)$itemId,
                    'quantity' => (int)$quantity,
                    'options' => $optionsArray
                ]);
            }

            // Add username to used list
            $listUser[] = $account->username;
            DB::table('gift')
                ->where('id', $gift->id)
                ->update([
                    'list_user' => json_encode($listUser)
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nhập mã quà tặng "' . $gift->name . '" thành công! Các vật phẩm đã được chuyển vào Rương đồ Web. Vui lòng vào Hồ Sơ để rút vào game.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình xử lý: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle claim all available gift codes AJAX request.
     */
    public function claimAll(Request $request)
    {
        $account = Auth::user();

        // 1. Check player character
        $player = Player::where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa tạo nhân vật trong game. Vui lòng vào game tạo nhân vật trước.'
            ]);
        }

        // 2. Fetch all gift codes
        $gifts = DB::table('gift')->get();
        $claimedCount = 0;
        $deliveredItemsCount = 0;
        $claimedCodes = [];

        DB::beginTransaction();
        try {
            foreach ($gifts as $gift) {
                $listUser = json_decode($gift->list_user, true) ?? [];
                
                // Skip if already used
                if (in_array($account->username, $listUser)) {
                    continue;
                }

                // Skip if not enough power
                if ($player->power < $gift->power) {
                    continue;
                }

                // Parse items
                $itemIds = json_decode($gift->item_id, true) ?? [];
                $quantities = json_decode($gift->item_quantity, true) ?? [];
                $itemOptions = json_decode($gift->item_options, true) ?? [];

                if (empty($itemIds)) {
                    continue;
                }

                // Deliver items for this gift to Web Chest
                foreach ($itemIds as $index => $itemId) {
                    $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 1;
                    $optionsArray = isset($itemOptions[$index]) && is_array($itemOptions[$index]) ? $itemOptions[$index] : [];

                    \App\Models\WebChestItem::create([
                        'account_id' => $account->id,
                        'item_template_id' => (int)$itemId,
                        'quantity' => (int)$quantity,
                        'options' => $optionsArray
                    ]);
                    $deliveredItemsCount++;
                }

                // Update list_user
                $listUser[] = $account->username;
                DB::table('gift')
                    ->where('id', $gift->id)
                    ->update([
                        'list_user' => json_encode($listUser)
                    ]);

                $claimedCount++;
                $claimedCodes[] = $gift->name;
            }

            if ($claimedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã sử dụng hết các mã giftcode hợp lệ hoặc rương đồ web đầy.'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thành công! Đã nhận ' . $claimedCount . ' mã quà tặng (' . implode(', ', $claimedCodes) . '). Tổng cộng ' . $deliveredItemsCount . ' vật phẩm đã được chuyển vào Rương đồ Web. Vui lòng vào Hồ Sơ để rút vào game.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình nhận tất cả: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format item options to match game server internal format.
     */
    private function formatItemOptions($optionsArray)
    {
        if (empty($optionsArray)) {
            return "[]";
        }
        $formatted = [];
        foreach ($optionsArray as $opt) {
            if (isset($opt[0])) {
                $formatted[] = json_encode([(int)$opt[0], (int)$opt[1]]);
            }
        }
        return json_encode($formatted);
    }
}
