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
        Paginator::useBootstrapFive();

        // Share server status globally
        view()->composer('*', function ($view) {
            $game_server_online = false;
            $online_players = 0;

            // Attempt to connect to game port 14445
            $connection = @fsockopen('127.0.0.1', 14445, $errno, $errstr, 0.1);
            if ($connection) {
                $game_server_online = true;
                fclose($connection);
                
                // Count accounts where last_time_login is greater than last_time_logout
                try {
                    $online_players = \Illuminate\Support\Facades\DB::table('account')
                        ->whereRaw('last_time_login > last_time_logout')
                        ->count();
                } catch (\Exception $e) {
                    $online_players = 0;
                }
            }

            $view->with([
                'game_server_online' => $game_server_online,
                'online_players' => $online_players,
            ]);
        });
    }
}
