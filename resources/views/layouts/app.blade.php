<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Ngọc Rồng BigK - Game NRO Private Hấp Dẫn Nhất')</title>
    <meta name="description" content="@yield('description', 'Website chính thức của máy chủ Ngọc Rồng BigK. Đăng ký, nạp thẻ, vòng quay may mắn, webshop mua thỏi vàng và vật phẩm cực kỳ hấp dẫn.')">
    
    <!-- CSS stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1.6">
    
    <!-- AlpineJS CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @yield('styles')
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="nav-container">
            <div class="logo">
                <a href="{{ route('home') }}">
                    NRO <span>BIGK</span>
                </a>
            </div>

            <!-- Server Status Widget -->
            <div class="server-status-widget" style="display: flex; gap: 10px; align-items: center; font-size: 0.75rem; font-family: 'Orbitron', sans-serif; background: rgba(0,0,0,0.4); padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(255,107,0,0.15);">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #00e676; box-shadow: 0 0 6px #00e676;"></span>
                    <span style="color: #00e676; font-weight: 500;">WEB</span>
                </div>
                <div style="height: 10px; width: 1px; background: rgba(255,255,255,0.15);"></div>
                <div style="display: flex; align-items: center; gap: 5px;">
                    @if($game_server_online)
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #00e676; box-shadow: 0 0 6px #00e676;"></span>
                        <span style="color: #00e676; font-weight: 500;">GAME</span>
                        <span style="color: #fff; font-size: 0.65rem; background: rgba(255,107,0,0.2); padding: 0px 4px; border-radius: 4px; border: 1px solid rgba(255,107,0,0.3);">{{ number_format($online_players) }} ON</span>
                    @else
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #ff1744; box-shadow: 0 0 6px #ff1744;"></span>
                        <span style="color: #ff1744; font-weight: 500;">OFFLINE</span>
                    @endif
                </div>
            </div>
            
            <ul class="nav-menu">
                <li><a href="{{ route('home') }}" class="nav-link {{ Route::is('home') ? 'active' : '' }}">Trang Chủ</a></li>
                <li><a href="{{ route('ranking') }}" class="nav-link {{ Route::is('ranking') ? 'active' : '' }}">Xếp Hạng</a></li>
                <li><a href="{{ route('acc_sales.index') }}" class="nav-link {{ Route::is('acc_sales*') ? 'active' : '' }}">Chợ Acc</a></li>
                <li><a href="{{ route('guide') }}" class="nav-link {{ Route::is('guide*') ? 'active' : '' }}">Hướng Dẫn</a></li>
                <li><a href="{{ route('download') }}" class="nav-link {{ Route::is('download') ? 'active' : '' }}">Tải Game</a></li>
                
                @auth
                    <li><a href="{{ route('shop') }}" class="nav-link {{ Route::is('shop') ? 'active' : '' }}">Cửa Hàng</a></li>
                    <li><a href="{{ route('spin') }}" class="nav-link {{ Route::is('spin') ? 'active' : '' }}">Vòng Quay</a></li>
                    <li><a href="{{ route('dashboard') }}" class="nav-link nav-btn {{ Route::is('dashboard') ? 'active' : '' }}">Tài Khoản</a></li>
                    
                    @if(Auth::user()->is_admin == 1)
                        <li><a href="{{ route('admin.dashboard') }}" class="nav-link nav-btn btn-sec">Admin</a></li>
                    @endif
                    
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer;">Thoát</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="nav-link {{ Route::is('login') ? 'active' : '' }}">Đăng Nhập</a></li>
                    <li><a href="{{ route('register') }}" class="nav-btn">Đăng Ký</a></li>
                @endauth
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <main style="min-height: calc(100vh - 180px); padding: 40px 0;">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer style="background: #020205; border-top: 1px solid rgba(255, 107, 0, 0.1); padding: 30px 0; text-align: center; color: var(--text-muted);">
        <div class="container">
            <p style="font-family: 'Orbitron', sans-serif; font-size: 0.95rem; margin-bottom: 8px; color: #fff;">
                &copy; {{ date('Y') }} NRO BIGK. ALL RIGHTS RESERVED.
            </p>
            <p style="font-size: 0.9rem;">
                Hệ thống game được vận hành bởi đội ngũ Ngọc Rồng BigK. Sản phẩm phi lợi nhuận, phi thương mại.
            </p>
        </div>
    </footer>

    <!-- Global Alert notification popup -->
    <div id="global-alert" class="alert-popup">
        <span id="global-alert-icon"></span>
        <span id="global-alert-message"></span>
    </div>

    <!-- CSRF and global alert JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Set up CSRF token for Fetch requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            window.csrfFetch = async (url, options = {}) => {
                options.headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {})
                };
                return fetch(url, options);
            };

            // Global alert function
            window.showAlert = (message, type = 'success') => {
                const alertEl = document.getElementById('global-alert');
                const msgEl = document.getElementById('global-alert-message');
                const iconEl = document.getElementById('global-alert-icon');
                
                alertEl.className = `alert-popup ${type} show`;
                msgEl.innerText = message;
                
                if (type === 'success') {
                    iconEl.innerHTML = '✔';
                } else {
                    iconEl.innerHTML = '⚠';
                }

                setTimeout(() => {
                    alertEl.classList.remove('show');
                }, 4000);
            };
            
            // Show session messages if available
            @if(session('success'))
                window.showAlert("{{ session('success') }}", 'success');
            @endif
            @if(session('error'))
                window.showAlert("{{ session('error') }}", 'error');
            @endif
        });
    </script>
    
    @yield('scripts')
</body>
</html>
