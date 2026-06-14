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

        // 5. Check if player is online
        $lastLogin = $account->last_time_login ? strtotime($account->last_time_login) : 0;
        $lastLogout = $account->last_time_logout ? strtotime($account->last_time_logout) : 0;
        if ($lastLogin > $lastLogout) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Tài khoản đang online trong game. Vui lòng THOÁT GAME hoàn toàn trước khi nhận code để tránh mất đồ.'
            ]);
        }

        // 6. Parse the item list from code
        $itemIds = json_decode($gift->item_id, true) ?? [];
        $quantities = json_decode($gift->item_quantity, true) ?? [];

        if (empty($itemIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã quà tặng này không chứa vật phẩm nào.'
            ]);
        }

        // 7. Load current items_bag and count empty slots
        $itemsBag = json_decode($player->items_bag, true);
        if (!is_array($itemsBag)) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu hành trang nhân vật không hợp lệ.'
            ]);
        }

        // Count empty slots
        $emptySlots = [];
        foreach ($itemsBag as $index => $slotStr) {
            $slotData = json_decode($slotStr, true);
            if (is_array($slotData) && isset($slotData[0]) && $slotData[0] == -1) {
                $emptySlots[] = $index;
            }
        }

        $itemsCount = count($itemIds);
        if (count($emptySlots) < $itemsCount) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Hành trang của bạn cần ít nhất ' . $itemsCount . ' ô trống để nhận các vật phẩm này. Hiện tại chỉ trống ' . count($emptySlots) . ' ô.'
            ]);
        }

        // 8. Process redemption and update DB
        DB::beginTransaction();
        try {
            // Deliver each item to inventory
            foreach ($itemIds as $index => $itemId) {
                $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 1;
                $emptySlotIndex = $emptySlots[$index];

                // Build item slot array [itemId, quantity, options, createTime]
                $newItemSlot = [
                    (int)$itemId,
                    (int)$quantity,
                    "[]",
                    round(microtime(true) * 1000)
                ];

                $itemsBag[$emptySlotIndex] = json_encode($newItemSlot);
            }

            // Save modified inventory bag to player
            DB::table('player')
                ->where('id', $player->id)
                ->update([
                    'items_bag' => json_encode($itemsBag)
                ]);

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
                'message' => 'Nhập mã quà tặng "' . $gift->name . '" thành công! Các vật phẩm đã được chuyển vào hành trang.'
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

        // 2. Check if player is online
        $lastLogin = $account->last_time_login ? strtotime($account->last_time_login) : 0;
        $lastLogout = $account->last_time_logout ? strtotime($account->last_time_logout) : 0;
        if ($lastLogin > $lastLogout) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Tài khoản đang online trong game. Vui lòng THOÁT GAME hoàn toàn trước khi nhận code để tránh mất đồ.'
            ]);
        }

        // 3. Load current items_bag and find empty slots
        $itemsBag = json_decode($player->items_bag, true);
        if (!is_array($itemsBag)) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu hành trang nhân vật không hợp lệ.'
            ]);
        }

        // Find all empty slots
        $emptySlots = [];
        foreach ($itemsBag as $index => $slotStr) {
            $slotData = json_decode($slotStr, true);
            if (is_array($slotData) && isset($slotData[0]) && $slotData[0] == -1) {
                $emptySlots[] = $index;
            }
        }

        if (empty($emptySlots)) {
            return response()->json([
                'success' => false,
                'message' => 'Hành trang của bạn không còn ô trống nào.'
            ]);
        }

        // 4. Fetch all gift codes
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
                $itemsCount = count($itemIds);

                if (empty($itemIds)) {
                    continue;
                }

                // Check if we have enough empty slots left for this code
                if (count($emptySlots) < $itemsCount) {
                    // Stop or skip if inventory gets full
                    break;
                }

                // Deliver items for this gift
                foreach ($itemIds as $index => $itemId) {
                    $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 1;
                    $emptySlotIndex = array_shift($emptySlots); // Take first empty slot

                    $newItemSlot = [
                        (int)$itemId,
                        (int)$quantity,
                        "[]",
                        round(microtime(true) * 1000)
                    ];

                    $itemsBag[$emptySlotIndex] = json_encode($newItemSlot);
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
                    'message' => 'Bạn đã sử dụng hết các mã giftcode hợp lệ hoặc hành trang không đủ chỗ.'
                ]);
            }

            // Save player inventory
            DB::table('player')
                ->where('id', $player->id)
                ->update([
                    'items_bag' => json_encode($itemsBag)
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thành công! Đã nhận ' . $claimedCount . ' mã quà tặng (' . implode(', ', $claimedCodes) . '). Tổng cộng ' . $deliveredItemsCount . ' vật phẩm đã được chuyển vào hành trang.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình nhận tất cả: ' . $e->getMessage()
            ]);
        }
    }
}
