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
                    👥 THÀNH VIÊN & ITEM
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.bosses') }}" class="admin-menu-link {{ Route::is('admin.bosses*') ? 'active' : '' }}">
                    👹 QUẢN LÝ BOSS
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.bots') }}" class="admin-menu-link {{ Route::is('admin.bots*') ? 'active' : '' }}">
                    🤖 QUẢN LÝ BOT AI
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.combine-rate') }}" class="admin-menu-link {{ Route::is('admin.combine-rate*') ? 'active' : '' }}">
                    🔨 TỈ LỆ ĐẬP ĐỒ
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.achievements') }}" class="admin-menu-link {{ Route::is('admin.achievements*') ? 'active' : '' }}">
                    🎯 NHIỆM VỤ BÒ MỘNG
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.drops') }}" class="admin-menu-link {{ Route::is('admin.drops*') ? 'active' : '' }}">
                    🎁 RƠI ĐỒ QUÁI (DROPS)
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="{{ route('admin.map_editor') }}" class="admin-menu-link {{ Route::is('admin.map_editor*') ? 'active' : '' }}">
                    🗺️ MAP EDITOR 2D
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
                <a href="{{ route('admin.web_chest') }}" class="admin-menu-link {{ Route::is('admin.web_chest') ? 'active' : '' }}">
                    💼 RƯƠNG ĐỒ WEB
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
            {{-- Lịch sử nạp disabled --}}
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
