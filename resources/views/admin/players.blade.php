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
                        <td style="text-align: right; display: flex; justify-content: flex-end; gap: 6px; flex-wrap: wrap;">
                            <!-- Buff Items / Gold / Ruby -->
                            <button class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #e91e63;" onclick="openBuffModal({{ $acc->id }}, '{{ $acc->username }}')">
                                🎁 BUFF ĐỒ / NGỌC
                            </button>

                            <!-- Set Points -->
                            @if($player)
                                <button class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #00bcd4;" onclick="openPointModal({{ $acc->id }}, '{{ $acc->username }}', '{{ $player->name }}')">
                                    ⚡ SET CHỈ SỐ
                                </button>
                                <button class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #673ab7;" onclick="openInspectModal({{ $acc->id }}, '{{ $acc->username }}', '{{ $player->name }}')">
                                    🔍 SOI ĐỒ
                                </button>
                            @endif

                            <!-- Kick -->
                            <button class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #ff9800;" onclick="kickPlayer({{ $acc->id }}, '{{ $acc->username }}')">
                                👟 KICK
                            </button>

                            <!-- Role Toggle -->
                            @if($acc->is_admin == 1)
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleAdmin({{ $acc->id }}, 'remove')">
                                    - Admin
                                </button>
                            @else
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleAdmin({{ $acc->id }}, 'set')">
                                    + Admin
                                </button>
                            @endif

                            <!-- Ban Toggle -->
                            @if($acc->ban > 0)
                                <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem; background: #4caf50;" onclick="toggleBan({{ $acc->id }}, 'unban')" id="ban-btn-{{ $acc->id }}">
                                    Mở
                                </button>
                            @else
                                <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleBan({{ $acc->id }}, 'ban')" id="ban-btn-{{ $acc->id }}">
                                    Khóa
                                </button>
                            @endif
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
    .then(d => {
        alert(d.message);
        if (d.success) closeBuffModal();
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
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
    .then(d => {
        alert(d.message);
        if (d.success) closePointModal();
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
});

// Kick Player
function kickPlayer(id, username) {
    if (!confirm('Bạn có chắc chắn muốn kick ' + username + ' văng khỏi game không?')) return;
    fetch('/admin/players/' + id + '/kick', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => alert(d.message))
    .catch(() => alert('Lỗi kết nối máy chủ'));
}

// Toggle Ban
function toggleBan(id, action) {
    fetch('/admin/players/' + id + '/' + action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        location.reload();
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
}

// Toggle Admin
function toggleAdmin(id, action) {
    fetch('/admin/players/' + id + '/' + (action === 'set' ? 'set-admin' : 'remove-admin'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        location.reload();
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
}
</script>

<!-- Modal Item Inspector (Soi & Sửa Trang Bị Trực Tiếp) -->
<div id="inspectModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center;">
    <div class="card" style="max-width: 800px; width: 95%; max-height: 90vh; overflow-y: auto; border: 1px solid #673ab7; box-shadow: 0 0 30px rgba(103,58,183,0.35);">
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
        <div id="inspectItemsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <p style="color: #888; text-align: center; grid-column: 1 / -1;">Đang tải dữ liệu từ RAM Game Server...</p>
        </div>
    </div>
</div>

<script>
let currentInspectData = null;
let currentInspectTab = 'body';

function openInspectModal(accId, username, charName) {
    document.getElementById('inspectAccountId').value = accId;
    document.getElementById('inspectModalTitle').innerText = 'SOI & SỬA TRANG BỊ: ' + charName + ' (' + username + ')';
    document.getElementById('inspectItemsList').innerHTML = '<p style="color: #888; text-align: center; grid-column: 1 / -1;">Đang tải dữ liệu từ RAM Game Server...</p>';
    document.getElementById('inspectModal').style.display = 'flex';

    fetch('/admin/players/' + accId + '/items')
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('inspectItemsList').innerHTML = '<p style="color: #ff5252; text-align: center; grid-column: 1 / -1;">' + (d.message || 'Không thể lấy dữ liệu') + '</p>';
                return;
            }
            currentInspectData = d;
            switchInspectTab('body');
        })
        .catch(() => {
            document.getElementById('inspectItemsList').innerHTML = '<p style="color: #ff5252; text-align: center; grid-column: 1 / -1;">Lỗi kết nối Game Server port 14446</p>';
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
        (it.options || []).forEach((opt, idx) => {
            optHtml += `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 0.8rem; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px;">
                    <span style="color: #b388ff;">${opt.name}</span>
                    <input type="number" id="opt_${tab}_${it.index}_${idx}" value="${opt.param}" style="width: 70px; background: #161f30; border: 1px solid #444; color: #fff; padding: 2px 4px; text-align: right; border-radius: 3px;" data-optid="${opt.id}">
                </div>
            `;
        });

        html += `
            <div style="background: rgba(22, 31, 48, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <img src="/images/icons/${it.iconId}.png" style="width: 26px; height: 26px; object-fit: contain;" onerror="this.onerror=null; this.src='/images/no_image.png';">
                        <strong style="color: #ffb74d; font-size: 0.9rem;">#${it.index} - ${it.name}</strong>
                    </div>
                    <span style="color: #888; font-size: 0.75rem;">ID: ${it.templateId}</span>
                </div>
                <div style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="color: #aaa; font-size: 0.8rem;">Số lượng:</span>
                    <input type="number" id="qty_${tab}_${it.index}" value="${it.quantity}" style="width: 70px; background: #161f30; border: 1px solid #444; color: #fff; padding: 2px 4px; text-align: right; border-radius: 3px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <div style="color: #888; font-size: 0.75rem; margin-bottom: 4px;">Các chỉ số (Options):</div>
                    ${optHtml || '<div style="color: #666; font-size: 0.75rem; font-style: italic;">Không có option nào</div>'}
                </div>
                <button type="button" class="btn" style="width: 100%; font-size: 0.8rem; padding: 6px; background: #673ab7;" onclick="saveItemUpdate('${tab}', ${it.index}, ${it.options ? it.options.length : 0})">
                    💾 LƯU MÓN ĐỒ NÀY
                </button>
            </div>
        `;
    });
    container.innerHTML = html;
}

function saveItemUpdate(tab, index, optCount) {
    const accId = document.getElementById('inspectAccountId').value;
    const qty = parseInt(document.getElementById('qty_' + tab + '_' + index).value) || 1;

    const options = [];
    for (let i = 0; i < optCount; i++) {
        const input = document.getElementById('opt_' + tab + '_' + index + '_' + i);
        if (input) {
            options.push({
                id: parseInt(input.getAttribute('data-optid')),
                param: parseInt(input.value) || 0
            });
        }
    }

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
    .then(d => {
        alert(d.message);
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
}
</script>
@endsection
