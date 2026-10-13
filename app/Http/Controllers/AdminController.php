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
        
        // 1. Thử gọi Game Server API nếu player đang online
        $res = $this->callGameApi('/api/player/items', ['username' => $account->username], 'GET');
        if (!empty($res['success'])) {
            $res['source'] = 'ram';
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
            'bag' => $bagItems
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
        $maps = DB::table('map_template')->select('id', DB::raw('COALESCE(NAME, name) as name'))->orderBy('id', 'asc')->get();
        $npcTemplates = DB::table('npc_template')->select('id', DB::raw('COALESCE(NAME, name) as name'), 'avatar')->orderBy('id', 'asc')->get();
        $mobTemplates = DB::table('mob_template')->select('id', DB::raw('COALESCE(NAME, name) as name'), 'hp')->orderBy('id', 'asc')->get();
        return view('admin.map_editor', compact('maps', 'npcTemplates', 'mobTemplates'));
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
                    $mobs[] = [
                        'id' => $mobId,
                        'level' => (int)$m[1],
                        'hp' => (int)$m[2],
                        'x' => (int)$m[3],
                        'y' => (int)$m[4],
                        'name' => $t ? ($t->NAME ?? $t->name ?? "Quái #$mobId") : "Quái #$mobId"
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

            return response()->json([
                'success' => true,
                'map' => [
                    'id' => $map->id,
                    'name' => $mapName,
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

            return response()->json(['success' => true, 'message' => 'Lưu tọa độ thực thể bản đồ vào Database thành công!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi lưu bản đồ: ' . $e->getMessage()]);
        }
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
}
