@extends('layouts.app')

@section('title', 'Admin Panel - NRO BigK')

@section('content')
@php
    $isSystemActive = Route::is('admin.dashboard') || Route::is('admin.players*') || Route::is('admin.downloads*') || Route::is('admin.posts*');
    $isGameActive = Route::is('admin.bosses*') || Route::is('admin.bots*') || Route::is('admin.map_editor*') || Route::is('admin.drops*') || Route::is('admin.achievements*') || Route::is('admin.combine-rate*');
    $isEconomyActive = Route::is('admin.items.lookup*') || Route::is('admin.shop*') || Route::is('admin.web_chest*') || Route::is('admin.giftcodes*') || Route::is('admin.acc_sales*');
    $isEventActive = Route::is('admin.spin*');
@endphp

<div class="admin-layout">
    
    <!-- Collapsible Grouped Sidebar -->
    <div class="admin-sidebar" style="padding: 15px 12px; background: rgba(14, 17, 24, 0.95); border: 1px solid rgba(255, 107, 0, 0.25); border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
        
        <!-- Sidebar Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px 14px 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">⚡</span>
                <span style="color: var(--primary); font-size: 0.95rem; font-weight: 800; font-family: 'Orbitron', sans-serif; letter-spacing: 0.5px;">ADMIN PANEL</span>
            </div>
            <button type="button" onclick="toggleAllGroups()" title="Mở rộng / Thu gọn tất cả" style="background: none; border: none; color: #888; font-size: 0.8rem; cursor: pointer; padding: 4px; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#888'">
                ⇕ Gom gọn
            </button>
        </div>

        <div class="sidebar-accordion" style="display: flex; flex-direction: column; gap: 8px;">

            <!-- ================= GROUP 1: HỆ THỐNG & TÀI KHOẢN ================= -->
            <div class="sidebar-group {{ $isSystemActive ? 'is-active is-open' : '' }}" id="group-system">
                <button type="button" class="group-header" onclick="toggleGroup('group-system')" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: {{ $isSystemActive ? 'linear-gradient(90deg, rgba(255,107,0,0.18), rgba(255,107,0,0.04))' : 'rgba(255,255,255,0.03)' }}; border: 1px solid {{ $isSystemActive ? 'rgba(255,107,0,0.4)' : 'rgba(255,255,255,0.06)' }}; border-radius: 8px; color: {{ $isSystemActive ? '#ff9800' : '#ddd' }}; cursor: pointer; font-family: 'Orbitron', sans-serif; font-size: 0.82rem; font-weight: 700; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>⚙️</span>
                        <span>HỆ THỐNG</span>
                        @if($isSystemActive)
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #00e676; box-shadow: 0 0 6px #00e676;"></span>
                        @endif
                    </div>
                    <span class="group-arrow" style="font-size: 0.75rem; transition: transform 0.25s; {{ $isSystemActive ? 'transform: rotate(0deg);' : 'transform: rotate(-90deg);' }}">▼</span>
                </button>
                <ul class="group-items" style="list-style: none; padding: 4px 0 2px 8px; margin: 0; {{ $isSystemActive ? 'display: block;' : 'display: none;' }}">
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.dashboard') }}" class="admin-menu-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                            📊 THỐNG KÊ
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.players') }}" class="admin-menu-link {{ Route::is('admin.players') ? 'active' : '' }}">
                            👥 THÀNH VIÊN & ITEM
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.downloads') }}" class="admin-menu-link {{ Route::is('admin.downloads*') ? 'active' : '' }}">
                            💾 FILE TẢI GAME
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.posts') }}" class="admin-menu-link {{ Route::is('admin.posts*') ? 'active' : '' }}">
                            📝 HƯỚNG DẪN & BÀI VIẾT
                        </a>
                    </li>
                </ul>
            </div>

            <!-- ================= GROUP 2: QUẢN LÝ GAME & MAP ================= -->
            <div class="sidebar-group {{ $isGameActive ? 'is-active is-open' : '' }}" id="group-game">
                <button type="button" class="group-header" onclick="toggleGroup('group-game')" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: {{ $isGameActive ? 'linear-gradient(90deg, rgba(255,107,0,0.18), rgba(255,107,0,0.04))' : 'rgba(255,255,255,0.03)' }}; border: 1px solid {{ $isGameActive ? 'rgba(255,107,0,0.4)' : 'rgba(255,255,255,0.06)' }}; border-radius: 8px; color: {{ $isGameActive ? '#ff9800' : '#ddd' }}; cursor: pointer; font-family: 'Orbitron', sans-serif; font-size: 0.82rem; font-weight: 700; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>⚔️</span>
                        <span>QUẢN LÝ GAME</span>
                        @if($isGameActive)
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #00e676; box-shadow: 0 0 6px #00e676;"></span>
                        @endif
                    </div>
                    <span class="group-arrow" style="font-size: 0.75rem; transition: transform 0.25s; {{ $isGameActive ? 'transform: rotate(0deg);' : 'transform: rotate(-90deg);' }}">▼</span>
                </button>
                <ul class="group-items" style="list-style: none; padding: 4px 0 2px 8px; margin: 0; {{ $isGameActive ? 'display: block;' : 'display: none;' }}">
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.bosses') }}" class="admin-menu-link {{ Route::is('admin.bosses*') ? 'active' : '' }}">
                            👹 QUẢN LÝ BOSS
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.bots') }}" class="admin-menu-link {{ Route::is('admin.bots*') ? 'active' : '' }}">
                            🤖 QUẢN LÝ BOT AI
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.map_editor') }}" class="admin-menu-link {{ Route::is('admin.map_editor*') ? 'active' : '' }}">
                            🗺️ MAP EDITOR 2D
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.drops') }}" class="admin-menu-link {{ Route::is('admin.drops*') ? 'active' : '' }}">
                            🎁 RƠI ĐỒ QUÁI (DROPS)
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.achievements') }}" class="admin-menu-link {{ Route::is('admin.achievements*') ? 'active' : '' }}">
                            🎯 NHIỆM VỤ BÒ MỘNG
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.combine-rate') }}" class="admin-menu-link {{ Route::is('admin.combine-rate*') ? 'active' : '' }}">
                            🔨 TỈ LỆ ĐẬP ĐỒ
                        </a>
                    </li>
                </ul>
            </div>

            <!-- ================= GROUP 3: VẬT PHẨM & KINH TẾ ================= -->
            <div class="sidebar-group {{ $isEconomyActive ? 'is-active is-open' : '' }}" id="group-economy">
                <button type="button" class="group-header" onclick="toggleGroup('group-economy')" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: {{ $isEconomyActive ? 'linear-gradient(90deg, rgba(255,107,0,0.18), rgba(255,107,0,0.04))' : 'rgba(255,255,255,0.03)' }}; border: 1px solid {{ $isEconomyActive ? 'rgba(255,107,0,0.4)' : 'rgba(255,255,255,0.06)' }}; border-radius: 8px; color: {{ $isEconomyActive ? '#ff9800' : '#ddd' }}; cursor: pointer; font-family: 'Orbitron', sans-serif; font-size: 0.82rem; font-weight: 700; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>💰</span>
                        <span>VẬT PHẨM & KINH TẾ</span>
                        @if($isEconomyActive)
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #00e676; box-shadow: 0 0 6px #00e676;"></span>
                        @endif
                    </div>
                    <span class="group-arrow" style="font-size: 0.75rem; transition: transform 0.25s; {{ $isEconomyActive ? 'transform: rotate(0deg);' : 'transform: rotate(-90deg);' }}">▼</span>
                </button>
                <ul class="group-items" style="list-style: none; padding: 4px 0 2px 8px; margin: 0; {{ $isEconomyActive ? 'display: block;' : 'display: none;' }}">
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.items.lookup') }}" class="admin-menu-link {{ Route::is('admin.items.lookup*') ? 'active' : '' }}">
                            📦 TRA CỨU VẬT PHẨM
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.shop') }}" class="admin-menu-link {{ Route::is('admin.shop*') ? 'active' : '' }}">
                            🛒 CỬA HÀNG WEB
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.web_chest') }}" class="admin-menu-link {{ Route::is('admin.web_chest*') ? 'active' : '' }}">
                            💼 RƯƠNG ĐỒ WEB
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.giftcodes') }}" class="admin-menu-link {{ Route::is('admin.giftcodes*') ? 'active' : '' }}">
                            🎁 GIFT CODES
                        </a>
                    </li>
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.acc_sales') }}" class="admin-menu-link {{ Route::is('admin.acc_sales*') ? 'active' : '' }}">
                            🛡️ QUẢN LÝ CHỢ ACC
                        </a>
                    </li>
                </ul>
            </div>

            <!-- ================= GROUP 4: VÒNG QUAY & SỰ KIỆN ================= -->
            <div class="sidebar-group {{ $isEventActive ? 'is-active is-open' : '' }}" id="group-event">
                <button type="button" class="group-header" onclick="toggleGroup('group-event')" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: {{ $isEventActive ? 'linear-gradient(90deg, rgba(255,107,0,0.18), rgba(255,107,0,0.04))' : 'rgba(255,255,255,0.03)' }}; border: 1px solid {{ $isEventActive ? 'rgba(255,107,0,0.4)' : 'rgba(255,255,255,0.06)' }}; border-radius: 8px; color: {{ $isEventActive ? '#ff9800' : '#ddd' }}; cursor: pointer; font-family: 'Orbitron', sans-serif; font-size: 0.82rem; font-weight: 700; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>🎡</span>
                        <span>VÒNG QUAY & EVENT</span>
                        @if($isEventActive)
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #00e676; box-shadow: 0 0 6px #00e676;"></span>
                        @endif
                    </div>
                    <span class="group-arrow" style="font-size: 0.75rem; transition: transform 0.25s; {{ $isEventActive ? 'transform: rotate(0deg);' : 'transform: rotate(-90deg);' }}">▼</span>
                </button>
                <ul class="group-items" style="list-style: none; padding: 4px 0 2px 8px; margin: 0; {{ $isEventActive ? 'display: block;' : 'display: none;' }}">
                    <li style="margin-top: 4px;">
                        <a href="{{ route('admin.spin') }}" class="admin-menu-link {{ Route::is('admin.spin*') ? 'active' : '' }}">
                            🎡 CẤU HÌNH VÒNG QUAY
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Admin Content Area -->
    <div style="min-width: 0;">
        @yield('admin_content')
    </div>

</div>

<!-- Sidebar Interactive Accordion Script -->
<script>
function toggleGroup(groupId) {
    const group = document.getElementById(groupId);
    if (!group) return;
    const items = group.querySelector('.group-items');
    const arrow = group.querySelector('.group-arrow');
    const isOpen = items.style.display !== 'none';

    if (isOpen) {
        items.style.display = 'none';
        arrow.style.transform = 'rotate(-90deg)';
        group.classList.remove('is-open');
    } else {
        items.style.display = 'block';
        arrow.style.transform = 'rotate(0deg)';
        group.classList.add('is-open');
    }
}

let allGroupsExpanded = false;
function toggleAllGroups() {
    allGroupsExpanded = !allGroupsExpanded;
    const groups = document.querySelectorAll('.sidebar-group');
    groups.forEach(g => {
        const items = g.querySelector('.group-items');
        const arrow = g.querySelector('.group-arrow');
        if (items && arrow) {
            items.style.display = allGroupsExpanded ? 'block' : 'none';
            arrow.style.transform = allGroupsExpanded ? 'rotate(0deg)' : 'rotate(-90deg)';
            if (allGroupsExpanded) g.classList.add('is-open');
            else g.classList.remove('is-open');
        }
    });
}
</script>
@endsection
