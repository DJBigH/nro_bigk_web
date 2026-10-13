<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use App\Models\WebShopItem;
use App\Models\WebShopPurchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Show Web Shop items.
     */
    public function index()
    {
        $account = Auth::user();
        
        // Auto seed sample web shop items if empty so user has items to purchase immediately
        if (WebShopItem::count() === 0) {
            // Seed a few popular items (e.g. Item 457: Thỏi vàng, Item 16: Ngọc rồng 3 sao, Item 2001: Capsule kì bí)
            $sampleItems = [
                ['item_template_id' => 457, 'price_vnd' => 10000, 'quantity' => -1, 'is_active' => 1],
                ['item_template_id' => 16, 'price_vnd' => 5000, 'quantity' => 100, 'is_active' => 1],
                ['item_template_id' => 2001, 'price_vnd' => 20000, 'quantity' => 10, 'is_active' => 1]
            ];
            foreach ($sampleItems as $item) {
                // Verify if item_template exists in DB
                $exists = DB::table('item_template')->where('id', $item['item_template_id'])->exists();
                if ($exists) {
                    WebShopItem::create($item);
                }
            }
        }

        $items = WebShopItem::where('is_active', 1)->paginate(12);

        return view('shop.index', compact('items', 'account'));
    }

    /**
     * Handle item purchase (AJAX).
     */
    public function buy(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:web_shop_items,id'
        ]);

        $account = Auth::user();
        $shopItem = WebShopItem::findOrFail($request->item_id);
        $template = $shopItem->template;

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Vật phẩm không tồn tại trên hệ thống game.'
            ]);
        }

        // 1. Check balance
        if (($account->vnd ?? 0) < $shopItem->price_vnd) {
            return response()->json([
                'success' => false,
                'message' => 'Số dư VND không đủ để mua vật phẩm này. Vui lòng nạp thêm.'
            ]);
        }

        // 2. Check stock
        if ($shopItem->quantity !== -1 && $shopItem->quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vật phẩm này đã hết hàng.'
            ]);
        }

        // 3. Check player
        $player = Player::where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa tạo nhân vật trong game. Vui lòng vào game tạo nhân vật trước.'
            ]);
        }

        // 4. Deliver item to Web Chest and update DB
        DB::beginTransaction();
        try {
            // Deduct VND
            DB::table('account')
                ->where('id', $account->id)
                ->update([
                    'vnd' => $account->vnd - $shopItem->price_vnd
                ]);

            // Deduct Shop Item stock (if not unlimited)
            if ($shopItem->quantity !== -1) {
                $shopItem->decrement('quantity');
            }

            // Write item to Web Chest
            \App\Models\WebChestItem::create([
                'account_id' => $account->id,
                'item_template_id' => (int)$shopItem->item_template_id,
                'quantity' => 1,
                'options' => is_array($shopItem->options) ? $shopItem->options : (json_decode($shopItem->options, true) ?? [])
            ]);

            // Save purchase log
            WebShopPurchase::create([
                'account_id' => $account->id,
                'item_template_id' => $shopItem->item_template_id,
                'price_vnd' => $shopItem->price_vnd
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mua vật phẩm "' . $template->NAME . '" thành công! Vật phẩm đã được chuyển vào Rương đồ Web. Bạn có thể vào trang hồ sơ để rút vật phẩm vào game bất cứ lúc nào.',
                'new_balance' => number_format($account->vnd - $shopItem->price_vnd)
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
