@extends('admin.layout')

@section('title', 'Quản Lý Thành Viên - Cyber Admin NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 25px;">
    <h2 style="color: var(--primary); margin-bottom: 12px; font-size: 1.3rem;">QUẢN LÝ THÀNH VIÊN & ĐIỀU CHỈNH CHỈ SỐ REALTIME</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">Hệ thống hỗ trợ bơm Thỏi Vàng, Hồng Ngọc, Item và set chỉ số nhân vật (lên tới 50 TỶ HP/MP/Dame, 200 TỶ Sức Mạnh) trực tiếp vào RAM không cần out game.</p>
    
    <!-- Search Form -->
    <form action="{{ route('admin.players') }}" method="GET" style="display: flex; gap: 15px;">
        <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Tìm theo username tài khoản hoặc tên nhân vật game..." style="flex: 1;">
        <button type="submit" class="btn">TÌM KIẾM</button>
        @if($search)
            <a href="{{ route('admin.players') }}" class="btn btn-secondary">HỦY</a>
        @endif
    </form>
</div>

<!-- Players List -->
<div class="card" style="padding: 0;">
    @if($accounts->isEmpty())
        <p style="padding: 25px; text-align: center; color: var(--text-muted);">Không tìm thấy người chơi nào khớp với từ khóa.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tài Khoản</th>
                    <th>Nhân Vật Game</th>
                    <th>Sức Mạnh / TNSM</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right;">Hành Động Realtime</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $acc)
                    @php
                        $player = $acc->players->first();
                    @endphp
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $acc->id }}</td>
                        <td style="font-weight: 700; color: #fff;">{{ $acc->username }}</td>
                        <td>
                            @if($player)
                                <span style="color: var(--primary); font-weight: 700;">{{ $player->name }}</span>
                                <div style="color: #ff5252; font-size: 0.85rem; font-family: 'Orbitron', sans-serif;">
                                    HP/KI Trần: 50 TỶ
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Chưa tạo nhân vật</span>
                            @endif
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif;">
                            @if($player)
                                <div style="color: #ffb74d; font-weight: 700;">{{ number_format($player->power) }} SM</div>
                                <div style="color: #888; font-size: 0.8rem;">(Max 200 Tỷ)</div>
                            @else
                                <span style="color: #888;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($acc->ban > 0)
                                <span class="badge badge-danger" id="status-badge-{{ $acc->id }}">ĐÃ KHÓA</span>
                            @else
                                <span class="badge badge-success" id="status-badge-{{ $acc->id }}">HOẠT ĐỘNG</span>
                            @endif
                            
                            @if($acc->is_admin == 1)
                                <span class="badge badge-primary">ADMIN</span>
                            @endif
                        </td>
                        <td style="text-align: right; vertical-align: middle; white-space: nowrap;">
                            <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button class="btn" style="padding: 5px 10px; font-size: 0.78rem; background: #e91e63; font-weight: 700; white-space: nowrap;" onclick="openBuffModal({{ $acc->id }}, '{{ $acc->username }}')">
                                        🎁 BUFF ĐỒ / NGỌC
                                    </button>
                                    @if($player)
                                        <button class="btn" style="padding: 5px 10px; font-size: 0.78rem; background: #00bcd4; font-weight: 700; white-space: nowrap;" onclick="openPointModal({{ $acc->id }}, '{{ $acc->username }}', '{{ $player->name }}')">
                                            ⚡ SET CHỈ SỐ
                                        </button>
                                        <button class="btn" style="padding: 5px 10px; font-size: 0.78rem; background: #673ab7; font-weight: 700; white-space: nowrap;" onclick="openInspectModal({{ $acc->id }}, '{{ $acc->username }}', '{{ $player->name }}')">
                                            🔍 SOI ĐỒ
                                        </button>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button class="btn" style="padding: 4px 10px; font-size: 0.75rem; background: #ff9800; font-weight: 600; white-space: nowrap;" onclick="kickPlayer({{ $acc->id }}, '{{ $acc->username }}')">
                                        👟 KICK
                                    </button>
                                    @if($acc->is_admin == 1)
                                        <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;" onclick="toggleAdmin({{ $acc->id }}, 'remove', '{{ $acc->username }}')">
                                            - Admin
                                        </button>
                                    @else
                                        <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;" onclick="toggleAdmin({{ $acc->id }}, 'set', '{{ $acc->username }}')">
                                            + Admin
                                        </button>
                                    @endif
                                    @if($acc->ban > 0)
                                        <button class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; background: #4caf50; font-weight: 600; white-space: nowrap;" onclick="toggleBan({{ $acc->id }}, 'unban', '{{ $acc->username }}')" id="ban-btn-{{ $acc->id }}">
                                            🔓 Mở Khóa
                                        </button>
                                    @else
                                        <button class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;" onclick="toggleBan({{ $acc->id }}, 'ban', '{{ $acc->username }}')" id="ban-btn-{{ $acc->id }}">
                                            🔒 Khóa
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $accounts->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>

<!-- Modal Buff Vật Phẩm / Tiền Tệ -->
<div id="buffModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; border: 1px solid var(--primary); box-shadow: 0 0 25px rgba(255,107,0,0.3);">
        <h3 style="color: var(--primary); margin-bottom: 15px; font-size: 1.15rem;" id="buffModalTitle">BƠM VẬT PHẨM & TIỀN TỆ (REALTIME)</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Vật phẩm và ngọc sẽ được cộng trực tiếp vào túi người chơi đang online.</p>
        
        <form id="buffForm">
            <input type="hidden" id="buffAccountId">
            <div style="margin-bottom: 12px;">
                <label style="color: #fff; font-size: 0.85rem;">Hồng Ngọc (Ruby):</label>
                <input type="number" id="buffRuby" class="form-input" style="width: 100%;" placeholder="Nhập số lượng Hồng Ngọc..." value="0" min="0">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="color: #fff; font-size: 0.85rem;">Thỏi Vàng (Item ID 457):</label>
                <input type="number" id="buffThoiVang" class="form-input" style="width: 100%;" placeholder="Nhập số Thỏi Vàng..." value="0" min="0">
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-bottom: 20px;">
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">Item Template ID:</label>
                    <input type="number" id="buffItemId" class="form-input" style="width: 100%;" placeholder="ID vật phẩm (tuỳ chọn)..." value="0">
                </div>
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">Số Lượng:</label>
                    <input type="number" id="buffItemQty" class="form-input" style="width: 100%;" placeholder="SL..." value="1" min="1">
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeBuffModal()">HỦY</button>
                <button type="submit" class="btn" style="background: #e91e63;">🚀 BƠM TRỰC TIẾP</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Set Chỉ Số Realtime (Lên tới 50 TỶ) -->
<div id="pointModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); align-items: center; justify-content: center;">
    <div class="card" style="max-width: 550px; width: 90%; border: 1px solid #00bcd4; box-shadow: 0 0 25px rgba(0,188,212,0.3);">
        <h3 style="color: #00bcd4; margin-bottom: 10px; font-size: 1.15rem;" id="pointModalTitle">ĐIỀU CHỈNH CHỈ SỐ NHÂN VẬT (REALTIME)</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Hệ thống hỗ trợ nâng trần chỉ số lên tới <strong style="color: #ff5252;">50 TỶ HP/MP/Dame</strong> và <strong style="color: #ffb74d;">200 TỶ Sức Mạnh</strong>!</p>
        
        <form id="pointForm">
            <input type="hidden" id="pointAccountId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">HP Gốc (Máu tối đa 50 Tỷ):</label>
                    <input type="number" id="pointHp" class="form-input" style="width: 100%;" placeholder="VD: 50000000000" max="50000000000">
                </div>
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">KI Gốc (Năng lượng 50 Tỷ):</label>
                    <input type="number" id="pointMp" class="form-input" style="width: 100%;" placeholder="VD: 50000000000" max="50000000000">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">Sức Đánh Gốc (50 Tỷ):</label>
                    <input type="number" id="pointDame" class="form-input" style="width: 100%;" placeholder="VD: 100000000" max="50000000000">
                </div>
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">Giáp Gốc (Max 32.000):</label>
                    <input type="number" id="pointDef" class="form-input" style="width: 100%;" placeholder="VD: 10000" max="32000">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">Sức Mạnh (Max 200 Tỷ):</label>
                    <input type="number" id="pointPower" class="form-input" style="width: 100%;" placeholder="VD: 200000000000" max="200000000000">
                </div>
                <div>
                    <label style="color: #fff; font-size: 0.85rem;">Tiềm Năng (Max 200 Tỷ):</label>
                    <input type="number" id="pointTiemNang" class="form-input" style="width: 100%;" placeholder="VD: 200000000000" max="200000000000">
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closePointModal()">HỦY</button>
                <button type="submit" class="btn" style="background: #00bcd4;">⚡ CẬP NHẬT REALTIME</button>
            </div>
        </form>
    </div>
</div>

<script>
// Buff Modal
function openBuffModal(accId, username) {
    document.getElementById('buffAccountId').value = accId;
    document.getElementById('buffModalTitle').innerText = 'BƠM VẬT PHẨM & TIỀN CHO: ' + username;
    document.getElementById('buffModal').style.display = 'flex';
}
function closeBuffModal() {
    document.getElementById('buffModal').style.display = 'none';
}

document.getElementById('buffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('buffAccountId').value;
    const ruby = parseInt(document.getElementById('buffRuby').value) || 0;
    const thoiVang = parseInt(document.getElementById('buffThoiVang').value) || 0;
    const itemId = parseInt(document.getElementById('buffItemId').value) || 0;
    const qty = parseInt(document.getElementById('buffItemQty').value) || 1;

    fetch('/admin/players/' + id + '/buff', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ruby: ruby, thoi_vang: thoiVang, item_id: itemId, quantity: qty })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('KẾT QUẢ BƠM ĐỒ', d.message, d.success ? 'success' : 'error');
        if (d.success) closeBuffModal();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
});

// Point Modal
function openPointModal(accId, username, charName) {
    document.getElementById('pointAccountId').value = accId;
    document.getElementById('pointModalTitle').innerText = 'ĐIỀU CHỈNH CHỈ SỐ: ' + charName + ' (' + username + ')';
    document.getElementById('pointModal').style.display = 'flex';
}
function closePointModal() {
    document.getElementById('pointModal').style.display = 'none';
}

document.getElementById('pointForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('pointAccountId').value;
    const data = {};
    if (document.getElementById('pointHp').value) data.hp = parseInt(document.getElementById('pointHp').value);
    if (document.getElementById('pointMp').value) data.mp = parseInt(document.getElementById('pointMp').value);
    if (document.getElementById('pointDame').value) data.dame = parseInt(document.getElementById('pointDame').value);
    if (document.getElementById('pointDef').value) data.def = parseInt(document.getElementById('pointDef').value);
    if (document.getElementById('pointPower').value) data.power = parseInt(document.getElementById('pointPower').value);
    if (document.getElementById('pointTiemNang').value) data.tiem_nang = parseInt(document.getElementById('pointTiemNang').value);

    fetch('/admin/players/' + id + '/set-point', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('KẾT QUẢ ĐẶT CHỈ SỐ', d.message, d.success ? 'success' : 'error');
        if (d.success) closePointModal();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
});

// Kick Player with Cyberpunk Confirmation Modal
async function kickPlayer(id, username) {
    const confirmed = await window.showConfirm({
        title: 'KICK NGƯỜI CHƠI',
        message: `Bạn có đồng ý kick nhân vật <strong>"${username}"</strong> văng khỏi game không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch('/admin/players/' + id + '/kick', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => window.showModalAlert('KẾT QUẢ KICK', d.message, d.success ? 'success' : 'error'))
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}

// Toggle Ban with Cyberpunk Confirmation Modal
async function toggleBan(id, action, username) {
    const isBan = action === 'ban';
    const confirmed = await window.showConfirm({
        title: isBan ? 'KHÓA TÀI KHOẢN' : 'MỞ KHÓA TÀI KHOẢN',
        message: isBan 
            ? `Bạn có đồng ý <strong>KHÓA</strong> tài khoản <strong>"${username}"</strong> không? Nhân vật sẽ không thể đăng nhập game.`
            : `Bạn có đồng ý <strong>MỞ KHÓA</strong> cho tài khoản <strong>"${username}"</strong> không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: isBan
    });
    if (!confirmed) return;

    fetch('/admin/players/' + id + '/' + action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('THÀNH CÔNG', d.message, 'success');
        location.reload();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}

// Toggle Admin with Cyberpunk Confirmation Modal (Đồng ý / Từ chối)
async function toggleAdmin(id, action, username) {
    const isSet = action === 'set';
    const confirmed = await window.showConfirm({
        title: isSet ? 'CẤP QUYỀN ADMIN' : 'TƯỚC QUYỀN ADMIN',
        message: isSet 
            ? `Bạn có đồng ý <strong>CẤP QUYỀN ADMIN</strong> cho tài khoản <strong>"${username}"</strong> không?<br><span style="color:#ffb74d;font-size:0.85rem;">Tài khoản này sẽ có toàn quyền truy cập khu vực Quản Trị Viên.</span>`
            : `Bạn có đồng ý <strong>HỦY QUYỀN ADMIN</strong> của tài khoản <strong>"${username}"</strong> không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: !isSet
    });
    if (!confirmed) return;

    fetch('/admin/players/' + id + '/' + (isSet ? 'set-admin' : 'remove-admin'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('THÀNH CÔNG', d.message, d.success ? 'success' : 'error');
        location.reload();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}
</script>

<!-- Modal Item Inspector (Soi & Sửa Trang Bị Trực Tiếp) -->
<div id="inspectModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center;">
    <div class="card" style="max-width: 860px; width: 95%; max-height: 90vh; overflow-y: auto; border: 1px solid #673ab7; box-shadow: 0 0 30px rgba(103,58,183,0.35);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="color: #b388ff; font-size: 1.2rem; margin: 0;" id="inspectModalTitle">SOI & SỬA TRANG BỊ REALTIME</h3>
            <button type="button" class="btn btn-secondary" style="padding: 4px 10px;" onclick="closeInspectModal()">ĐÓNG ✕</button>
        </div>
        
        <input type="hidden" id="inspectAccountId">

        <!-- Tabs -->
        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <button type="button" id="tabBtnBody" class="btn" style="background: #673ab7; font-size: 0.85rem;" onclick="switchInspectTab('body')">👕 ĐỒ ĐANG MẶC (BODY)</button>
            <button type="button" id="tabBtnBag" class="btn btn-secondary" style="font-size: 0.85rem;" onclick="switchInspectTab('bag')">🎒 HÀNH TRANG (BAG)</button>
        </div>

        <!-- Items Container -->
        <div id="inspectItemsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <p style="color: #888; text-align: center; grid-column: 1 / -1;">Đang tải dữ liệu từ RAM Game Server...</p>
        </div>
    </div>
</div>

<script>
let currentInspectData = null;
let currentInspectTab = 'body';
let currentInspectAccId = null;
let currentInspectUsername = '';
let currentInspectCharName = '';

function openInspectModal(accId, username, charName) {
    currentInspectAccId = accId;
    currentInspectUsername = username;
    currentInspectCharName = charName;
    document.getElementById('inspectAccountId').value = accId;
    document.getElementById('inspectModalTitle').innerText = 'SOI & SỬA TRANG BỊ: ' + charName + ' (' + username + ')';
    document.getElementById('inspectItemsList').innerHTML = '<p style="color: #888; text-align: center; grid-column: 1 / -1;">Đang tải dữ liệu...</p>';
    document.getElementById('inspectModal').style.display = 'flex';

    fetchAndRenderItems(accId);
}

function fetchAndRenderItems(accId) {
    fetch('/admin/players/' + accId + '/items')
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('inspectItemsList').innerHTML = '<p style="color: #ff5252; text-align: center; grid-column: 1 / -1;">' + (d.message || 'Không thể lấy dữ liệu') + '</p>';
                return;
            }
            currentInspectData = d;
            const srcBadge = (d.source === 'db') 
                ? '<span class="badge" style="background: rgba(255, 152, 0, 0.2); color: #ff9800; border: 1px solid #ff9800; font-size: 0.75rem; margin-left: 8px;">💾 OFFLINE (DATABASE)</span>'
                : '<span class="badge" style="background: rgba(0, 230, 118, 0.2); color: #00e676; border: 1px solid #00e676; font-size: 0.75rem; margin-left: 8px;">⚡ ONLINE (RAM REALTIME)</span>';
            document.getElementById('inspectModalTitle').innerHTML = 'SOI & SỬA TRANG BỊ: ' + currentInspectCharName + ' (' + currentInspectUsername + ') ' + srcBadge;
            switchInspectTab(currentInspectTab);
        })
        .catch(() => {
            document.getElementById('inspectItemsList').innerHTML = '<p style="color: #ff5252; text-align: center; grid-column: 1 / -1;">Lỗi kết nối máy chủ</p>';
        });
}

function closeInspectModal() {
    document.getElementById('inspectModal').style.display = 'none';
}

function switchInspectTab(tab) {
    currentInspectTab = tab;
    if (tab === 'body') {
        document.getElementById('tabBtnBody').style.background = '#673ab7';
        document.getElementById('tabBtnBody').className = 'btn';
        document.getElementById('tabBtnBag').className = 'btn btn-secondary';
        document.getElementById('tabBtnBag').style.background = '';
    } else {
        document.getElementById('tabBtnBag').style.background = '#673ab7';
        document.getElementById('tabBtnBag').className = 'btn';
        document.getElementById('tabBtnBody').className = 'btn btn-secondary';
        document.getElementById('tabBtnBody').style.background = '';
    }

    if (!currentInspectData) return;
    const items = currentInspectData[tab] || [];
    const container = document.getElementById('inspectItemsList');

    if (items.length === 0) {
        container.innerHTML = '<p style="color: #888; text-align: center; grid-column: 1 / -1;">Không có món đồ nào trong ô này.</p>';
        return;
    }

    let html = '';
    items.forEach(it => {
        let optHtml = '';
        (it.options || []).forEach(opt => {
            optHtml += `
                <div class="item-option-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.8rem; background: rgba(0,0,0,0.35); padding: 5px 8px; border-radius: 5px; gap: 8px;">
                    <span style="color: #b388ff; flex: 1; font-weight: 500;" class="opt-label">${opt.name}</span>
                    <input type="hidden" class="opt-id-val" value="${opt.id}">
                    <input type="number" class="opt-param-val" value="${opt.param}" style="width: 75px; background: #161f30; border: 1px solid #555; color: #fff; padding: 3px 6px; text-align: right; border-radius: 4px;">
                    <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #ff5252; cursor: pointer; padding: 2px 4px; font-weight: bold; font-size: 0.9rem;" title="Xóa option này">✕</button>
                </div>
            `;
        });

        const safeItemName = (it.name || 'Vật phẩm').replace(/'/g, "\\'");

        html += `
            <div style="background: rgba(22, 31, 48, 0.95); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <img src="/images/icons/${it.iconId}.png" style="width: 28px; height: 28px; object-fit: contain;" onerror="this.onerror=null; this.src='/images/no_image.png';">
                            <strong style="color: #ffb74d; font-size: 0.92rem;">#${it.index} - ${it.name}</strong>
                        </div>
                        <span style="color: #888; font-size: 0.75rem;">ID: ${it.templateId}</span>
                    </div>

                    <div style="margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: #aaa; font-size: 0.82rem;">Số lượng món đồ:</span>
                        <input type="number" id="qty_${tab}_${it.index}" value="${it.quantity}" min="1" style="width: 80px; background: #161f30; border: 1px solid #555; color: #fff; padding: 3px 6px; text-align: right; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="color: #aaa; font-size: 0.8rem; font-weight: 600;">Chỉ số thuộc tính (Options):</span>
                            <button type="button" class="btn btn-secondary" style="padding: 2px 8px; font-size: 0.72rem; border-color: #b388ff; color: #b388ff;" onclick="addNewOptionRow('${tab}', ${it.index})">
                                ➕ Thêm Option
                            </button>
                        </div>
                        <div id="opt_list_${tab}_${it.index}">
                            ${optHtml || '<div class="no-opt-text" style="color: #666; font-size: 0.75rem; font-style: italic; margin-bottom: 4px;">Chưa có chỉ số nào</div>'}
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 8px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 10px;">
                    <button type="button" class="btn" style="font-size: 0.82rem; padding: 7px; background: #673ab7; font-weight: 700;" onclick="saveItemUpdate('${tab}', ${it.index})">
                        💾 LƯU MÓN ĐỒ
                    </button>
                    <button type="button" class="btn btn-danger" style="font-size: 0.82rem; padding: 7px; background: #d32f2f; font-weight: 700;" onclick="deleteItemSlot('${tab}', ${it.index}, '${safeItemName}')">
                        🗑️ XÓA ĐỒ
                    </button>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function addNewOptionRow(tab, index) {
    const list = document.getElementById(`opt_list_${tab}_${index}`);
    if (!list) return;

    // Remove no-opt placeholder if present
    const noOpt = list.querySelector('.no-opt-text');
    if (noOpt) noOpt.remove();

    const optionsTpl = (currentInspectData && currentInspectData.allOptions) ? currentInspectData.allOptions : [];
    let optionsSelectHtml = '<select class="opt-id-val form-input" style="flex: 1; padding: 3px 6px; font-size: 0.78rem; margin: 0; background: #161f30; border: 1px solid #673ab7; color: #fff;">';
    optionsTpl.forEach(op => {
        optionsSelectHtml += `<option value="${op.id}">#${op.id} - ${op.name}</option>`;
    });
    optionsSelectHtml += '</select>';

    const row = document.createElement('div');
    row.className = 'item-option-row';
    row.style = 'display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.8rem; background: rgba(103,58,183,0.15); border: 1px solid rgba(103,58,183,0.4); padding: 5px 8px; border-radius: 5px; gap: 8px;';
    row.innerHTML = `
        ${optionsSelectHtml}
        <input type="number" class="opt-param-val" value="10" placeholder="Chỉ số" style="width: 75px; background: #161f30; border: 1px solid #555; color: #fff; padding: 3px 6px; text-align: right; border-radius: 4px;">
        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #ff5252; cursor: pointer; padding: 2px 4px; font-weight: bold; font-size: 0.9rem;" title="Xóa option này">✕</button>
    `;
    list.appendChild(row);
}

function saveItemUpdate(tab, index) {
    const accId = document.getElementById('inspectAccountId').value;
    const qtyInput = document.getElementById('qty_' + tab + '_' + index);
    const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;

    const optRows = document.querySelectorAll(`#opt_list_${tab}_${index} .item-option-row`);
    const options = [];
    optRows.forEach(row => {
        const idEl = row.querySelector('.opt-id-val');
        const paramEl = row.querySelector('.opt-param-val');
        if (idEl && paramEl) {
            const id = parseInt(idEl.value);
            const param = parseInt(paramEl.value) || 0;
            if (!isNaN(id)) {
                options.push({ id: id, param: param });
            }
        }
    });

    fetch('/admin/players/' + accId + '/item-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: tab,
            index: index,
            quantity: qty,
            options: options
        })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('LƯU MÓN ĐỒ', d.message, d.success ? 'success' : 'error');
        if (d.success) {
            fetchAndRenderItems(accId);
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}

async function deleteItemSlot(tab, index, itemName) {
    const accId = document.getElementById('inspectAccountId').value;
    const tabName = (tab === 'body') ? 'Trang Bị Đang Mặc' : 'Hành Trang';
    const confirmed = await window.showConfirm({
        title: 'XÓA MÓN ĐỒ NÀY',
        message: `Bạn có đồng ý <strong>XÓA VĨNH VIỄN</strong> món đồ <strong>#${index} - ${itemName}</strong> khỏi <strong>${tabName}</strong> không?<br><span style="color:#ff5252;font-size:0.85rem;">Hành động này sẽ làm trống ô đồ và không thể hoàn tác!</span>`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch('/admin/players/' + accId + '/item-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: tab, index: index })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('KẾT QUẢ XÓA', d.message, d.success ? 'success' : 'error');
        if (d.success) {
            fetchAndRenderItems(accId);
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}
</script>
@endsection
