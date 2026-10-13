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
use App\Models\WebChestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminController extends Controller
{
    /**
     * Helper to get safe per_page value (default 5, allows 5, 10, 15, 20)
     */
    protected function getPerPage(Request $request, $default = 5)
    {
        $perPage = (int) $request->input('per_page', $default);
        return in_array($perPage, [5, 10, 15, 20]) ? $perPage : $default;
    }

    /**
     * Admin Dashboard index.
     */
    public function dashboard()
    {
        $serverStats = $this->callGameApi('/api/server/stats', [], 'GET');

        $stats = [
            'total_users' => Account::count(),
            'server_online' => $serverStats['success'] ?? false,
            'online_count' => $serverStats['online_count'] ?? 0,
            'used_memory_mb' => $serverStats['used_memory_mb'] ?? 0,
            'max_memory_mb' => $serverStats['max_memory_mb'] ?? 0,
            'total_purchases' => WebShopPurchase::count(),
            'active_shop_items' => WebShopItem::where('is_active', 1)->count(),
            'total_giftcodes' => GiftCode::count(),
            'spin_wheel_items' => SpinWheelItem::count(),
        ];

        return view('admin.dashboard', compact('stats', 'serverStats'));
    }

    /**
     * Realtime Dashboard stats polling API.
     */
    public function dashboardData()
    {
        $serverStats = $this->callGameApi('/api/server/stats', [], 'GET');

        $stats = [
            'total_users' => Account::count(),
            'server_online' => $serverStats['success'] ?? false,
            'online_count' => $serverStats['online_count'] ?? 0,
            'used_memory_mb' => $serverStats['used_memory_mb'] ?? 0,
            'max_memory_mb' => $serverStats['max_memory_mb'] ?? 0,
            'total_giftcodes' => GiftCode::count(),
            'rate_exp' => $serverStats['rate_exp'] ?? 2,
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'players' => $serverStats['players'] ?? []
        ]);
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
        
        $perPage = $this->getPerPage($request, 5);
        $accounts = $query->paginate($perPage)->appends($request->query());
        return view('admin.players', compact('accounts', 'search', 'perPage'));
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

    /**
     * Call Game Server API (Port 14446)
     */
    private function callGameApi($endpoint, $data = [], $method = 'POST')
    {
        $token = "BIGK_ADMIN_TOKEN_2026";
        $url = "http://127.0.0.1:14446" . $endpoint;
        $data['token'] = $token;

        try {
            $ch = curl_init();
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 300);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 800);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                return json_decode($response, true);
            }
        } catch (\Exception $e) {
            // Server offline or connection failed
        }
        return ['success' => false, 'message' => 'Game Server (port 14446) đang bảo trì hoặc chưa bật!'];
    }

    /**
     * Realtime Buff Player (Thỏi Vàng, Hồng Ngọc, Item)
     */
    public function buffPlayer(Request $request, $id)
    {
        $request->validate([
            'ruby' => 'nullable|integer|min:0',
            'thoi_vang' => 'nullable|integer|min:0',
            'item_id' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0'
        ]);

        $account = Account::findOrFail($id);
        $res = $this->callGameApi('/api/buff', [
            'username' => $account->username,
            'ruby' => (int) ($request->ruby ?? 0),
            'thoi_vang' => (int) ($request->thoi_vang ?? 0),
            'item_id' => (int) ($request->item_id ?? -1),
            'quantity' => (int) ($request->quantity ?? 0),
        ]);

        return response()->json($res);
    }

    /**
     * Realtime Set Player Points (lên tới 50 TỶ HP/MP/Dame, 200 TỶ Sức Mạnh)
     */
    public function setPlayerPoints(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $data = ['username' => $account->username];

        if ($request->filled('hp')) $data['hp'] = (int) $request->hp;
        if ($request->filled('mp')) $data['mp'] = (int) $request->mp;
        if ($request->filled('dame')) $data['dame'] = (int) $request->dame;
        if ($request->filled('def')) $data['def'] = (int) $request->def;
        if ($request->filled('crit')) $data['crit'] = (int) $request->crit;
        if ($request->filled('power')) $data['power'] = (int) $request->power;
        if ($request->filled('tiem_nang')) $data['tiem_nang'] = (int) $request->tiem_nang;

        $res = $this->callGameApi('/api/player/set-point', $data);
        return response()->json($res);
    }

    /**
     * Kick Player
     */
    public function kickPlayer($id)
    {
        $account = Account::findOrFail($id);
        $res = $this->callGameApi('/api/player/kick', ['username' => $account->username]);
        return response()->json($res);
    }

    /**
     * Realtime Server Notice
     */
    public function sendServerNotify(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $res = $this->callGameApi('/api/server/notify', ['message' => $request->message]);
        return response()->json($res);
    }

    /**
     * Realtime Maintenance Countdown
     */
    public function startMaintenance(Request $request)
    {
        $seconds = (int) ($request->seconds ?? 30);
        $res = $this->callGameApi('/api/server/maintenance', ['seconds' => $seconds]);
        return response()->json($res);
    }

    /**
     * Get Realtime Server Stats
     */
    public function getServerStats()
    {
        $res = $this->callGameApi('/api/server/stats', [], 'GET');
        return response()->json($res);
    }

    /**
     * Realtime Boss Management
     */
    public function bosses(Request $request)
    {
        $res = $this->callGameApi('/api/boss/list', [], 'GET');
        $allBosses = $res['bosses'] ?? [];
        $statusFilter = $request->input('status', 'all');

        $filteredBosses = $allBosses;
        if ($statusFilter === 'live') {
            $filteredBosses = array_values(array_filter($allBosses, function($b) {
                return ($b['status'] ?? 'LIVE') === 'LIVE';
            }));
        } elseif ($statusFilter === 'die') {
            $filteredBosses = array_values(array_filter($allBosses, function($b) {
                return ($b['status'] ?? 'LIVE') !== 'LIVE';
            }));
        }

        $perPage = $this->getPerPage($request, 5);
        $currentPage = (int) $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($filteredBosses, $offset, $perPage);

        $bosses = new LengthAwarePaginator(
            $items,
            count($filteredBosses),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.bosses', compact('bosses', 'allBosses', 'statusFilter', 'perPage'));
    }

    /**
     * Realtime Boss data polling API.
     */
    public function bossesData(Request $request)
    {
        $res = $this->callGameApi('/api/boss/list', [], 'GET');
        $allBosses = $res['bosses'] ?? [];
        $statusFilter = $request->input('status', 'all');

        $filteredBosses = $allBosses;
        if ($statusFilter === 'live') {
            $filteredBosses = array_values(array_filter($allBosses, function($b) {
                return ($b['status'] ?? 'LIVE') === 'LIVE';
            }));
        } elseif ($statusFilter === 'die') {
            $filteredBosses = array_values(array_filter($allBosses, function($b) {
                return ($b['status'] ?? 'LIVE') !== 'LIVE';
            }));
        }

        $perPage = $this->getPerPage($request, 5);
        $currentPage = (int) $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($filteredBosses, $offset, $perPage);

        $liveCount = 0;
        $dieCount = 0;
        foreach ($allBosses as $b) {
            if (($b['status'] ?? 'LIVE') === 'LIVE') $liveCount++;
            else $dieCount++;
        }

        return response()->json([
            'success' => true,
            'bosses' => $items,
            'total' => count($filteredBosses),
            'liveCount' => $liveCount,
            'dieCount' => $dieCount,
            'totalCount' => count($allBosses),
            'currentPage' => $currentPage,
            'lastPage' => (int) ceil(count($filteredBosses) / $perPage)
        ]);
    }

    public function bossSpawn(Request $request)
    {
        $request->validate(['boss_id' => 'required|integer']);
        $res = $this->callGameApi('/api/boss/spawn', ['boss_id' => (int) $request->boss_id]);
        return response()->json($res);
    }

    public function bossKill(Request $request)
    {
        $request->validate(['boss_id' => 'required|integer']);
        $res = $this->callGameApi('/api/boss/kill', ['boss_id' => (int) $request->boss_id]);
        return response()->json($res);
    }

    /**
     * Realtime Item Inspector (Supports both RAM Online and Database Offline)
     */
    public function playerItems($id)
    {
        $account = Account::findOrFail($id);
        
        $allOptions = DB::table('item_option_template')
            ->select('id', DB::raw('COALESCE(NAME, name) as name'))
            ->orderBy('id', 'asc')
            ->get();

        // 1. Thử gọi Game Server API nếu player đang online
        $res = $this->callGameApi('/api/player/items', ['username' => $account->username], 'GET');
        if (!empty($res['success'])) {
            $res['source'] = 'ram';
            $res['allOptions'] = $allOptions;
            return response()->json($res);
        }

        // 2. Nếu không online hoặc Game Server chưa bật, đọc trực tiếp từ MySQL Database (offline check)
        $player = DB::table('player')->where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa tạo nhân vật trong game!'
            ]);
        }

        $itemTemplates = DB::table('item_template')->select('id', 'NAME', 'icon_id')->get()->keyBy('id');
        $optTemplates = DB::table('item_option_template')->pluck('NAME', 'id')->toArray();

        $parseItems = function($rawJson) use ($itemTemplates, $optTemplates) {
            $slots = json_decode($rawJson, true);
            if (!is_array($slots)) return [];
            $items = [];
            foreach ($slots as $idx => $sStr) {
                $s = is_string($sStr) ? json_decode($sStr, true) : $sStr;
                if (!is_array($s) || count($s) < 3) continue;
                $itemId = (int) $s[0];
                if ($itemId === -1) continue;
                $qty = (int) $s[1];
                $tpl = $itemTemplates[$itemId] ?? null;

                $options = [];
                $optsRaw = is_string($s[2]) ? json_decode($s[2], true) : $s[2];
                if (is_array($optsRaw)) {
                    foreach ($optsRaw as $oStr) {
                        $o = is_string($oStr) ? json_decode($oStr, true) : $oStr;
                        if (is_array($o) && count($o) >= 2) {
                            $optId = (int) $o[0];
                            $param = (int) $o[1];
                            $optNameTpl = $optTemplates[$optId] ?? ("Option #{$optId}");
                            $formattedName = str_replace('#', (string)$param, $optNameTpl);
                            $options[] = [
                                'id' => $optId,
                                'param' => $param,
                                'name' => $formattedName
                            ];
                        }
                    }
                }

                $items[] = [
                    'index' => (int) $idx,
                    'templateId' => $itemId,
                    'name' => $tpl ? $tpl->NAME : ("Vật phẩm #{$itemId}"),
                    'iconId' => $tpl ? $tpl->icon_id : 0,
                    'quantity' => $qty,
                    'options' => $options
                ];
            }
            return $items;
        };

        $bodyItems = $parseItems($player->items_body ?? '[]');
        $bagItems = $parseItems($player->items_bag ?? '[]');

        return response()->json([
            'success' => true,
            'source' => 'db',
            'username' => $account->username,
            'body' => $bodyItems,
            'bag' => $bagItems,
            'allOptions' => $allOptions
        ]);
    }

    public function playerItemUpdate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $type = $request->input('type', 'bag');
        $index = (int) $request->input('index', 0);
        $quantity = (int) $request->input('quantity', 1);
        $optionsInput = $request->input('options', []);

        // 1. Thử gọi Game Server API nếu player online
        $res = $this->callGameApi('/api/player/item/update', [
            'username' => $account->username,
            'type' => $type,
            'index' => $index,
            'quantity' => $quantity,
            'options' => $optionsInput
        ]);

        if (!empty($res['success'])) {
            return response()->json($res);
        }

        // 2. Nếu offline, cập nhật trực tiếp vào MySQL database
        $column = ($type === 'body') ? 'items_body' : 'items_bag';
        $player = DB::table('player')->where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy nhân vật trong Database!']);
        }

        $slots = json_decode($player->{$column}, true);
        if (!is_array($slots) || !isset($slots[$index])) {
            return response()->json(['success' => false, 'message' => "Không tìm thấy ô #{$index} trong {$type}!"]);
        }

        $s = is_string($slots[$index]) ? json_decode($slots[$index], true) : $slots[$index];
        if (!is_array($s) || $s[0] == -1) {
            return response()->json(['success' => false, 'message' => "Ô #{$index} đang trống!"]);
        }

        $s[1] = $quantity;
        $newOptsArr = [];
        foreach ($optionsInput as $opt) {
            $newOptsArr[] = "[" . (int)$opt['id'] . "," . (int)$opt['param'] . "]";
        }
        $s[2] = json_encode($newOptsArr);
        $slots[$index] = json_encode($s);

        DB::table('player')->where('account_id', $account->id)->update([
            $column => json_encode($slots)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu thay đổi món đồ vào Database thành công (nhân vật offline)!'
        ]);
    }

    public function playerItemDelete(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $type = $request->input('type', 'bag');
        $index = (int) $request->input('index', 0);

        // 1. Thử gọi Game Server API nếu player online
        $res = $this->callGameApi('/api/player/item/delete', [
            'username' => $account->username,
            'type' => $type,
            'index' => $index
        ]);

        if (!empty($res['success'])) {
            return response()->json($res);
        }

        // 2. Nếu offline, xóa trực tiếp khỏi MySQL database
        $column = ($type === 'body') ? 'items_body' : 'items_bag';
        $player = DB::table('player')->where('account_id', $account->id)->first();
        if (!$player) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy nhân vật trong Database!']);
        }

        $slots = json_decode($player->{$column}, true);
        if (!is_array($slots) || !isset($slots[$index])) {
            return response()->json(['success' => false, 'message' => "Không tìm thấy ô #{$index} trong {$type}!"]);
        }

        $slots[$index] = '[-1,0,"[]",' . (time() * 1000) . ']';

        DB::table('player')->where('account_id', $account->id)->update([
            $column => json_encode($slots)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa món đồ khỏi Database thành công (nhân vật offline)!'
        ]);
    }

    /**
     * Update EXP Server Config
     */
    public function serverConfigUpdate(Request $request)
    {
        $request->validate(['rate_exp' => 'required|integer|min:1|max:100']);
        $res = $this->callGameApi('/api/server/config', ['rate_exp' => (int) $request->rate_exp]);
        return response()->json($res);
    }

    /**
     * Realtime Bot Management
     */
    public function bots(Request $request)
    {
        $res = $this->callGameApi('/api/bot/status', [], 'GET');
        $botCount = $res['bot_count'] ?? 0;
        $chatEnabled = $res['chat_enabled'] ?? true;
        $allBots = $res['bots'] ?? [];

        // Enrich bot map names using database
        try {
            $mapNames = DB::table('map_template')->pluck('NAME', 'id')->toArray();
            foreach ($allBots as &$b) {
                $mid = $b['mapId'] ?? -1;
                if (isset($mapNames[$mid])) {
                    $b['mapName'] = $mapNames[$mid];
                } elseif (empty($b['mapName'])) {
                    $b['mapName'] = $mid >= 0 ? "Bản đồ #{$mid}" : "Chưa vào Map";
                }
            }
        } catch (\Exception $e) {}

        $perPage = $this->getPerPage($request, 5);
        $currentPage = (int) $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($allBots, $offset, $perPage);

        $bots = new LengthAwarePaginator(
            $items,
            count($allBots),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.bots', compact('botCount', 'chatEnabled', 'bots', 'allBots', 'perPage'));
    }

    public function botSpawn(Request $request)
    {
        $count = (int) ($request->count ?? 5);
        $res = $this->callGameApi('/api/bot/spawn', ['count' => $count]);
        if (!isset($res['success']) && isset($res['status'])) {
            $res['success'] = ($res['status'] === 'success');
        }
        return response()->json($res);
    }

    public function botStop(Request $request)
    {
        $payload = [];
        if ($request->has('bot_id')) {
            $payload['bot_id'] = (int) $request->bot_id;
        } elseif ($request->has('bot_ids') && is_array($request->bot_ids)) {
            $payload['bot_ids'] = array_map('intval', $request->bot_ids);
        } elseif ($request->has('count')) {
            $payload['count'] = (int) $request->count;
        }
        $res = $this->callGameApi('/api/bot/stop', $payload);
        if (!isset($res['success']) && isset($res['status'])) {
            $res['success'] = ($res['status'] === 'success');
        }
        return response()->json($res);
    }

    public function botToggleChat(Request $request)
    {
        $enabled = (bool) $request->enabled;
        $res = $this->callGameApi('/api/bot/toggle-chat', ['enabled' => $enabled]);
        return response()->json($res);
    }

    /**
     * Realtime Combine Rate Management
     */
    public function combineRate()
    {
        $res = $this->callGameApi('/api/server/combine-rate', [], 'GET');
        $rates = $res['rates'] ?? [80.0, 50.0, 20.0, 10.0, 5.0, 2.0, 1.0, 0.5];
        return view('admin.combine_rate', compact('rates'));
    }

    public function combineRateUpdate(Request $request)
    {
        $rates = $request->input('rates', []);
        $res = $this->callGameApi('/api/server/combine-rate', ['rates' => $rates]);
        return response()->json($res);
    }

    /**
     * Web Shop Items management.
     */
    public function shop(Request $request)
    {
        $perPage = $this->getPerPage($request, 5);
        $shopItems = WebShopItem::orderBy('id', 'desc')->paginate($perPage)->appends($request->query());
        return view('admin.shop', compact('shopItems', 'perPage'));
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

    public function searchOptionTemplates(Request $request)
    {
        $q = $request->q;
        if (empty($q)) {
            return response()->json([]);
        }

        $options = DB::table('item_option_template')
            ->select('id', 'NAME')
            ->where('id', '=', $q)
            ->orWhere('NAME', 'like', "%{$q}%")
            ->limit(25)
            ->get();

        return response()->json($options);
    }

    public function shopStore(Request $request)
    {
        $request->validate([
            'item_template_id' => 'required|integer',
            'price_vnd' => 'required|integer|min:0',
            'quantity' => 'required|integer',
            'is_active' => 'required|boolean',
            'image_override' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.id' => 'required|integer',
            'options.*.value' => 'required|integer',
        ]);

        // Verify if template exists
        $exists = DB::table('item_template')->where('id', $request->item_template_id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'ID vật phẩm game không tồn tại.']);
        }

        $options = [];
        if ($request->has('options') && is_array($request->options)) {
            foreach ($request->options as $opt) {
                $optExists = DB::table('item_option_template')->where('id', $opt['id'])->exists();
                if (!$optExists) {
                    return response()->json(['success' => false, 'message' => 'Chỉ số ID ' . $opt['id'] . ' không tồn tại trong game.']);
                }
                $options[] = [(int)$opt['id'], (int)$opt['value']];
            }
        }

        WebShopItem::create([
            'item_template_id' => $request->item_template_id,
            'price_vnd' => $request->price_vnd,
            'quantity' => $request->quantity,
            'is_active' => $request->is_active,
            'image_override' => $request->image_override,
            'options' => $options
        ]);

        return response()->json(['success' => true, 'message' => 'Thêm vật phẩm vào Web Shop thành công.']);
    }

    public function shopUpdate(Request $request, $id)
    {
        $request->validate([
            'price_vnd' => 'required|integer|min:0',
            'quantity' => 'required|integer',
            'is_active' => 'required|boolean',
            'image_override' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.id' => 'required|integer',
            'options.*.value' => 'required|integer',
        ]);

        $shopItem = WebShopItem::findOrFail($id);

        $options = [];
        if ($request->has('options') && is_array($request->options)) {
            foreach ($request->options as $opt) {
                $optExists = DB::table('item_option_template')->where('id', $opt['id'])->exists();
                if (!$optExists) {
                    return response()->json(['success' => false, 'message' => 'Chỉ số ID ' . $opt['id'] . ' không tồn tại trong game.']);
                }
                $options[] = [(int)$opt['id'], (int)$opt['value']];
            }
        }

        $shopItem->update([
            'price_vnd' => $request->price_vnd,
            'quantity' => $request->quantity,
            'is_active' => $request->is_active,
            'image_override' => $request->image_override,
            'options' => $options
        ]);

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
    public function giftcodes(Request $request)
    {
        $perPage = $this->getPerPage($request, 5);
        $giftcodes = GiftCode::orderBy('id', 'desc')->paginate($perPage)->appends($request->query());
        return view('admin.giftcodes', compact('giftcodes', 'perPage'));
    }

    public function giftcodeStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:gift,name',
            'power' => 'required|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'items.*.options.*.id' => 'required|integer',
            'items.*.options.*.value' => 'required|integer',
        ]);

        $itemIds = [];
        $itemQuantities = [];
        $itemOptions = [];

        foreach ($request->items as $item) {
            // Verify if item_template exists
            $exists = DB::table('item_template')->where('id', $item['id'])->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Vật phẩm ID ' . $item['id'] . ' không tồn tại trong game.']);
            }
            $itemIds[] = (string)$item['id'];
            $itemQuantities[] = (string)$item['quantity'];

            $optList = [];
            if (isset($item['options']) && is_array($item['options'])) {
                foreach ($item['options'] as $opt) {
                    $optExists = DB::table('item_option_template')->where('id', $opt['id'])->exists();
                    if (!$optExists) {
                        return response()->json(['success' => false, 'message' => 'Chỉ số ID ' . $opt['id'] . ' không tồn tại trong game.']);
                    }
                    $optList[] = [(int)$opt['id'], (int)$opt['value']];
                }
            }
            $itemOptions[] = $optList;
        }

        GiftCode::create([
            'name' => trim($request->name),
            'item_id' => $itemIds,
            'item_quantity' => $itemQuantities,
            'item_options' => $itemOptions,
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
            'items.*.options' => 'nullable|array',
            'items.*.options.*.id' => 'required|integer',
            'items.*.options.*.value' => 'required|integer',
        ]);

        $itemIds = [];
        $itemQuantities = [];
        $itemOptions = [];

        foreach ($request->items as $item) {
            // Verify if item_template exists
            $exists = DB::table('item_template')->where('id', $item['id'])->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Vật phẩm ID ' . $item['id'] . ' không tồn tại trong game.']);
            }
            $itemIds[] = (string)$item['id'];
            $itemQuantities[] = (string)$item['quantity'];

            $optList = [];
            if (isset($item['options']) && is_array($item['options'])) {
                foreach ($item['options'] as $opt) {
                    $optExists = DB::table('item_option_template')->where('id', $opt['id'])->exists();
                    if (!$optExists) {
                        return response()->json(['success' => false, 'message' => 'Chỉ số ID ' . $opt['id'] . ' không tồn tại trong game.']);
                    }
                    $optList[] = [(int)$opt['id'], (int)$opt['value']];
                }
            }
            $itemOptions[] = $optList;
        }

        $giftcode = GiftCode::findOrFail($id);
        $giftcode->update([
            'name' => trim($request->name),
            'item_id' => $itemIds,
            'item_quantity' => $itemQuantities,
            'item_options' => $itemOptions,
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
        $type = $request->input('type');
        $gender = $request->input('gender');
        $perPage = $this->getPerPage($request, 5);

        $query = DB::table('item_template');

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', '=', (int)$search);
                }
                $q->orWhere('NAME', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type !== null && $type !== '') {
            $query->where('TYPE', '=', (int)$type);
        }

        if ($gender !== null && $gender !== '' && $gender !== '-1') {
            $query->where('gender', '=', (int)$gender);
        }

        $totalItems = DB::table('item_template')->count();
        $items = $query->orderBy('id', 'asc')->paginate($perPage)->appends($request->query());

        // Lấy danh sách Type tags kèm số lượng
        $itemTypes = DB::table('type_item')
            ->select('type_item.id', 'type_item.NAME')
            ->orderBy('type_item.id', 'asc')
            ->get();

        $typeCounts = DB::table('item_template')
            ->select('TYPE', DB::raw('count(*) as total'))
            ->groupBy('TYPE')
            ->pluck('total', 'TYPE')
            ->toArray();

        return view('admin.items_lookup', compact('items', 'search', 'type', 'gender', 'perPage', 'totalItems', 'itemTypes', 'typeCounts'));
    }

    /**
     * Download Client Links.
     */
    public function downloads(Request $request)
    {
        $perPage = $this->getPerPage($request, 5);
        $downloads = DownloadFile::orderBy('id', 'asc')->paginate($perPage)->appends($request->query());
        return view('admin.downloads', compact('downloads', 'perPage'));
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
    public function posts(Request $request)
    {
        $perPage = $this->getPerPage($request, 5);
        $posts = Post::where('category', 'guide')->orderBy('id', 'desc')->paginate($perPage)->appends($request->query());
        return view('admin.posts', compact('posts', 'perPage'));
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
    public function recharges(Request $request)
    {
        $perPage = $this->getPerPage($request, 5);
        $transactions = PaymentTransaction::with('account')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.recharges', compact('transactions', 'perPage'));
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
        
        $perPage = $this->getPerPage($request, 5);
        $listings = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());
        $commission = (int)Setting::get('acc_sale_commission', 10);

        return view('admin.acc_sales', compact('listings', 'commission', 'status', 'perPage'));
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

    /**
     * View all Web Chest items.
     */
    public function webChestIndex(Request $request)
    {
        $search = $request->input('search');
        
        $query = WebChestItem::with('account');
        if ($search) {
            $query->whereHas('account', function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%");
            });
        }
        
        $perPage = $this->getPerPage($request, 5);
        $chestItems = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());
        return view('admin.web_chest', compact('chestItems', 'search', 'perPage'));
    }

    /**
     * Send an item directly to a user's web chest.
     */
    public function webChestSend(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'item_template_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array',
            'options.*.id' => 'required|integer',
            'options.*.value' => 'required|integer',
        ]);

        $account = Account::where('username', $request->username)->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Tài khoản không tồn tại.']);
        }

        // Verify if template exists
        $exists = DB::table('item_template')->where('id', $request->item_template_id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'ID vật phẩm game không tồn tại.']);
        }

        $options = [];
        if ($request->has('options') && is_array($request->options)) {
            foreach ($request->options as $opt) {
                $optExists = DB::table('item_option_template')->where('id', $opt['id'])->exists();
                if (!$optExists) {
                    return response()->json(['success' => false, 'message' => 'Chỉ số ID ' . $opt['id'] . ' không tồn tại trong game.']);
                }
                $options[] = [(int)$opt['id'], (int)$opt['value']];
            }
        }

        WebChestItem::create([
            'account_id' => $account->id,
            'item_template_id' => $request->item_template_id,
            'quantity' => $request->quantity,
            'options' => $options
        ]);

        return response()->json(['success' => true, 'message' => 'Đã gửi vật phẩm vào Rương Web của tài khoản "' . $request->username . '" thành công!']);
    }

    /**
     * Delete an item from a user's web chest.
     */
    public function webChestDelete($id)
    {
        $item = WebChestItem::findOrFail($id);
        $itemName = $item->template->NAME ?? 'Vật phẩm';
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa vật phẩm "' . $itemName . '" khỏi Rương Web thành công.']);
    }

    /**
     * Nhiệm Vụ Bò Mộng & Thành Tựu
     */
    public function achievements(Request $request)
    {
        $perPage = $this->getPerPage($request, 5);
        $achievements = DB::table('archive_template')->orderBy('id', 'asc')->paginate($perPage)->appends($request->query());
        return view('admin.achievements', compact('achievements', 'perPage'));
    }

    public function achievementStore(Request $request)
    {
        $request->validate([
            'info1' => 'required|string',
            'info2' => 'required|string',
            'money' => 'required|integer|min:0',
        ]);

        $maxId = DB::table('archive_template')->max('id');
        $newId = ($maxId !== null) ? $maxId + 1 : 0;

        DB::table('archive_template')->insert([
            'id' => $newId,
            'info1' => $request->info1,
            'info2' => $request->info2,
            'money' => (int) $request->money,
        ]);

        return response()->json(['success' => true, 'message' => 'Thêm mốc thành tựu mới thành công!']);
    }

    public function achievementUpdate(Request $request, $id)
    {
        $request->validate([
            'info1' => 'required|string',
            'info2' => 'required|string',
            'money' => 'required|integer|min:0',
        ]);

        DB::table('archive_template')->where('id', $id)->update([
            'info1' => $request->info1,
            'info2' => $request->info2,
            'money' => (int) $request->money,
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật mốc thành tựu thành công!']);
    }

    public function achievementDelete($id)
    {
        DB::table('archive_template')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa mốc thành tựu thành công!']);
    }

    /**
     * Quản Lý Rơi Đồ Quái (Mob Drops)
     */
    public function drops(Request $request)
    {
        $mobId = $request->input('mob_id');
        $mapId = $request->input('map_id');

        $query = DB::table('mob_drop')
            ->leftJoin('item_template', 'mob_drop.item_id', '=', 'item_template.id')
            ->leftJoin('map_template', 'mob_drop.map_id', '=', 'map_template.id')
            ->select('mob_drop.*', 'item_template.NAME as item_name', 'item_template.icon_id', 'map_template.name as map_name');

        if ($mobId !== null && $mobId !== '') {
            $query->where('mob_drop.mob_id', (int) $mobId);
        }
        if ($mapId !== null && $mapId !== '') {
            $query->where('mob_drop.map_id', (int) $mapId);
        }

        $perPage = $this->getPerPage($request, 5);
        $drops = $query->orderBy('mob_drop.id', 'desc')->paginate($perPage)->appends($request->query());
        $maps = DB::table('map_template')->select('id', 'name')->orderBy('id', 'asc')->get();

        return view('admin.drops', compact('drops', 'maps', 'mobId', 'mapId', 'perPage'));
    }

    public function dropStore(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'rate' => 'required|numeric|min:0.01|max:100',
            'quantity_min' => 'required|integer|min:1',
            'quantity_max' => 'required|integer|min:1',
        ]);

        $exists = DB::table('item_template')->where('id', $request->item_id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'ID vật phẩm không tồn tại!']);
        }

        DB::table('mob_drop')->insert([
            'mob_id' => (int) ($request->mob_id ?? -1),
            'map_id' => (int) ($request->map_id ?? -1),
            'item_id' => (int) $request->item_id,
            'quantity_min' => (int) $request->quantity_min,
            'quantity_max' => (int) $request->quantity_max,
            'rate' => (float) $request->rate,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Thêm cấu hình rơi đồ quái thành công!']);
    }

    public function dropUpdate(Request $request, $id)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0.01|max:100',
            'quantity_min' => 'required|integer|min:1',
            'quantity_max' => 'required|integer|min:1',
        ]);

        DB::table('mob_drop')->where('id', $id)->update([
            'rate' => (float) $request->rate,
            'quantity_min' => (int) $request->quantity_min,
            'quantity_max' => (int) $request->quantity_max,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật cấu hình rơi đồ thành công!']);
    }

    public function dropToggle($id)
    {
        $drop = DB::table('mob_drop')->where('id', $id)->first();
        if (!$drop) {
            return response()->json(['success' => false, 'message' => 'Dòng cấu hình không tồn tại!']);
        }

        $newStatus = $drop->is_active ? 0 : 1;
        DB::table('mob_drop')->where('id', $id)->update(['is_active' => $newStatus, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => ($newStatus ? 'Đã kích hoạt' : 'Đã tạm dừng') . ' dòng rơi đồ!']);
    }

    public function dropDelete($id)
    {
        DB::table('mob_drop')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa dòng cấu hình rơi đồ!']);
    }

    /**
     * Visual Map Editor 2D (HTML5 Canvas)
     */
    public function mapEditor(Request $request)
    {
        $maps = DB::table('map_template')
            ->select('id', DB::raw('COALESCE(NAME, name) as name'), 'bg_id', 'npcs', 'mobs', 'zones')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($m) {
                $npcList = !empty($m->npcs) ? (json_decode($m->npcs, true) ?? []) : [];
                $mobList = !empty($m->mobs) ? (json_decode($m->mobs, true) ?? []) : [];
                $m->npc_count = is_array($npcList) ? count($npcList) : 0;
                $m->mob_count = is_array($mobList) ? count($mobList) : 0;
                return $m;
            });

        $currentServerEvent = 0;
        $eventConfigFile = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/event_config.properties";
        if (file_exists($eventConfigFile)) {
            $lines = file($eventConfigFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), 'current_event=')) {
                    $parts = explode('=', trim($line), 2);
                    $currentServerEvent = (int) ($parts[1] ?? 0);
                    break;
                }
            }
        }

        $eventsMetadata = $this->getEventsMetadata();
        $serverEvent = $this->callGameApi('/api/server/event', [], 'GET');
        if ($serverEvent['success'] ?? false) {
            $currentServerEvent = (int) ($serverEvent['current_event'] ?? $currentServerEvent);
        }

        $npcTemplates = DB::table('npc_template')->select('id', DB::raw('COALESCE(NAME, name) as name'), 'avatar')->orderBy('id', 'asc')->get();
        $radarMobIcons = DB::table('radar')->where('type', 0)->pluck('iconId', 'template')->toArray();
        $mobTemplates = DB::table('mob_template')->select('id', DB::raw('COALESCE(NAME, name) as name'), 'hp')->orderBy('id', 'asc')->get();
        foreach ($mobTemplates as $mt) {
            $mt->iconId = $radarMobIcons[$mt->id] ?? 0;
        }
        return view('admin.map_editor', compact('maps', 'npcTemplates', 'mobTemplates', 'currentServerEvent', 'eventsMetadata'));
    }

    public function mapEditorData(Request $request)
    {
        try {
            $mapId = (int) $request->input('map_id', 0);
            $map = DB::table('map_template')->where('id', $mapId)->first();
            if (!$map) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy map!']);
            }

            $mapName = $map->NAME ?? $map->name ?? ("Bản đồ #" . $mapId);

            $radarMobIcons = DB::table('radar')->where('type', 0)->pluck('iconId', 'template')->toArray();
            $npcTemplates = DB::table('npc_template')->get()->keyBy('id');
            $mobTemplates = DB::table('mob_template')->get()->keyBy('id');

            // 1. Parse NPCs
            $npcsRaw = [];
            if (!empty($map->npcs)) {
                $npcsRaw = json_decode($map->npcs, true) ?? [];
            }
            $npcs = [];
            foreach ($npcsRaw as $n) {
                if (is_array($n) && count($n) >= 3) {
                    $npcId = (int)$n[0];
                    $t = $npcTemplates[$npcId] ?? null;
                    $npcs[] = [
                        'id' => $npcId,
                        'x' => (int)$n[1],
                        'y' => (int)$n[2],
                        'name' => $t ? ($t->NAME ?? $t->name ?? "NPC #$npcId") : "NPC #$npcId",
                        'avatar' => $t ? (int)$t->avatar : 0
                    ];
                }
            }

            // 2. Parse Mobs
            $mobsRaw = [];
            if (!empty($map->mobs)) {
                $mobsRaw = json_decode($map->mobs, true) ?? [];
            }
            $mobs = [];
            foreach ($mobsRaw as $m) {
                if (is_string($m)) {
                    $m = json_decode($m, true);
                }
                if (is_array($m) && count($m) >= 5) {
                    $mobId = (int)$m[0];
                    $t = $mobTemplates[$mobId] ?? null;
                    $iconId = $radarMobIcons[$mobId] ?? 0;
                    $mobs[] = [
                        'id' => $mobId,
                        'level' => (int)$m[1],
                        'hp' => (int)$m[2],
                        'x' => (int)$m[3],
                        'y' => (int)$m[4],
                        'name' => $t ? ($t->NAME ?? $t->name ?? "Quái #$mobId") : "Quái #$mobId",
                        'iconId' => (int)$iconId
                    ];
                }
            }

            // 3. Parse Waypoints
            $waypoints = [];
            if (!empty($map->waypoints)) {
                $cleanedWp = str_replace(['["[', ']"]', '","'], ['[[', ']]', ','], $map->waypoints);
                $decoded = json_decode($cleanedWp, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $w) {
                        if (is_string($w)) {
                            $w = json_decode($w, true);
                        }
                        if (is_array($w) && count($w) >= 10) {
                            $waypoints[] = [
                                'name' => (string) $w[0],
                                'minX' => (int) $w[1],
                                'minY' => (int) $w[2],
                                'maxX' => (int) $w[3],
                                'maxY' => (int) $w[4],
                                'goMap' => (int) $w[7],
                                'goX' => (int) $w[8],
                                'goY' => (int) $w[9]
                            ];
                        }
                    }
                }
            }

            // 4. Parse Real Map Tile Platforms & Dimensions
            $mapWidth = 1400;
            $mapHeight = 600;
            $platforms = [];
            $tileDataPath = storage_path("app/tile_map_data/{$mapId}");
            if (!file_exists($tileDataPath)) {
                $tileDataPath = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/map/tile_map_data/{$mapId}";
            }

            if (file_exists($tileDataPath)) {
                $raw = file_get_contents($tileDataPath);
                if (strlen($raw) >= 2) {
                    $tw = ord($raw[0]);
                    $th = ord($raw[1]);
                    $mapWidth = max(1400, $tw * 24);
                    $mapHeight = max(560, $th * 24);

                    $tiles = [];
                    $offset = 2;
                    for ($y = 0; $y < $th; $y++) {
                        for ($x = 0; $x < $tw; $x++) {
                            if ($offset < strlen($raw)) {
                                $tiles[$y][$x] = ord($raw[$offset++]);
                            } else {
                                $tiles[$y][$x] = 0;
                            }
                        }
                    }

                    $blocks = [];
                    // Extract solid blocks and top walkable platform edges
                    for ($y = 0; $y < $th; $y++) {
                        $currentSeg = null;
                        $blockStart = null;
                        for ($x = 0; $x < $tw; $x++) {
                            $isSolid = ($tiles[$y][$x] > 0);

                            // Solid blocks (for drawing real game terrain mesh)
                            if ($isSolid) {
                                if ($blockStart === null) {
                                    $blockStart = $x;
                                }
                            } else {
                                if ($blockStart !== null) {
                                    $blocks[] = [
                                        'x' => $blockStart * 24,
                                        'y' => $y * 24,
                                        'w' => ($x - $blockStart) * 24,
                                        'h' => 24
                                    ];
                                    $blockStart = null;
                                }
                            }

                            // Walkable platform top edges (exclude ceiling at row 0)
                            $isTop = $isSolid && ($y > 0) && ($tiles[$y - 1][$x] == 0);
                            if ($isTop) {
                                if ($currentSeg === null) {
                                    $currentSeg = ['y' => $y * 24, 'x1' => $x * 24, 'x2' => ($x + 1) * 24];
                                } else {
                                    $currentSeg['x2'] = ($x + 1) * 24;
                                }
                            } else {
                                if ($currentSeg !== null) {
                                    $platforms[] = $currentSeg;
                                    $currentSeg = null;
                                }
                            }
                        }
                        if ($blockStart !== null) {
                            $blocks[] = [
                                'x' => $blockStart * 24,
                                'y' => $y * 24,
                                'w' => ($tw - $blockStart) * 24,
                                'h' => 24
                            ];
                        }
                        if ($currentSeg !== null) {
                            $platforms[] = $currentSeg;
                        }
                    }
                }
            }

            $decorations = [];
            $decorationsCount = 0;
            $bgDataPath = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/map/item_bg_map_data/{$mapId}";
            if (file_exists($bgDataPath)) {
                $bgRaw = @file_get_contents($bgDataPath);
                if ($bgRaw && strlen($bgRaw) >= 2) {
                    $decorationsCount = (ord($bgRaw[0]) << 8) | ord($bgRaw[1]);
                    $templates = $this->getItemBgTemplates();
                    for ($i = 0; $i < $decorationsCount; $i++) {
                        $offset = 2 + $i * 6;
                        if (strlen($bgRaw) < $offset + 6) break;
                        $tplId = (ord($bgRaw[$offset]) << 8) | ord($bgRaw[$offset + 1]);
                        $tx = (ord($bgRaw[$offset + 2]) << 8) | ord($bgRaw[$offset + 3]);
                        if ($tx >= 0x8000) $tx -= 0x10000;
                        $ty = (ord($bgRaw[$offset + 4]) << 8) | ord($bgRaw[$offset + 5]);
                        if ($ty >= 0x8000) $ty -= 0x10000;

                        $tpl = $templates[$tplId] ?? null;
                        if ($tpl) {
                            $px = $tx * 24 + $tpl['dx'];
                            $py = $ty * 24 + $tpl['dy'];
                            $decorations[] = [
                                'index' => $i,
                                'template_id' => $tplId,
                                'img_id' => $tpl['img_id'],
                                'layer' => $tpl['layer'],
                                'tx' => $tx,
                                'ty' => $ty,
                                'dx' => $tpl['dx'],
                                'dy' => $tpl['dy'],
                                'x' => $px,
                                'y' => $py
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'map' => [
                    'id' => $map->id,
                    'name' => $mapName,
                    'planet_id' => (int)($map->planet_id ?? 0),
                    'bg_id' => (int)($map->bg_id ?? 0),
                    'bg_type' => (int)($map->bg_type ?? 0),
                    'tile_id' => (int)($map->tile_id ?? 1),
                    'zones' => (int)($map->zones ?? 10),
                    'decorations_count' => count($decorations),
                    'decorations' => $decorations,
                    'width' => $mapWidth,
                    'height' => $mapHeight,
                    'tw' => $tw ?? 52,
                    'th' => $th ?? 24,
                    'tiles' => $tiles ?? [],
                    'blocks' => $blocks,
                    'platforms' => $platforms,
                    'npcs' => $npcs,
                    'mobs' => $mobs,
                    'waypoints' => $waypoints
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý dữ liệu map: ' . $e->getMessage()]);
        }
    }

    public function mapEditorSave(Request $request)
    {
        try {
            $mapId = (int) $request->input('map_id');
            $npcsInput = $request->input('npcs', []);
            $mobsInput = $request->input('mobs', []);

            $npcsFormatted = [];
            foreach ($npcsInput as $n) {
                if (is_array($n)) {
                    $id = (int)($n['id'] ?? $n[0] ?? 0);
                    $x = (int)($n['x'] ?? $n[1] ?? 0);
                    $y = (int)($n['y'] ?? $n[2] ?? 0);
                    $npcsFormatted[] = [$id, $x, $y];
                }
            }

            $mobsFormatted = [];
            foreach ($mobsInput as $m) {
                if (is_array($m)) {
                    $mobsFormatted[] = json_encode([
                        (int)($m['id'] ?? $m[0] ?? 0),
                        (int)($m['level'] ?? $m[1] ?? 1),
                        (int)($m['hp'] ?? $m[2] ?? 100),
                        (int)($m['x'] ?? $m[3] ?? 0),
                        (int)($m['y'] ?? $m[4] ?? 0)
                    ]);
                }
            }

            DB::table('map_template')->where('id', $mapId)->update([
                'npcs' => json_encode($npcsFormatted),
                'mobs' => json_encode($mobsFormatted),
            ]);

            // Save decorations to game binary item_bg_map_data if provided
            if ($request->has('decorations')) {
                $decosInput = $request->input('decorations', []);
                if (is_array($decosInput)) {
                    $cnt = count($decosInput);
                    $bytes = pack('n', $cnt);
                    foreach ($decosInput as $d) {
                        $tplId = (int)($d['template_id'] ?? 0);
                        $tx = (int)($d['tx'] ?? round(($d['x'] - ($d['dx'] ?? 0)) / 24));
                        $ty = (int)($d['ty'] ?? round(($d['y'] - ($d['dy'] ?? 0)) / 24));
                        $bytes .= pack('n', $tplId & 0xFFFF);
                        $bytes .= pack('n', $tx & 0xFFFF);
                        $bytes .= pack('n', $ty & 0xFFFF);
                    }
                    $bgDataPath = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/map/item_bg_map_data/{$mapId}";
                    if (file_exists($bgDataPath) && !file_exists($bgDataPath . '.bak')) {
                        @copy($bgDataPath, $bgDataPath . '.bak');
                    }
                    @file_put_contents($bgDataPath, $bytes);
                }
            }

            return response()->json(['success' => true, 'message' => 'Lưu dữ liệu bản đồ & vật trang trí thành công!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi lưu bản đồ: ' . $e->getMessage()]);
        }
    }

    /**
     * Cache & Parse Item Background Templates
     */
    protected function getItemBgTemplates()
    {
        static $cachedTemplates = null;
        if ($cachedTemplates !== null) {
            return $cachedTemplates;
        }

        $cachedTemplates = [];
        $path = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/item_bg_temp/item_bg_data";
        if (!file_exists($path)) {
            return $cachedTemplates;
        }

        $raw = @file_get_contents($path);
        if (!$raw || strlen($raw) < 2) {
            return $cachedTemplates;
        }

        $count = (ord($raw[0]) << 8) | ord($raw[1]);
        for ($i = 0; $i < $count; $i++) {
            $offset = 2 + $i * 8;
            if (strlen($raw) < $offset + 8) break;
            $imgId = (ord($raw[$offset]) << 8) | ord($raw[$offset + 1]);
            if ($imgId >= 0x8000) $imgId -= 0x10000;
            $layer = ord($raw[$offset + 2]);
            $dx = (ord($raw[$offset + 3]) << 8) | ord($raw[$offset + 4]);
            if ($dx >= 0x8000) $dx -= 0x10000;
            $dy = (ord($raw[$offset + 5]) << 8) | ord($raw[$offset + 6]);
            if ($dy >= 0x8000) $dy -= 0x10000;

            $cachedTemplates[$i] = [
                'img_id' => $imgId,
                'layer' => $layer,
                'dx' => $dx,
                'dy' => $dy
            ];
        }

        return $cachedTemplates;
    }

    /**
     * API to get item bg templates list for picker
     */
    public function getItemBgTemplatesApi()
    {
        return response()->json([
            'success' => true,
            'templates' => $this->getItemBgTemplates()
        ]);
    }

    /**
     * Serve Item Icon PNG directly with cache and fallback
     */
    public function getItemIcon($id)
    {
        $id = (int) $id;
        $scales = ['x4', 'x3', 'x2', 'x1'];
        $baseDir = "D:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/icon";

        foreach ($scales as $s) {
            $path = "{$baseDir}/{$s}/{$id}.png";
            if (file_exists($path)) {
                return response()->file($path, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=604800'
                ]);
            }
        }

        $fallback = public_path('images/no_image.png');
        if (file_exists($fallback)) {
            return response()->file($fallback, ['Content-Type' => 'image/png']);
        }

        abort(404);
    }

    /**
     * Serve Item BG Decoration PNG directly with theme and fallback
     */
    public function getItemBgImage(Request $request, $id)
    {
        $id = (int) $id;
        $theme = strtolower($request->input('theme', 'normal'));

        $baseDir = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun";
        $path = null;

        if ($theme === 'tet' || $theme === '1') {
            $p = "{$baseDir}/event/tet/x1/{$id}.png";
            if (file_exists($p)) {
                $path = $p;
            }
        } elseif ($theme === 'tuyet' || $theme === 'noel' || $theme === '2') {
            $p = "{$baseDir}/event/tuyet/x1/{$id}.png";
            if (file_exists($p)) {
                $path = $p;
            }
        }

        if (!$path) {
            $path = "{$baseDir}/item_bg_temp/x1/{$id}.png";
        }

        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=604800'
            ]);
        }

        $fallback = public_path('images/no_image.png');
        if (file_exists($fallback)) {
            return response()->file($fallback, ['Content-Type' => 'image/png']);
        }

        abort(404);
    }

    /**
     * Save active server event configuration
     */
    public function saveEventTheme(Request $request)
    {
        try {
            $eventId = (int) $request->input('event_id', 0);
            if ($eventId < 0 || $eventId > 10) {
                return response()->json(['success' => false, 'message' => 'ID sự kiện không hợp lệ!']);
            }

            $eventConfigFile = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/event_config.properties";
            $content = "#NRO Server Event Configuration\n";
            $content .= "#Updated via Map Editor on " . date('Y-m-d H:i:s') . "\n";
            $content .= "event_end_time=0\n";
            $content .= "current_event=" . $eventId . "\n";

            file_put_contents($eventConfigFile, $content);

            $events = $this->getEventsMetadata();
            $eventName = $events[$eventId]['name'] ?? "Sự kiện #$eventId";

            // Attempt realtime synchronization via Game Server API
            $realtimeRes = $this->callGameApi('/api/server/event', [
                'event_id' => $eventId,
                'duration_hours' => 0
            ]);
            $isRealtime = $realtimeRes['success'] ?? false;

            return response()->json([
                'success' => true,
                'message' => "Đã lưu cài đặt sự kiện: {$eventName}" . ($isRealtime ? " (Áp dụng Realtime ngay trên Game Server)!" : "!")
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete metadata for all 11 Game Events
     */
    public function getEventsMetadata()
    {
        return [
            0 => [
                'id' => 0,
                'name' => 'Bình Thường (Mặc Định)',
                'short_name' => 'Bình Thường',
                'icon' => '🌿',
                'badge' => 'Mặc định',
                'color' => '#8b949e',
                'desc' => 'Không có sự kiện, bản đồ và tài nguyên nguyên bản.',
                'npc' => 'Không có NPC sự kiện',
                'items' => 'Tài nguyên gốc server',
                'highlight' => 'Trạng thái server tiêu chuẩn nguyên bản'
            ],
            1 => [
                'id' => 1,
                'name' => 'Tết Nguyên Đán',
                'short_name' => 'Tết Nguyên Đán',
                'icon' => '🧧',
                'badge' => 'Lễ Hội',
                'color' => '#f85149',
                'desc' => 'Nấu Bánh Tét, Bánh Chưng, Hộp Quà Tết, Pháo Hoa, Cải Trang Thần Tài.',
                'npc' => 'Lý Tiểu Nương (ID 54)',
                'items' => 'Bánh chưng, Bánh tét, Thiệp chúc tết, Hộp quà tết, Pháo hoa, Cải trang Thần Tài',
                'highlight' => 'Bản đồ hoa mai vàng & hoa đào khoe sắc'
            ],
            2 => [
                'id' => 2,
                'name' => 'Giáng Sinh / Noel (Tuyết)',
                'short_name' => 'Giáng Sinh (Tuyết)',
                'icon' => '❄️',
                'badge' => 'Mùa Đông',
                'color' => '#58a6ff',
                'desc' => 'Bản đồ tuyết rơi, Santa phát quà, Kẹo Noel, Nón Noel, Cải Trang Noel Vip.',
                'npc' => 'Santa Claus (ID 39)',
                'items' => 'Kẹo giáng sinh, Nón Noel Đỏ/Xanh, Hộp quà Noel, Cải trang Noel Vip',
                'highlight' => 'Cây cối và nhà cửa phủ tuyết trắng xóa'
            ],
            3 => [
                'id' => 3,
                'name' => 'Sự Kiện Trung Thu',
                'short_name' => 'Trung Thu',
                'icon' => '🥮',
                'badge' => 'Lễ Hội',
                'color' => '#d29922',
                'desc' => 'Bánh Nướng, Bánh Dẻo, Lồng Đèn, Chị Hằng, Pet Thỏ Ngọc, Cải Trang Hằng Nga.',
                'npc' => 'Chị Hằng (ID 92)',
                'items' => 'Bánh dẻo, Bánh nướng, Hạt sen, Đậu xanh, Lồng đèn, Pet Thỏ Ngọc',
                'highlight' => 'Lễ hội trăng rằm, rước đèn & phá cỗ'
            ],
            4 => [
                'id' => 4,
                'name' => 'Sự Kiện Halloween',
                'short_name' => 'Halloween',
                'icon' => '🎃',
                'badge' => 'Lễ Hội',
                'color' => '#bc8cff',
                'desc' => 'Bí Ngô Ma Quái, Kẹo Phù Thủy, Quái Bí Ngô, Cải Trang Ma Cà Rồng & Bí Ngô.',
                'npc' => 'Ông Già Bí Ngô (ID 75)',
                'items' => 'Kẹo Halloween, Quả Bí ngô, Răng nanh, Áo choàng ma',
                'highlight' => 'Không khí kinh dị, quái bí ngô xuất hiện khắp nơi'
            ],
            5 => [
                'id' => 5,
                'name' => 'Mùa Hè Sôi Động (Đi Biển)',
                'short_name' => 'Mùa Hè Sôi Động',
                'icon' => '🏖️',
                'badge' => 'Mùa Hè',
                'color' => '#3fb950',
                'desc' => 'Thu hoạch Dưa Hấu, Vỏ Sò, Cải Trang Đi Biển, Nón Rơm Mùa Hè.',
                'npc' => 'Ông Ngư Dân (ID 98)',
                'items' => 'Dưa hấu, Vỏ ốc, Sao biển, Kem giải khát, Nón rơm đi biển',
                'highlight' => 'Thu thập giải khát và thời trang biển rực rỡ'
            ],
            6 => [
                'id' => 6,
                'name' => 'Giỗ Tổ Hùng Vương',
                'short_name' => 'Giỗ Tổ Hùng Vương',
                'icon' => '👑',
                'badge' => 'Truyền Thống',
                'color' => '#e3b341',
                'desc' => 'Sính Lễ Voi 9 Ngà, Ngựa 9 Mao, Gà 9 Cựa, Nấu Bánh Chưng, CT Sơn Tinh, Thủy Tinh, Mị Nương (Top 1 Server).',
                'npc' => 'Mị Nương (ID 77)',
                'items' => 'Voi 9 ngà, Ngựa 9 mao, Gà 9 cựa, Bánh chưng đất tổ',
                'highlight' => 'Đua TOP dâng sính lễ nhận Cải Trang Mị Nương Vô Cực'
            ],
            7 => [
                'id' => 7,
                'name' => 'Valentine & Ngày Phụ Nữ',
                'short_name' => 'Valentine & Phụ Nữ',
                'icon' => '🌹',
                'badge' => 'Tình Yêu',
                'color' => '#f778ba',
                'desc' => 'Hoa Hồng, Socola, Hộp Quà Tình Nhân, Cải Trang Cặp Đôi.',
                'npc' => 'Lý Tiểu Nương (ID 54)',
                'items' => 'Hoa hồng đỏ, Socola tình yêu, Hộp quà tình nhân, Nhẫn đôi',
                'highlight' => 'Tặng hoa ghép cặp, tăng chỉ số khi đi cùng nhau'
            ],
            8 => [
                'id' => 8,
                'name' => 'Đại Lễ 30/4 - 1/5 & 2/9',
                'short_name' => 'Đại Lễ 30/4 & 2/9',
                'icon' => '🎆',
                'badge' => 'Quốc Khánh',
                'color' => '#ff7b72',
                'desc' => 'x2 EXP Toàn Server, Bắn Pháo Hoa, Cờ Tổ Quốc, Huy Hiệu Chiến Thắng.',
                'npc' => 'Quốc Vương (ID 42)',
                'items' => 'Huy hiệu chiến thắng, Pháo hoa đại lễ, Cờ đỏ sao vàng',
                'highlight' => 'Nhân đôi toàn bộ kinh nghiệm train quái & rớt vàng'
            ],
            9 => [
                'id' => 9,
                'name' => 'Sự Kiện Tây Du Ký',
                'short_name' => 'Tây Du Ký',
                'icon' => '🐒',
                'badge' => 'Huyền Thoại',
                'color' => '#e36209',
                'desc' => 'Sưu tầm chữ [GIẢI-KHAI-PHONG-ẤN], Quả Hồng Đào Tiên, Gậy Như Ý, Vòng Kim Cô.',
                'npc' => 'Đường Tăng (ID 49)',
                'items' => 'Chữ GIẢI-KHAI-PHONG-ẤN, Hồng đào tiên, Gậy như ý, Kim cô',
                'highlight' => 'Thỉnh kinh hộ tống, đại náo thiên cung'
            ],
            10 => [
                'id' => 10,
                'name' => 'Quốc Tế Thiếu Nhi (1/6)',
                'short_name' => 'Quốc Tế Thiếu Nhi',
                'icon' => '🎈',
                'badge' => 'Thiếu Nhi',
                'color' => '#56d364',
                'desc' => 'Hộp Quà Thiếu Nhi, Bong Bóng Bay, Cải Trang Arale, Pet Ngộ Nghĩnh.',
                'npc' => 'Tori-Bot (ID 74)',
                'items' => 'Bong bóng nhiều màu, Kẹo mút, Hộp quà thiếu nhi, Pet bé ngoan',
                'highlight' => 'Nhận quà mỗi ngày, mini game tuổi thơ'
            ]
        ];
    }

    /**
     * Dedicated Event Management Page
     */
    public function events(Request $request)
    {
        $events = $this->getEventsMetadata();

        // 1. Fetch live event status from Game Server
        $serverEvent = $this->callGameApi('/api/server/event', [], 'GET');
        $isGameActive = ($serverEvent['success'] ?? false) || @fsockopen('127.0.0.1', 14445, $errno, $errstr, 0.3) !== false;

        $currentEventId = 0;
        $eventEndTime = 0;
        $timeRemaining = 'Không áp dụng';

        if ($serverEvent['success'] ?? false) {
            $currentEventId = (int) ($serverEvent['current_event'] ?? 0);
            $eventEndTime = (int) ($serverEvent['event_end_time'] ?? 0);
            $timeRemaining = $serverEvent['time_remaining'] ?? 'Không áp dụng';
        } else {
            // Read from properties file as fallback
            $eventConfigFile = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/event_config.properties";
            if (file_exists($eventConfigFile)) {
                $lines = file($eventConfigFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), 'current_event=')) {
                        $parts = explode('=', trim($line), 2);
                        $currentEventId = (int) ($parts[1] ?? 0);
                    }
                    if (str_starts_with(trim($line), 'event_end_time=')) {
                        $parts = explode('=', trim($line), 2);
                        $eventEndTime = (int) ($parts[1] ?? 0);
                    }
                }
            }
            if ($currentEventId > 0) {
                $timeRemaining = $eventEndTime > 0 ? date('d/m/Y H:i', $eventEndTime / 1000) : 'Vĩnh viễn';
            }
        }

        $activeEvent = $events[$currentEventId] ?? $events[0];

        return view('admin.events', compact(
            'events',
            'currentEventId',
            'activeEvent',
            'timeRemaining',
            'eventEndTime',
            'isGameActive'
        ));
    }

    /**
     * Update Event in Realtime or with Maintenance
     */
    public function eventUpdate(Request $request)
    {
        try {
            $eventId = (int) $request->input('event_id', 0);
            $durationHours = (int) $request->input('duration_hours', 0);
            $maintenance = (bool) $request->input('maintenance', false);
            $delaySeconds = (int) $request->input('delay_seconds', 10);

            if ($eventId < 0 || $eventId > 10) {
                return response()->json(['success' => false, 'message' => 'ID sự kiện không hợp lệ! Vui lòng chọn 0 đến 10.']);
            }

            // 1. Persistent file update
            $eventConfigFile = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon/data/girlkun/event_config.properties";
            $endTimeCalc = $durationHours > 0 ? (time() * 1000 + ($durationHours * 3600 * 1000)) : 0;
            $content = "#NRO Server Event Configuration\n";
            $content .= "#Updated via Web Admin on " . date('Y-m-d H:i:s') . "\n";
            $content .= "event_end_time=" . $endTimeCalc . "\n";
            $content .= "current_event=" . $eventId . "\n";
            file_put_contents($eventConfigFile, $content);

            // 2. Realtime update via Game Server API
            $apiRes = $this->callGameApi('/api/server/event', [
                'event_id' => $eventId,
                'duration_hours' => $durationHours,
                'maintenance' => $maintenance,
                'delay_seconds' => $delaySeconds
            ]);

            $events = $this->getEventsMetadata();
            $evName = $events[$eventId]['name'] ?? "Sự kiện #$eventId";

            if ($apiRes['success'] ?? false) {
                return response()->json([
                    'success' => true,
                    'message' => $apiRes['message'] ?? "Đã kích hoạt sự kiện {$evName} thành công!",
                    'current_event' => $eventId,
                    'event_name' => $evName,
                    'is_realtime' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Đã lưu cài đặt sự kiện {$evName} vào cấu hình máy chủ (Game Server đang offline, sẽ tự nạp khi bật game)!",
                'current_event' => $eventId,
                'event_name' => $evName,
                'is_realtime' => false
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear client background cache
     */
    public function eventClearCache(Request $request)
    {
        try {
            $apiRes = $this->callGameApi('/api/server/event', ['clear_cache' => true]);
            if ($apiRes['success'] ?? false) {
                return response()->json(['success' => true, 'message' => $apiRes['message'] ?? 'Đã xóa sạch cache client!']);
            }
            return response()->json(['success' => true, 'message' => 'Đã gửi lệnh xóa cache nền client.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * Server & Build Management Page
     */
    public function serverBuild(Request $request)
    {
        $serverStats = $this->callGameApi('/api/server/stats', [], 'GET');
        $isGameActive = ($serverStats['success'] ?? false) || @fsockopen('127.0.0.1', 14445, $errno, $errstr, 0.3) !== false;

        $isMysqlActive = false;
        try {
            DB::connection()->getPdo();
            $isMysqlActive = true;
        } catch (\Throwable $e) {}

        $isNginxActive = true;
        $isPanelActive = true;

        $detectedIp = $request->header('X-Real-IP')
            ?: ($request->header('X-Forwarded-For') ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) : $request->ip());

        $defaultAllowlist = ['100.64.0.0/10', '127.0.0.1/32', '::1/128', '14.233.210.201/32', '123.26.4.208/32', '123.26.9.45/32'];
        $allowlistFile = storage_path('app/dev_allowlist.json');
        $saved = [];
        if (file_exists($allowlistFile)) {
            $saved = json_decode(@file_get_contents($allowlistFile), true) ?? [];
        }
        $allowlist = array_values(array_unique(array_merge($defaultAllowlist, $saved)));

        return view('admin.server_build', compact(
            'isGameActive',
            'isMysqlActive',
            'isNginxActive',
            'isPanelActive',
            'detectedIp',
            'allowlist',
            'serverStats'
        ));
    }

    public function serverBuildStatus()
    {
        $serverStats = $this->callGameApi('/api/server/stats', [], 'GET');
        $isGameActive = ($serverStats['success'] ?? false) || @fsockopen('127.0.0.1', 14445, $errno, $errstr, 0.3) !== false;

        $isMysqlActive = false;
        try {
            DB::connection()->getPdo();
            $isMysqlActive = true;
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'nro_game' => $isGameActive,
            'mysql' => $isMysqlActive,
            'nginx' => true,
            'nro_panel' => true
        ]);
    }

    private function launchGameServerDaemon()
    {
        $psCode = "Start-Process -FilePath 'C:\\Program Files\\Java\\jdk-17\\bin\\java.exe' -ArgumentList @('-server', '-cp', 'dist/soicon.jar;lib/*', 'com.girlkun.server.ServerManager') -WorkingDirectory 'd:\\nro\\srcnrobigk03032026\\nrobigkv7\\soicon' -WindowStyle Hidden";
        $encoded = base64_encode(mb_convert_encoding($psCode, 'UTF-16LE', 'UTF-8'));
        shell_exec("powershell.exe -NoProfile -ExecutionPolicy Bypass -EncodedCommand {$encoded} 2>&1");
    }

    public function serverBuildAction(Request $request)
    {
        $action = $request->input('action');
        $output = "";

        switch ($action) {
            case 'start':
                $fp = @fsockopen('127.0.0.1', 14445, $errno, $errstr, 0.3);
                if ($fp) {
                    fclose($fp);
                    return response()->json([
                        'success' => true,
                        'message' => 'Máy chủ Game Server đã đang chạy trên Port 14445!',
                        'output' => "[STATUS] Game Server đang chạy trên port 14445 và Admin API 14446."
                    ]);
                }

                $this->launchGameServerDaemon();
                $output = "[INFO] Đã gửi lệnh khởi chạy Game Server dưới nền.\n[SOCKET] Chờ port 14445 & HTTP API 14446 lắng nghe...";
                break;

            case 'stop':
                $this->callGameApi('/api/server/maintenance', ['seconds' => 3]);
                shell_exec('powershell.exe -NoProfile -Command "Get-Process -Name java -ErrorAction SilentlyContinue | Stop-Process -Force"');
                $output = "[STOP] Đã lưu dữ liệu player và dừng tiến trình Game Server.";
                break;

            case 'restart':
                $this->callGameApi('/api/server/maintenance', ['seconds' => 2]);
                shell_exec('powershell.exe -NoProfile -Command "Get-Process -Name java -ErrorAction SilentlyContinue | Stop-Process -Force"');
                sleep(1);
                $this->launchGameServerDaemon();
                $output = "[RESTART] Đã khởi động lại máy chủ Game Server (Port 14445).";
                break;

            case 'backup':
                $backupDir = storage_path('app/backups');
                if (!file_exists($backupDir)) @mkdir($backupDir, 0777, true);
                $filename = 'backup_bigk_' . date('Ymd_His') . '.sql';
                $filepath = $backupDir . '/' . $filename;
                
                $dumpCmd = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root bigk > \"$filepath\"";
                shell_exec("cmd /c \"$dumpCmd\"");
                $size = file_exists($filepath) ? round(filesize($filepath) / 1024, 2) . ' KB' : '0 KB';
                $output = "[BACKUP] Sao lưu Database 'bigk' thành công!\n[FILE] {$filepath} ({$size})\n[TIME] " . date('Y-m-d H:i:s');
                break;

            case 'build':
            case 'clean_build':
                $soiconDir = "d:/nro/srcnrobigk03032026/nrobigkv7/soicon";
                $buildOutput = [];
                if ($action === 'clean_build') {
                    $buildOutput[] = "[CLEAN] Xóa bộ nhớ cache build/classes...";
                    shell_exec("cmd /c \"rd /s /q \\\"$soiconDir\\build\\classes\\\" 2>nul & mkdir \\\"$soiconDir\\build\\classes\\\"\"");
                }

                $buildOutput[] = "[BUILD] Bắt đầu biên dịch source Java với JDK 17 javac...";
                $compileCmd = "cd /d \"$soiconDir\" && \"C:\\Program Files\\Java\\jdk-17\\bin\\javac.exe\" -encoding UTF-8 -cp \"lib/*\" -d build/classes @sources.txt 2>&1";
                $compileRes = shell_exec($compileCmd);
                if ($compileRes) {
                    $buildOutput[] = $compileRes;
                }

                $jarCmd = "cd /d \"$soiconDir\" && \"C:\\Program Files\\Java\\jdk-17\\bin\\jar.exe\" cf dist/soicon.jar -C build/classes . 2>&1";
                $jarRes = shell_exec($jarCmd);
                if ($jarRes) {
                    $buildOutput[] = $jarRes;
                }
                $buildOutput[] = "[SUCCESS] Đóng gói hoàn tất file dist/soicon.jar! (" . date('H:i:s') . ")";
                $output = implode("\n", $buildOutput);
                break;

            case 'maintenance':
                $this->callGameApi('/api/server/maintenance', ['seconds' => 30]);
                $output = "[BẢO TRÌ] Kích hoạt đếm ngược 30 giây: Thông báo toàn server, kick và lưu tất cả tài khoản an toàn.";
                break;

            case 'reset_server':
                try {
                    DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
                    DB::table('player')->whereNotIn('account_id', function($q) {
                        $q->select('id')->from('account')->where('is_admin', 1);
                    })->delete();
                    DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
                    $output = "[RESET SERVER] Đã dọn dẹp toàn bộ dữ liệu nhân vật thường, giữ nguyên tài khoản Admin.";
                } catch (\Throwable $e) {
                    $output = "[RESET SERVER ERROR] " . $e->getMessage();
                }
                break;

            default:
                $output = "[ERROR] Hành động không hợp lệ: {$action}";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Thực hiện thành công',
            'output' => $output
        ]);
    }

    public function serverBuildIp(Request $request)
    {
        $action = $request->input('action', 'add');
        $ip = trim($request->input('ip', ''));

        $allowlistFile = storage_path('app/dev_allowlist.json');
        $allowlist = [];
        if (file_exists($allowlistFile)) {
            $allowlist = json_decode(@file_get_contents($allowlistFile), true) ?? [];
        }

        if ($action === 'add' && !empty($ip)) {
            if (!in_array($ip, $allowlist)) {
                $allowlist[] = $ip;
                file_put_contents($allowlistFile, json_encode($allowlist, JSON_PRETTY_PRINT));
            }
            return response()->json([
                'success' => true,
                'message' => "Đã thêm IP {$ip} vào danh sách cho phép (nrodev)!",
                'allowlist' => array_values(array_unique(array_merge(['100.64.0.0/10', '127.0.0.1/32', '::1/128', '14.233.210.201/32', '123.26.4.208/32', '123.26.9.45/32'], $allowlist)))
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã tải lại danh sách IP thành công!',
            'allowlist' => array_values(array_unique(array_merge(['100.64.0.0/10', '127.0.0.1/32', '::1/128', '14.233.210.201/32', '123.26.4.208/32', '123.26.9.45/32'], $allowlist)))
        ]);
    }
}
