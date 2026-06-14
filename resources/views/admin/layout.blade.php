@extends('layouts.app')

@section('title', 'Admin Panel - NRO BigK')

@section('content')
<div class="admin-layout">
    
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <h3 style="color: var(--primary); font-size: 1.1rem; margin-bottom: 15px; padding-left: 16px; font-family: 'Orbitron', sans-serif;">QUẢN TRỊ VIÊN</h3>
        <ul class="admin-menu">
            <li class="admin-menu-item">
                <a href="{{ route('admin.dashboard') }}" class="admin-menu-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    📊 THỐNG KÊ
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.players') }}" class="admin-menu-link {{ Route::is('admin.players') ? 'active' : '' }}">
                    👥 THÀNH VIÊN
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.shop') }}" class="admin-menu-link {{ Route::is('admin.shop') ? 'active' : '' }}">
                    🛒 CỬA HÀNG
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.giftcodes') }}" class="admin-menu-link {{ Route::is('admin.giftcodes') ? 'active' : '' }}">
                    🎁 GIFT CODES
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.items.lookup') }}" class="admin-menu-link {{ Route::is('admin.items.lookup*') ? 'active' : '' }}">
                    📦 TRA CỨU VẬT PHẨM
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.spin') }}" class="admin-menu-link {{ Route::is('admin.spin') ? 'active' : '' }}">
                    🎡 VÒNG QUAY
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.posts') }}" class="admin-menu-link {{ Route::is('admin.posts') ? 'active' : '' }}">
                    📝 HƯỚNG DẪN
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.recharges') }}" class="admin-menu-link {{ Route::is('admin.recharges') ? 'active' : '' }}">
                    💳 LỊCH SỬ NẠP
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.acc_sales') }}" class="admin-menu-link {{ Route::is('admin.acc_sales') ? 'active' : '' }}">
                    🛡️ QUẢN LÝ CHỢ ACC
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.downloads') }}" class="admin-menu-link {{ Route::is('admin.downloads') ? 'active' : '' }}">
                    💾 FILE TẢI GAME
                </a>
            </li>
        </ul>
    </div>

    <!-- Admin Content Area -->
    <div>
        @yield('admin_content')
    </div>

</div>
@endsection
