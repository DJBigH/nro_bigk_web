<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use App\Models\WebShopItem;
use App\Models\WebShopPurchase;
use App\Models\GiftCode;
use App\Models\DownloadFile;
use App\Models\Post;
use App\Models\SpinWheelItem;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Models\WebAccSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Admin Dashboard index.
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => Account::count(),
            'total_revenue' => PaymentTransaction::where('status', 'success')->sum('amount'),
            'total_purchases' => WebShopPurchase::count(),
            'active_shop_items' => WebShopItem::where('is_active', 1)->count(),
            'total_giftcodes' => GiftCode::count(),
            'spin_wheel_items' => SpinWheelItem::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Players and Accounts management.
     */
    public function players(Request $request)
    {
        $search = $request->input('search');
        
        $query = Account::with('players');
        if ($search) {
            $query->where('username', 'like', "%{$search}%")
                  ->orWhereHas('players', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }
        
        $accounts = $query->paginate(15);
        return view('admin.players', compact('accounts', 'search'));
    }

    public function banPlayer($id)
    {
        $account = Account::findOrFail($id);
        $account->update(['ban' => 1]);
        return response()->json(['success' => true, 'message' => 'Đã khóa tài khoản thành công.']);
    }

    public function unbanPlayer($id)
    {
        $account = Account::findOrFail($id);
        $account->update(['ban' => 0]);
        return response()->json(['success' => true, 'message' => 'Đã mở khóa tài khoản thành công.']);
    }

    public function setAdmin($id)
    {
        $account = Account::findOrFail($id);
        $account->update(['is_admin' => 1]);
        return response()->json(['success' => true, 'message' => 'Đã cấp quyền Admin thành công.']);
    }

    public function removeAdmin($id)
    {
        $account = Account::findOrFail($id);
        $account->update(['is_admin' => 0]);
        return response()->json(['success' => true, 'message' => 'Đã gỡ quyền Admin thành công.']);
    }

    public function addVnd(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $account = Account::findOrFail($id);
        $currentVnd = $account->vnd ?? 0;
        $account->update([
            'vnd' => $currentVnd + $request->amount
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cộng ' . number_format($request->amount) . ' VND thành công.',
            'new_vnd' => number_format($account->fresh()->vnd)
        ]);
    }

    /**
     * Web Shop Items management.
     */
    public function shop()
    {
        $shopItems = WebShopItem::paginate(15);
        return view('admin.shop', compact('shopItems'));
    }

    public function searchTemplates(Request $request)
    {
        $q = $request->q;
        if (empty($q)) {
            return response()->json([]);
        }

        $templates = DB::table('item_template')
            ->select('id', 'NAME', 'TYPE', 'icon_id')
            ->where('id', '=', $q)
            ->orWhere('NAME', 'like', "%{$q}%")
            ->limit(25)
            ->get();

        return response()->json($templates);
    }

    public function shopStore(Request $request)
    {
        $request->validate([
            'item_template_id' => 'required|integer',
            'price_vnd' => 'required|integer|min:0',
            'quantity' => 'required|integer',
            'is_active' => 'required|boolean',
            'image_override' => 'nullable|string'
        ]);

        // Verify if template exists
        $exists = DB::table('item_template')->where('id', $request->item_template_id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'ID vật phẩm game không tồn tại.']);
        }

        WebShopItem::create($request->all());

        return response()->json(['success' => true, 'message' => 'Thêm vật phẩm vào Web Shop thành công.']);
    }

    public function shopUpdate(Request $request, $id)
    {
        $request->validate([
            'price_vnd' => 'required|integer|min:0',
            'quantity' => 'required|integer',
            'is_active' => 'required|boolean',
            'image_override' => 'nullable|string'
        ]);

        $shopItem = WebShopItem::findOrFail($id);
        $shopItem->update($request->all());

        return response()->json(['success' => true, 'message' => 'Cập nhật vật phẩm thành công.']);
    }

    public function shopDelete($id)
    {
        $shopItem = WebShopItem::findOrFail($id);
        $shopItem->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa vật phẩm khỏi Web Shop.']);
    }

    /**
     * Gift Codes management.
     */
    public function giftcodes()
    {
        $giftcodes = GiftCode::paginate(15);
        return view('admin.giftcodes', compact('giftcodes'));
    }

    public function giftcodeStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:gift,name',
            'power' => 'required|integer|min:0',
            'items' => 'required|array|min:1', // items format: [['id' => 123, 'quantity' => 10], ...]
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $itemIds = [];
        $itemQuantities = [];

        foreach ($request->items as $item) {
            // Verify if item_template exists
            $exists = DB::table('item_template')->where('id', $item['id'])->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Vật phẩm ID ' . $item['id'] . ' không tồn tại trong game.']);
            }
            $itemIds[] = (string)$item['id'];
            $itemQuantities[] = (string)$item['quantity'];
        }

        GiftCode::create([
            'name' => trim($request->name),
            'item_id' => $itemIds,
            'item_quantity' => $itemQuantities,
            'power' => (int)$request->power,
            'list_user' => []
        ]);

        return response()->json(['success' => true, 'message' => 'Tạo Gift Code thành công.']);
    }

    public function giftcodeUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:gift,name,' . $id,
            'power' => 'required|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $itemIds = [];
        $itemQuantities = [];

        foreach ($request->items as $item) {
            // Verify if item_template exists
            $exists = DB::table('item_template')->where('id', $item['id'])->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Vật phẩm ID ' . $item['id'] . ' không tồn tại trong game.']);
            }
            $itemIds[] = (string)$item['id'];
            $itemQuantities[] = (string)$item['quantity'];
        }

        $giftcode = GiftCode::findOrFail($id);
        $giftcode->update([
            'name' => trim($request->name),
            'item_id' => $itemIds,
            'item_quantity' => $itemQuantities,
            'power' => (int)$request->power,
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật Gift Code thành công.']);
    }

    public function giftcodeDelete($id)
    {
        $giftcode = GiftCode::findOrFail($id);
        $giftcode->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa Gift Code.']);
    }

    public function itemsLookup(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('item_template');

        if ($search) {
            $query->where('id', '=', $search)
                  ->orWhere('NAME', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $items = $query->orderBy('id', 'asc')->paginate(30);

        return view('admin.items_lookup', compact('items', 'search'));
    }

    /**
     * Download Client Links.
     */
    public function downloads()
    {
        $downloads = DownloadFile::all();
        return view('admin.downloads', compact('downloads'));
    }

    public function downloadStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'version' => 'nullable|string',
            'url' => 'required|url',
            'size' => 'nullable|string',
            'is_active' => 'required|boolean'
        ]);

        DownloadFile::create($request->all());

        return response()->json(['success' => true, 'message' => 'Thêm file tải thành công.']);
    }

    public function downloadUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'version' => 'nullable|string',
            'url' => 'required|url',
            'size' => 'nullable|string',
            'is_active' => 'required|boolean'
        ]);

        $download = DownloadFile::findOrFail($id);
        $download->update($request->all());

        return response()->json(['success' => true, 'message' => 'Cập nhật file tải thành công.']);
    }

    public function downloadDelete($id)
    {
        $download = DownloadFile::findOrFail($id);
        $download->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa file tải.']);
    }

    /**
     * Guides Articles.
     */
    public function posts()
    {
        $posts = Post::where('category', 'guide')->paginate(15);
        return view('admin.posts', compact('posts'));
    }

    public function postStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'up_top' => 'required|boolean'
        ]);

        Post::create([
            'code_post' => mt_rand(100000, 999999), // unique code_post
            'title' => $request->title,
            'description' => $request->description,
            'id' => 1, // Poster admin account id
            'type_post' => 'guide',
            'time_post' => now(),
            'up_top' => $request->up_top,
            'type' => 0,
            'category' => 'guide'
        ]);

        return response()->json(['success' => true, 'message' => 'Tạo bài viết hướng dẫn thành công.']);
    }

    public function postUpdate(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'up_top' => 'required|boolean'
        ]);

        $post = Post::findOrFail($id);
        $post->update([
            'title' => $request->title,
            'description' => $request->description,
            'up_top' => $request->up_top
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật bài viết thành công.']);
    }

    public function postDelete($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa bài viết.']);
    }

    /**
     * Spin Wheel prize items and settings.
     */
    public function spin()
    {
        $spinItems = SpinWheelItem::orderBy('sort_order', 'asc')->get();
        $spinPrice = (int)Setting::get('spin_price_vnd', 10000);
        $spinEnabled = Setting::get('spin_enabled', '1') == '1';

        return view('admin.spin', compact('spinItems', 'spinPrice', 'spinEnabled'));
    }

    public function spinSettings(Request $request)
    {
        $request->validate([
            'spin_price_vnd' => 'required|integer|min:0',
            'spin_enabled' => 'required|boolean'
        ]);

        Setting::set('spin_price_vnd', $request->spin_price_vnd);
        Setting::set('spin_enabled', $request->spin_enabled ? '1' : '0');

        return response()->json(['success' => true, 'message' => 'Cập nhật cài đặt Vòng Quay thành công.']);
    }

    public function spinItemStore(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string',
            'item_template_id' => 'nullable|integer',
            'reward_type' => 'required|string|in:item,vnd,coin',
            'reward_value' => 'required|integer|min:0',
            'ratio' => 'required|numeric|min:0|max:100',
            'color' => 'required|string',
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer'
        ]);

        // Check sum of ratios to keep it safe (must be <= 100)
        $currentSum = SpinWheelItem::where('is_active', 1)->sum('ratio');
        if (($currentSum + $request->ratio) > 100.01) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Tổng tỉ lệ của tất cả các ô trên vòng quay vượt quá 100% (Hiện tại: ' . $currentSum . '%). Vui lòng điều chỉnh lại tỉ lệ.'
            ]);
        }

        if ($request->reward_type === 'item' && $request->item_template_id) {
            $exists = DB::table('item_template')->where('id', $request->item_template_id)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'ID vật phẩm game không tồn tại.']);
            }
        }

        SpinWheelItem::create($request->all());

        return response()->json(['success' => true, 'message' => 'Thêm ô vòng quay thành công.']);
    }

    public function spinItemUpdate(Request $request, $id)
    {
        $request->validate([
            'display_name' => 'required|string',
            'item_template_id' => 'nullable|integer',
            'reward_type' => 'required|string|in:item,vnd,coin',
            'reward_value' => 'required|integer|min:0',
            'ratio' => 'required|numeric|min:0|max:100',
            'color' => 'required|string',
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer'
        ]);

        $item = SpinWheelItem::findOrFail($id);

        // Check ratio sum exclusion
        $currentSum = SpinWheelItem::where('is_active', 1)->where('id', '!=', $id)->sum('ratio');
        if (($currentSum + $request->ratio) > 100.01) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Tổng tỉ lệ của các ô sẽ vượt quá 100% (Hiện tại các ô khác: ' . $currentSum . '%). Vui lòng điều chỉnh lại tỉ lệ.'
            ]);
        }

        if ($request->reward_type === 'item' && $request->item_template_id) {
            $exists = DB::table('item_template')->where('id', $request->item_template_id)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'ID vật phẩm game không tồn tại.']);
            }
        }

        $item->update($request->all());

        return response()->json(['success' => true, 'message' => 'Cập nhật ô vòng quay thành công.']);
    }

    public function spinItemDelete($id)
    {
        $item = SpinWheelItem::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa ô vòng quay.']);
    }

    /**
     * Recent Server Recharges.
     */
    public function recharges()
    {
        $transactions = PaymentTransaction::with('account')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.recharges', compact('transactions'));
    }

    /**
     * Display account sales for admin.
     */
    public function accSales(Request $request)
    {
        $status = $request->input('status');
        $query = WebAccSale::with(['seller', 'buyer']);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $listings = $query->orderBy('created_at', 'desc')->paginate(15);
        $commission = (int)Setting::get('acc_sale_commission', 10);

        return view('admin.acc_sales', compact('listings', 'commission', 'status'));
    }

    /**
     * Update marketplace commission settings.
     */
    public function accSaleSettings(Request $request)
    {
        $request->validate([
            'acc_sale_commission' => 'required|integer|min:0|max:100'
        ]);

        Setting::set('acc_sale_commission', $request->acc_sale_commission);

        return response()->json(['success' => true, 'message' => 'Cập nhật hoa hồng sàn giao dịch thành công.']);
    }

    /**
     * Set sale price for listed account.
     */
    public function accSaleUpdatePrice(Request $request, $id)
    {
        $request->validate([
            'sale_price' => 'nullable|integer|min:0'
        ]);

        $listing = WebAccSale::findOrFail($id);
        $listing->update([
            'sale_price' => $request->sale_price
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật giá bán khuyến mãi thành công.']);
    }

    /**
     * Cancel listed account by admin with a reason.
     */
    public function accSaleCancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:255'
        ]);

        $listing = WebAccSale::findOrFail($id);
        
        if ($listing->status !== 'selling') {
            return response()->json(['success' => false, 'message' => 'Tài khoản không ở trạng thái đang bán.']);
        }

        DB::beginTransaction();
        try {
            $listing->update([
                'status' => 'cancelled',
                'cancel_reason' => $request->cancel_reason
            ]);

            // Restore game account password and status unban
            $soldAcc = Account::where('username', $listing->username_sold)->first();
            if ($soldAcc) {
                DB::table('account')
                    ->where('id', $soldAcc->id)
                    ->update([
                        'password' => $listing->password_sold,
                        'ban' => 0
                    ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đã hủy đăng bán tài khoản này và trả về trạng thái hoạt động bình thường.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve seller payout.
     */
    public function accSaleApprovePayout(Request $request, $id)
    {
        $listing = WebAccSale::findOrFail($id);

        if ($listing->status !== 'sold' || $listing->payout_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không ở trạng thái chờ duyệt thanh toán.']);
        }

        $finalPrice = $listing->display_price;
        $commissionPercent = (int)Setting::get('acc_sale_commission', 10);
        $commissionAmount = (int)($finalPrice * ($commissionPercent / 100));
        $payoutAmount = $finalPrice - $commissionAmount;

        DB::beginTransaction();
        try {
            // Credit seller web balance (vnd)
            $seller = Account::findOrFail($listing->seller_id);
            $seller->update([
                'vnd' => ($seller->vnd ?? 0) + $payoutAmount
            ]);

            // Mark listing payout as approved
            $listing->update([
                'payout_status' => 'approved'
            ]);

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Duyệt thanh toán thành công! Đã cộng ' . number_format($payoutAmount) . ' đ (phí sàn ' . $commissionPercent . '%) vào ví của người bán.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Refund buyer and return account to seller.
     */
    public function accSaleRefund(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:255'
        ]);

        $listing = WebAccSale::findOrFail($id);

        if ($listing->status !== 'sold' || $listing->payout_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không ở trạng thái có thể hoàn tiền.']);
        }

        DB::beginTransaction();
        try {
            // Refund buyer VND
            $buyer = Account::findOrFail($listing->buyer_id);
            $buyer->update([
                'vnd' => ($buyer->vnd ?? 0) + $listing->display_price
            ]);

            // Update listing
            $listing->update([
                'status' => 'cancelled',
                'payout_status' => 'refunded',
                'cancel_reason' => $request->cancel_reason
            ]);

            // Restore seller game account original credentials and unban
            $soldAcc = Account::where('username', $listing->username_sold)->first();
            if ($soldAcc) {
                DB::table('account')
                    ->where('id', $soldAcc->id)
                    ->update([
                        'password' => $listing->password_sold,
                        'ban' => 0
                    ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đơn hàng đã được hủy, hoàn tiền ' . number_format($listing->display_price) . ' đ cho người mua và trả tài khoản về người bán thành công.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
