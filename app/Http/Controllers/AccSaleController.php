<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use App\Models\WebAccSale;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccSaleController extends Controller
{
    /**
     * Display accounts for sale.
     */
    public function index(Request $request)
    {
        $query = WebAccSale::where('status', 'selling');

        // Filter by price
        if ($request->has('price_range')) {
            $range = $request->price_range;
            if ($range === 'under_50k') {
                $query->where(function($q) {
                    $q->whereNull('sale_price')->where('price', '<', 50000)
                      ->orWhere('sale_price', '<', 50000);
                });
            } elseif ($range === '50k_200k') {
                $query->where(function($q) {
                    $q->whereBetween('price', [50000, 200000])->whereNull('sale_price')
                      ->orWhereBetween('sale_price', [50000, 200000]);
                });
            } elseif ($range === 'over_200k') {
                $query->where(function($q) {
                    $q->whereNull('sale_price')->where('price', '>', 200000)
                      ->orWhere('sale_price', '>', 200000);
                });
            }
        }

        // Search description
        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('acc_sales.index', compact('listings'));
    }

    /**
     * Show form to sell an account.
     */
    public function sell()
    {
        // Warn if seller doesn't have a main character to prevent dummy listings
        $playerCount = Player::where('account_id', Auth::id())->count();
        return view('acc_sales.sell', compact('playerCount'));
    }

    /**
     * Store listed account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username_sold' => 'required|string',
            'password_sold' => 'required|string',
            'price' => 'required|integer|min:1000',
            'description' => 'required|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'username_sold.required' => 'Vui lòng nhập tài khoản game.',
            'password_sold.required' => 'Vui lòng nhập mật khẩu game.',
            'price.required' => 'Vui lòng nhập giá tiền.',
            'price.min' => 'Giá tối thiểu là 1,000 đ.',
            'images.max' => 'Bạn chỉ được tải lên tối đa 5 hình ảnh.'
        ]);

        // 1. Verify account exists in DB and credentials match
        $soldAcc = Account::where('username', $request->username_sold)->first();
        if (!$soldAcc) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản game không tồn tại trên hệ thống.'
            ]);
        }

        // Verify password
        $pwMatch = false;
        if (Hash::check($request->password_sold, $soldAcc->password)) {
            $pwMatch = true;
        } elseif (md5($request->password_sold) === $soldAcc->password) {
            $pwMatch = true;
        } elseif ($request->password_sold === $soldAcc->password) {
            $pwMatch = true;
        }

        if (!$pwMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu tài khoản game không chính xác.'
            ]);
        }

        // 2. Check if account is already listed
        $alreadySelling = WebAccSale::where('username_sold', $request->username_sold)
            ->where('status', 'selling')
            ->exists();
        if ($alreadySelling) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản này đang được đăng bán trên sàn.'
            ]);
        }

        // Check if the account has players
        $hasPlayers = Player::where('account_id', $soldAcc->id)->exists();
        if (!$hasPlayers) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản này chưa có nhân vật game. Vui lòng tạo nhân vật trước khi bán.'
            ]);
        }

        // 3. Process images upload
        $imagePaths = [];
        if ($request->hasFile('images')) {
            $uploadPath = public_path('uploads/acc_sales');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            foreach ($request->file('images') as $file) {
                $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $imagePaths[] = '/uploads/acc_sales/' . $filename;
            }
        }

        // Fallback placeholder image if none uploaded
        if (empty($imagePaths)) {
            $imagePaths[] = '/images/no_image.png';
        }

        // 4. Create transaction
        DB::beginTransaction();
        try {
            // Suspends account in game by changing password to random temporary password & setting ban = 1
            $tempPass = Str::random(32);
            DB::table('account')
                ->where('id', $soldAcc->id)
                ->update([
                    'password' => $tempPass,
                    'ban' => 1
                ]);

            // Save listing info
            WebAccSale::create([
                'seller_id' => Auth::id(),
                'username_sold' => $request->username_sold,
                'password_sold' => $request->password_sold, // Save original password to give to buyer later
                'price' => $request->price,
                'description' => $request->description,
                'images' => $imagePaths,
                'status' => 'selling',
                'payout_status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đăng bán tài khoản thành công! Tài khoản game đã tự động được khóa để bảo vệ vật phẩm.',
                'redirect' => route('acc_sales.history')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show details of account listing.
     */
    public function show($id)
    {
        $listing = WebAccSale::findOrFail($id);
        
        // Find player details of listing
        $soldAcc = Account::where('username', $listing->username_sold)->first();
        $players = $soldAcc ? Player::where('account_id', $soldAcc->id)->get() : collect();

        return view('acc_sales.show', compact('listing', 'players'));
    }

    /**
     * Purchase account.
     */
    public function buy(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:web_acc_sales,id'
        ]);

        $buyer = Auth::user();
        $listing = WebAccSale::findOrFail($request->listing_id);

        if ($listing->status !== 'selling') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản này không còn trong trạng thái đăng bán.'
            ]);
        }

        if ($listing->seller_id == $buyer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự mua tài khoản của chính mình.'
            ]);
        }

        $finalPrice = $listing->display_price;

        if ($buyer->vnd < $finalPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Số dư tài khoản của bạn không đủ. Vui lòng nạp thêm VND.'
            ]);
        }

        // Process purchase
        DB::beginTransaction();
        try {
            // Deduct buyer VND
            DB::table('account')
                ->where('id', $buyer->id)
                ->update(['vnd' => $buyer->vnd - $finalPrice]);

            // Update listing
            $listing->update([
                'buyer_id' => $buyer->id,
                'status' => 'sold',
                'payout_status' => 'pending' // Escrow: pending admin payout approval
            ]);

            // Reset password in game database back to original password, and set ban = 0 (unban)
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

            return response()->json([
                'success' => true,
                'message' => 'Mua tài khoản thành công! Bạn có thể xem thông tin đăng nhập trong Lịch sử mua hàng.',
                'redirect' => route('acc_sales.history')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel listed account by seller.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:web_acc_sales,id'
        ]);

        $listing = WebAccSale::findOrFail($request->listing_id);

        if ($listing->seller_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ]);
        }

        if ($listing->status !== 'selling') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản này đã được bán hoặc đã hủy trước đó.'
            ]);
        }

        DB::beginTransaction();
        try {
            // Change status to cancelled
            $listing->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Người bán tự hủy đăng bán.'
            ]);

            // Restore account password and unban
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

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy đăng bán tài khoản. Tài khoản game đã được mở khóa hoạt động bình thường.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View history of purchases and sales.
     */
    public function history()
    {
        $account = Auth::user();

        // Accounts purchased by this user
        $purchases = WebAccSale::where('buyer_id', $account->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Accounts listed by this user
        $sales = WebAccSale::where('seller_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('acc_sales.history', compact('purchases', 'sales'));
    }
}
