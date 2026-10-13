<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\GiftCodeController;
use App\Http\Controllers\SpinController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccSaleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [DashboardController::class, 'home'])->name('home');
Route::get('/ranking', [DashboardController::class, 'ranking'])->name('ranking');
Route::get('/guide', [DashboardController::class, 'guide'])->name('guide');
Route::get('/guide/{id}', [DashboardController::class, 'guideShow'])->name('guide.show');
Route::get('/download', [DashboardController::class, 'download'])->name('download');
Route::get('/images/icons/{id}.png', [AdminController::class, 'getItemIcon'])->name('item.icon');

// Guest only (login, register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated players
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/change-password', [DashboardController::class, 'changePassword'])->name('profile.change-password');
    // Recharge disabled (internal server) -> Redirect to shop
    Route::get('/recharge', function () { return redirect()->route('shop'); })->name('recharge');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::post('/shop/buy', [ShopController::class, 'buy'])->name('shop.buy');
    Route::post('/giftcode/use', [GiftCodeController::class, 'useCode'])->name('giftcode.use');
    Route::post('/giftcode/claim-all', [GiftCodeController::class, 'claimAll'])->name('giftcode.claim-all');
    Route::get('/spin', [SpinController::class, 'index'])->name('spin');
    Route::post('/spin', [SpinController::class, 'spin'])->name('spin.execute');
    
    // Web Chest
    Route::post('/web-chest/withdraw-all', [DashboardController::class, 'withdrawAllChestItems'])->name('web_chest.withdraw_all');
    Route::post('/web-chest/withdraw/{id}', [DashboardController::class, 'withdrawChestItem'])->name('web_chest.withdraw');
    
    // Account Marketplace
    Route::get('/acc-sales', [AccSaleController::class, 'index'])->name('acc_sales.index');
    Route::get('/acc-sales/sell', [AccSaleController::class, 'sell'])->name('acc_sales.sell');
    Route::post('/acc-sales/sell', [AccSaleController::class, 'store'])->name('acc_sales.store');
    Route::get('/acc-sales/history', [AccSaleController::class, 'history'])->name('acc_sales.history');
    Route::get('/acc-sales/{id}', [AccSaleController::class, 'show'])->name('acc_sales.show');
    Route::post('/acc-sales/buy', [AccSaleController::class, 'buy'])->name('acc_sales.buy');
    Route::post('/acc-sales/cancel', [AccSaleController::class, 'cancel'])->name('acc_sales.cancel');
});

// Admin area redirect
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

// Admin area
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('index');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [AdminController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/players', [AdminController::class, 'players'])->name('players');
    Route::post('/players/{id}/ban', [AdminController::class, 'banPlayer'])->name('players.ban');
    Route::post('/players/{id}/unban', [AdminController::class, 'unbanPlayer'])->name('players.unban');
    Route::post('/players/{id}/set-admin', [AdminController::class, 'setAdmin'])->name('players.set-admin');
    Route::post('/players/{id}/remove-admin', [AdminController::class, 'removeAdmin'])->name('players.remove-admin');
    // Realtime Game Server Control
    Route::post('/players/{id}/buff', [AdminController::class, 'buffPlayer'])->name('players.buff');
    Route::post('/players/{id}/set-point', [AdminController::class, 'setPlayerPoints'])->name('players.set-point');
    Route::post('/players/{id}/kick', [AdminController::class, 'kickPlayer'])->name('players.kick');
    Route::get('/players/{id}/items', [AdminController::class, 'playerItems'])->name('players.items');
    Route::post('/players/{id}/item-update', [AdminController::class, 'playerItemUpdate'])->name('players.item-update');
    Route::post('/players/{id}/item-delete', [AdminController::class, 'playerItemDelete'])->name('players.item-delete');
    Route::post('/server/notify', [AdminController::class, 'sendServerNotify'])->name('server.notify');
    Route::post('/server/maintenance', [AdminController::class, 'startMaintenance'])->name('server.maintenance');
    Route::get('/server/stats', [AdminController::class, 'getServerStats'])->name('server.stats');
    Route::post('/server/config', [AdminController::class, 'serverConfigUpdate'])->name('server.config');

    // Boss Management
    Route::get('/bosses', [AdminController::class, 'bosses'])->name('bosses');
    Route::get('/bosses/data', [AdminController::class, 'bossesData'])->name('bosses.data');
    Route::post('/bosses/spawn', [AdminController::class, 'bossSpawn'])->name('bosses.spawn');
    Route::post('/bosses/kill', [AdminController::class, 'bossKill'])->name('bosses.kill');

    // Bot AI Management
    Route::get('/bots', [AdminController::class, 'bots'])->name('bots');
    Route::post('/bots/spawn', [AdminController::class, 'botSpawn'])->name('bots.spawn');
    Route::post('/bots/stop', [AdminController::class, 'botStop'])->name('bots.stop');
    Route::post('/bots/toggle-chat', [AdminController::class, 'botToggleChat'])->name('bots.toggle-chat');

    // Combine Rate Management
    Route::get('/combine-rate', [AdminController::class, 'combineRate'])->name('combine-rate');
    Route::post('/combine-rate', [AdminController::class, 'combineRateUpdate'])->name('combine-rate.update');
    
    // Shop
    Route::get('/shop', [AdminController::class, 'shop'])->name('shop');
    Route::get('/shop/search-templates', [AdminController::class, 'searchTemplates'])->name('shop.search-templates');
    Route::get('/shop/search-options', [AdminController::class, 'searchOptionTemplates'])->name('shop.search-options');
    Route::post('/shop/store', [AdminController::class, 'shopStore'])->name('shop.store');
    Route::put('/shop/{id}', [AdminController::class, 'shopUpdate'])->name('shop.update');
    Route::delete('/shop/{id}', [AdminController::class, 'shopDelete'])->name('shop.delete');
    
    // Gift Codes
    Route::get('/giftcodes', [AdminController::class, 'giftcodes'])->name('giftcodes');
    Route::post('/giftcodes/store', [AdminController::class, 'giftcodeStore'])->name('giftcodes.store');
    Route::put('/giftcodes/{id}', [AdminController::class, 'giftcodeUpdate'])->name('giftcodes.update');
    Route::delete('/giftcodes/{id}', [AdminController::class, 'giftcodeDelete'])->name('giftcodes.delete');
    
    // Items Lookup
    Route::get('/items-lookup', [AdminController::class, 'itemsLookup'])->name('items.lookup');

    // Achievements / Bo Mong Task
    Route::get('/achievements', [AdminController::class, 'achievements'])->name('achievements');
    Route::post('/achievements/store', [AdminController::class, 'achievementStore'])->name('achievements.store');
    Route::put('/achievements/{id}', [AdminController::class, 'achievementUpdate'])->name('achievements.update');
    Route::delete('/achievements/{id}', [AdminController::class, 'achievementDelete'])->name('achievements.delete');

    // Mob Drops Management
    Route::get('/drops', [AdminController::class, 'drops'])->name('drops');
    Route::post('/drops/store', [AdminController::class, 'dropStore'])->name('drops.store');
    Route::put('/drops/{id}', [AdminController::class, 'dropUpdate'])->name('drops.update');
    Route::post('/drops/{id}/toggle', [AdminController::class, 'dropToggle'])->name('drops.toggle');
    Route::delete('/drops/{id}', [AdminController::class, 'dropDelete'])->name('drops.delete');

    // Visual Map Editor 2D
    Route::get('/map-editor', [AdminController::class, 'mapEditor'])->name('map_editor');
    Route::get('/map-editor/data', [AdminController::class, 'mapEditorData'])->name('map_editor.data');
    Route::post('/map-editor/save', [AdminController::class, 'mapEditorSave'])->name('map_editor.save');

    // Server & Build
    Route::get('/server-build', [AdminController::class, 'serverBuild'])->name('server_build');
    Route::post('/server-build/action', [AdminController::class, 'serverBuildAction'])->name('server_build.action');
    Route::post('/server-build/ip', [AdminController::class, 'serverBuildIp'])->name('server_build.ip');
    Route::get('/server-build/status', [AdminController::class, 'serverBuildStatus'])->name('server_build.status');
    
    // Downloads
    Route::get('/downloads', [AdminController::class, 'downloads'])->name('downloads');
    Route::post('/downloads/store', [AdminController::class, 'downloadStore'])->name('downloads.store');
    Route::put('/downloads/{id}', [AdminController::class, 'downloadUpdate'])->name('downloads.update');
    Route::delete('/downloads/{id}', [AdminController::class, 'downloadDelete'])->name('downloads.delete');
    
    // Guides (Posts)
    Route::get('/posts', [AdminController::class, 'posts'])->name('posts');
    Route::post('/posts/store', [AdminController::class, 'postStore'])->name('posts.store');
    Route::put('/posts/{id}', [AdminController::class, 'postUpdate'])->name('posts.update');
    Route::delete('/posts/{id}', [AdminController::class, 'postDelete'])->name('posts.delete');
    
    // Spin Wheel
    Route::get('/spin', [AdminController::class, 'spin'])->name('spin');
    Route::post('/spin/settings', [AdminController::class, 'spinSettings'])->name('spin.settings');
    Route::post('/spin/items/store', [AdminController::class, 'spinItemStore'])->name('spin.items.store');
    Route::put('/spin/items/{id}', [AdminController::class, 'spinItemUpdate'])->name('spin.items.update');
    Route::delete('/spin/items/{id}', [AdminController::class, 'spinItemDelete'])->name('spin.items.delete');
    
    // Recharges disabled

    // Web Chest Management
    Route::get('/web-chest', [AdminController::class, 'webChestIndex'])->name('web_chest');
    Route::post('/web-chest/send', [AdminController::class, 'webChestSend'])->name('web_chest.send');
    Route::delete('/web-chest/{id}', [AdminController::class, 'webChestDelete'])->name('web_chest.delete');
    
    // Account Marketplace Management
    Route::get('/acc-sales', [AdminController::class, 'accSales'])->name('acc_sales');
    Route::post('/acc-sales/settings', [AdminController::class, 'accSaleSettings'])->name('acc_sales.settings');
    Route::put('/acc-sales/{id}/price', [AdminController::class, 'accSaleUpdatePrice'])->name('acc_sales.price');
    Route::post('/acc-sales/{id}/cancel', [AdminController::class, 'accSaleCancel'])->name('acc_sales.cancel');
    Route::post('/acc-sales/{id}/approve', [AdminController::class, 'accSaleApprovePayout'])->name('acc_sales.approve');
    Route::post('/acc-sales/{id}/refund', [AdminController::class, 'accSaleRefund'])->name('acc_sales.refund');
});
