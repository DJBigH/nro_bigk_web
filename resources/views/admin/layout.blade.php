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
            <button type="button" id="toggleAllBtn" onclick="toggleAllGroups()" title="Thu gọn tất cả các nhóm menu" style="background: none; border: none; color: #888; font-size: 0.8rem; cursor: pointer; padding: 4px; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#888'">
                ↕ Gom gọn
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

<!-- Global Cyberpunk Confirmation Modal (Đồng ý / Từ chối) -->
<div id="cyberConfirmModal" style="display: none; position: fixed; inset: 0; background: rgba(3, 7, 18, 0.88); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 15px;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid var(--primary); box-shadow: 0 0 35px rgba(255, 107, 0, 0.35); background: #0c101c; border-radius: 12px; padding: 24px; animation: modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px;">
            <div id="cyberConfirmIcon" style="font-size: 1.4rem;">⚠️</div>
            <h3 id="cyberConfirmTitle" style="color: #fff; font-family: 'Orbitron', sans-serif; font-size: 1.05rem; margin: 0; letter-spacing: 0.5px;">XÁC NHẬN THAO TÁC</h3>
        </div>
        <p id="cyberConfirmMessage" style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin: 0 0 22px 0;">
            Bạn có chắc chắn muốn thực hiện hành động này?
        </p>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button type="button" id="cyberConfirmCancelBtn" class="btn btn-secondary" style="padding: 9px 20px; font-weight: 600; font-size: 0.88rem; border-color: rgba(255,255,255,0.2); min-width: 100px;">
                Từ chối
            </button>
            <button type="button" id="cyberConfirmOkBtn" class="btn" style="padding: 9px 22px; font-weight: 700; font-size: 0.88rem; background: var(--primary); box-shadow: 0 0 15px rgba(255,107,0,0.4); min-width: 110px;">
                Đồng ý
            </button>
        </div>
    </div>
</div>

<!-- Global Cyberpunk Alert Modal -->
<div id="cyberAlertModal" style="display: none; position: fixed; inset: 0; background: rgba(3, 7, 18, 0.88); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 15px;">
    <div class="card" style="max-width: 460px; width: 100%; border: 1px solid #00bcd4; box-shadow: 0 0 35px rgba(0, 188, 212, 0.35); background: #0c101c; border-radius: 12px; padding: 24px; animation: modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px;">
            <div id="cyberAlertIcon" style="font-size: 1.4rem;">ℹ️</div>
            <h3 id="cyberAlertTitle" style="color: #00e5ff; font-family: 'Orbitron', sans-serif; font-size: 1.05rem; margin: 0; letter-spacing: 0.5px;">THÔNG BÁO</h3>
        </div>
        <p id="cyberAlertMessage" style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin: 0 0 22px 0;">
            Nội dung thông báo
        </p>
        <div style="display: flex; justify-content: flex-end;">
            <button type="button" id="cyberAlertOkBtn" class="btn" style="padding: 9px 24px; font-weight: 700; font-size: 0.88rem; background: #00bcd4; box-shadow: 0 0 15px rgba(0,188,212,0.4); min-width: 110px;">
                ĐÃ HIỂU
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<!-- Sidebar Interactive Accordion & Global Modal Script -->
<script>
function updateToggleAllBtnState() {
    const btn = document.getElementById('toggleAllBtn');
    if (!btn) return;
    const openGroups = document.querySelectorAll('.sidebar-group.is-open');
    if (openGroups.length === 0) {
        btn.innerHTML = '↕ Mở rộng';
        btn.title = 'Mở rộng tất cả các nhóm menu';
    } else {
        btn.innerHTML = '↕ Gom gọn';
        btn.title = 'Thu gọn tất cả các nhóm menu';
    }
}

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
    updateToggleAllBtnState();
}

function toggleAllGroups() {
    const openGroups = document.querySelectorAll('.sidebar-group.is-open');
    const shouldCollapse = openGroups.length > 0;
    const groups = document.querySelectorAll('.sidebar-group');

    groups.forEach(g => {
        const items = g.querySelector('.group-items');
        const arrow = g.querySelector('.group-arrow');
        if (items && arrow) {
            if (shouldCollapse) {
                items.style.display = 'none';
                arrow.style.transform = 'rotate(-90deg)';
                g.classList.remove('is-open');
            } else {
                items.style.display = 'block';
                arrow.style.transform = 'rotate(0deg)';
                g.classList.add('is-open');
            }
        }
    });

    updateToggleAllBtnState();
}

/**
 * Global Cyberpunk Confirmation Dialog with "Đồng ý" and "Từ chối"
 * Returns Promise<boolean> or invokes callback onOk / onCancel
 */
window.showConfirm = function(options) {
    return new Promise((resolve) => {
        const opts = (typeof options === 'string') ? { message: options } : (options || {});
        const title = opts.title || 'XÁC NHẬN THAO TÁC';
        const message = opts.message || 'Bạn có chắc chắn muốn thực hiện hành động này?';
        const okText = opts.okText || 'Đồng ý';
        const cancelText = opts.cancelText || 'Từ chối';
        const isDanger = opts.isDanger !== false; // Default true for security/admin actions

        const modal = document.getElementById('cyberConfirmModal');
        const titleEl = document.getElementById('cyberConfirmTitle');
        const msgEl = document.getElementById('cyberConfirmMessage');
        const iconEl = document.getElementById('cyberConfirmIcon');
        const okBtn = document.getElementById('cyberConfirmOkBtn');
        const cancelBtn = document.getElementById('cyberConfirmCancelBtn');

        titleEl.innerText = title;
        msgEl.innerHTML = message;
        okBtn.innerText = okText;
        cancelBtn.innerText = cancelText;

        if (isDanger) {
            iconEl.innerText = '⚠️';
            okBtn.style.background = '#e53935';
            okBtn.style.boxShadow = '0 0 15px rgba(229,57,53,0.5)';
        } else {
            iconEl.innerText = '⚡';
            okBtn.style.background = 'var(--primary)';
            okBtn.style.boxShadow = '0 0 15px rgba(255,107,0,0.5)';
        }

        modal.style.display = 'flex';

        const cleanup = () => {
            modal.style.display = 'none';
            okBtn.onclick = null;
            cancelBtn.onclick = null;
        };

        okBtn.onclick = () => {
            cleanup();
            if (typeof opts.onOk === 'function') opts.onOk();
            resolve(true);
        };

        cancelBtn.onclick = () => {
            cleanup();
            if (typeof opts.onCancel === 'function') opts.onCancel();
            resolve(false);
        };
    });
};

/**
 * Global Cyberpunk Alert Modal (replacement for alert)
 */
window.showModalAlert = function(title, message, type = 'info') {
    return new Promise((resolve) => {
        let msg = message;
        let t = title;
        if (msg === undefined) {
            msg = t;
            t = 'THÔNG BÁO';
        }

        const modal = document.getElementById('cyberAlertModal');
        const titleEl = document.getElementById('cyberAlertTitle');
        const msgEl = document.getElementById('cyberAlertMessage');
        const iconEl = document.getElementById('cyberAlertIcon');
        const okBtn = document.getElementById('cyberAlertOkBtn');

        titleEl.innerText = t;
        msgEl.innerHTML = msg;

        if (type === 'success') {
            iconEl.innerText = '✅';
            titleEl.style.color = '#00e676';
            okBtn.style.background = '#00c853';
            okBtn.style.boxShadow = '0 0 15px rgba(0,200,83,0.5)';
        } else if (type === 'error') {
            iconEl.innerText = '❌';
            titleEl.style.color = '#ff5252';
            okBtn.style.background = '#d32f2f';
            okBtn.style.boxShadow = '0 0 15px rgba(211,47,47,0.5)';
        } else {
            iconEl.innerText = 'ℹ️';
            titleEl.style.color = '#00e5ff';
            okBtn.style.background = '#00bcd4';
            okBtn.style.boxShadow = '0 0 15px rgba(0,188,212,0.5)';
        }

        modal.style.display = 'flex';

        okBtn.onclick = () => {
            modal.style.display = 'none';
            okBtn.onclick = null;
            resolve(true);
        };
    });
};
</script>
@endsection
