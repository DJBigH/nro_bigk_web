<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.cyber');
        Paginator::defaultSimpleView('pagination.cyber');

        // Share server status globally with cache to avoid lag and socket freezing
        view()->composer(['layouts.app', 'admin.layout', 'home'], function ($view) {
            $status = \Illuminate\Support\Facades\Cache::remember('shared_game_server_status', 10, function () {
                $game_server_online = false;
                $online_players = 0;

                // Non-blocking quick check on game port 14445 (timeout 0.05s)
                $connection = @fsockopen('127.0.0.1', 14445, $errno, $errstr, 0.05);
                if ($connection) {
                    $game_server_online = true;
                    fclose($connection);

                    try {
                        $online_players = \Illuminate\Support\Facades\DB::table('account')
                            ->whereRaw('last_time_login > last_time_logout')
                            ->count();
                    } catch (\Exception $e) {
                        $online_players = 0;
                    }
                }

                return [
                    'game_server_online' => $game_server_online,
                    'online_players' => $online_players,
                ];
            });

            $view->with($status);
        });
    }
}
