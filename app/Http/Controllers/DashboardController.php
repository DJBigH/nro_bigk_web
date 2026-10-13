<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use App\Models\Post;
use App\Models\DownloadFile;
use App\Models\WebChestItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Show home page.
     */
    public function home()
    {
        // Get top 5 guide posts to show on home page
        $posts = Post::where('category', 'guide')
            ->orderBy('up_top', 'desc')
            ->orderBy('time_post', 'desc')
            ->limit(5)
            ->get();

        return view('home', compact('posts'));
    }

    /**
     * Show player dashboard.
     */
    public function dashboard()
    {
        $account = Auth::user();
        // Load the account's players
        $players = Player::where('account_id', $account->id)->get();
        
        // Load all available gift codes from the game 'gift' table
        $gifts = DB::table('gift')->get();

        // Load the web chest items for this account
        $chestItems = WebChestItem::where('account_id', $account->id)->get();

        return view('dashboard', compact('account', 'players', 'gifts', 'chestItems'));
    }

    /**
     * Change password of current logged in user.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:4|confirmed',
        ], [
            'old_password.required' => 'Vui lòng nhập mật khẩu cũ.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải chứa ít nhất 4 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.'
        ]);

        $account = Auth::user();

        // Verify old password
        $pwMatch = false;
        if (Hash::check($request->old_password, $account->password)) {
            $pwMatch = true;
        } elseif (md5($request->old_password) === $account->password) {
            $pwMatch = true;
        } elseif ($request->old_password === $account->password) {
            $pwMatch = true;
        }

        if (!$pwMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu cũ không chính xác.'
            ]);
        }

        // Save new plain text password
        DB::table('account')
            ->where('id', $account->id)
            ->update([
                'password' => $request->password
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }

    /**
     * Show download client page.
     */
    public function download()
    {
        $downloads = DownloadFile::where('is_active', 1)->get();
        return view('download', compact('downloads'));
    }

    /**
     * Show guides list page.
     */
    public function guide()
    {
        $posts = Post::where('category', 'guide')
            ->orderBy('up_top', 'desc')
            ->orderBy('time_post', 'desc')
            ->paginate(10);

        return view('guide.index', compact('posts'));
    }

    /**
     * Show specific guide details.
     */
    public function guideShow($id)
    {
        $post = Post::findOrFail($id);
        return view('guide.show', compact('post'));
    }

    /**
     * Show rankings page.
     */
    public function ranking(Request $request)
    {
        $type = request('type', 'power'); // 'power', 'task', 'recharge'

        $rankings = [];

        if ($type === 'power') {
            // TOP Power
            $rankings = DB::select("
                SELECT p.name, p.head, p.gender, p.capTT,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(TRIM(BOTH '[]' FROM p.data_point), ',', 2), ',', -1) AS UNSIGNED) AS value
                FROM player p
                ORDER BY value DESC
                LIMIT 20
            ");
        } elseif ($type === 'task') {
            // TOP Tasks
            $rankings = DB::select("
                SELECT p.name, p.head, p.gender, p.capTT as value
                FROM player p
                ORDER BY value DESC
                LIMIT 20
            ");
        } elseif ($type === 'recharge') {
            // TOP Recharge (tongnap) from account
            // Map account username to player name if exists, else show username
            $rankings = DB::select("
                SELECT a.username as name, a.tongnap as value, p.head, p.gender
                FROM account a
                LEFT JOIN player p ON p.account_id = a.id
                WHERE a.active = 1
                ORDER BY value DESC
                LIMIT 20
            ");

            // Filter duplicates if account has multiple characters, select the first character
            $filtered = [];
            $seen = [];
            foreach ($rankings as $row) {
                if (!in_array($row->name, $seen)) {
                    $seen[] = $row->name;
                    $filtered[] = $row;
                }
            }
            $rankings = $filtered;
        }

        return view('ranking', compact('rankings', 'type'));
    }

    /**
     * Withdraw a single item from the web chest to the in-game inventory.
     */
    public function withdrawChestItem(Request $request, $id)
    {
        $account = Auth::user();
        $item = WebChestItem::where('account_id', $account->id)->findOrFail($id);

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
                'message' => '⚠️ Tài khoản đang online trong game. Vui lòng THOÁT GAME hoàn toàn trước khi rút đồ.'
            ]);
        }

        // 3. Decode items_bag and find empty slot
        $itemsBag = json_decode($player->items_bag, true);
        if (!is_array($itemsBag)) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu hành trang nhân vật không hợp lệ.'
            ]);
        }

        $emptySlotIndex = -1;
        foreach ($itemsBag as $index => $slotStr) {
            $slotData = json_decode($slotStr, true);
            if (is_array($slotData) && isset($slotData[0]) && $slotData[0] == -1) {
                $emptySlotIndex = $index;
                break;
            }
        }

        if ($emptySlotIndex === -1) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Hành trang nhân vật đã đầy. Vui lòng cất bớt đồ vào rương rồi thử lại.'
            ]);
        }

        // 4. Format item options and save
        DB::beginTransaction();
        try {
            $formattedOptionsStr = $this->formatItemOptions($item->options ?? []);
            
            $newItemSlot = [
                (int)$item->item_template_id,
                (int)$item->quantity,
                $formattedOptionsStr,
                round(microtime(true) * 1000)
            ];

            $itemsBag[$emptySlotIndex] = json_encode($newItemSlot);

            DB::table('player')
                ->where('id', $player->id)
                ->update(['items_bag' => json_encode($itemsBag)]);

            // Delete item from chest
            $item->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Rút vật phẩm "' . ($item->template->NAME ?? 'Vật phẩm') . '" vào hành trang game thành công!'
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
     * Withdraw all items from the web chest to the in-game inventory.
     */
    public function withdrawAllChestItems()
    {
        $account = Auth::user();
        $items = WebChestItem::where('account_id', $account->id)->get();
        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Rương đồ Web của bạn đang trống.'
            ]);
        }

        $player = Player::where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa tạo nhân vật trong game. Vui lòng vào game tạo nhân vật trước.'
            ]);
        }

        // Check if player is online
        $lastLogin = $account->last_time_login ? strtotime($account->last_time_login) : 0;
        $lastLogout = $account->last_time_logout ? strtotime($account->last_time_logout) : 0;
        if ($lastLogin > $lastLogout) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Tài khoản đang online trong game. Vui lòng THOÁT GAME hoàn toàn trước khi rút đồ.'
            ]);
        }

        $itemsBag = json_decode($player->items_bag, true);
        if (!is_array($itemsBag)) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu hành trang nhân vật không hợp lệ.'
            ]);
        }

        // Find empty slots
        $emptySlots = [];
        foreach ($itemsBag as $index => $slotStr) {
            $slotData = json_decode($slotStr, true);
            if (is_array($slotData) && isset($slotData[0]) && $slotData[0] == -1) {
                $emptySlots[] = $index;
            }
        }

        if (count($emptySlots) < $items->count()) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Hành trang của bạn cần ít nhất ' . $items->count() . ' ô trống để rút toàn bộ vật phẩm. Hiện tại chỉ trống ' . count($emptySlots) . ' ô.'
            ]);
        }

        DB::beginTransaction();
        try {
            foreach ($items as $idx => $item) {
                $emptySlotIndex = $emptySlots[$idx];
                $formattedOptionsStr = $this->formatItemOptions($item->options ?? []);
                
                $newItemSlot = [
                    (int)$item->item_template_id,
                    (int)$item->quantity,
                    $formattedOptionsStr,
                    round(microtime(true) * 1000)
                ];

                $itemsBag[$emptySlotIndex] = json_encode($newItemSlot);
                $item->delete();
            }

            DB::table('player')
                ->where('id', $player->id)
                ->update(['items_bag' => json_encode($itemsBag)]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Rút toàn bộ ' . $items->count() . ' vật phẩm vào game thành công!'
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
