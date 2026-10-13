@extends('admin.layout')

@section('title', 'Trung Tâm Điều Khiển Quản Trị - NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 25px; border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: var(--primary); font-size: 1.4rem; display: flex; align-items: center; gap: 8px;">
            ⚡ TRUNG TÂM ĐIỀU KHIỂN QUẢN TRỊ VIÊN (CYBER ADMIN)
        </h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">Giám sát hệ thống & Điều khiển Game Server Realtime (Cổng RAM 14446).</p>
    </div>
    <div id="serverStatusBadge">
        @if($stats['server_online'])
            <span class="badge badge-success" style="font-size: 0.95rem; padding: 8px 14px; font-weight: 700;">🟢 GAME SERVER: ONLINE</span>
        @else
            <span class="badge badge-danger" style="font-size: 0.95rem; padding: 8px 14px; font-weight: 700;">🔴 GAME SERVER: OFFLINE</span>
        @endif
    </div>
</div>

<!-- Stats Grid Realtime -->
<div class="summary-grid" style="grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
    <div class="card summary-card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Người Chơi Online (RAM)</div>
            <div class="summary-val" id="onlineCountVal" style="color: #00e676; margin-top: 8px;">{{ number_format($stats['online_count']) }}</div>
        </div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 10px;">Đang kết nối trực tiếp</div>
    </div>
    
    <div class="card summary-card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">RAM Máy Chủ Sử Dụng</div>
            <div class="summary-val" id="memoryUsageVal" style="color: #00bcd4; margin-top: 8px; display: flex; align-items: baseline; gap: 6px; flex-wrap: nowrap; white-space: nowrap; overflow: hidden;">
                <span id="memUsedNum" style="font-size: 1.6rem; color: #00bcd4; font-family: 'Orbitron', sans-serif; font-weight: 800;">{{ number_format($stats['used_memory_mb'] ?? 0) }}</span>
                <span id="memMaxText" style="font-size: 0.82rem; color: #888; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap;">/ {{ number_format($stats['max_memory_mb'] ?? 4064) }} MB</span>
            </div>
        </div>
        <div style="margin-top: 10px;">
            @php
                $usedMb = $stats['used_memory_mb'] ?? 0;
                $maxMb = $stats['max_memory_mb'] ?? 4064;
                $pct = $maxMb > 0 ? min(round(($usedMb / $maxMb) * 100, 1), 100) : 0;
                $barColor = $pct >= 90 ? '#ff5252' : ($pct >= 75 ? '#ff9800' : '#00bcd4');
            @endphp
            <div style="background: rgba(255,255,255,0.08); height: 4px; border-radius: 2px; overflow: hidden;">
                <div id="ramProgressBar" style="width: {{ $pct }}%; height: 100%; background: {{ $barColor }}; border-radius: 2px; transition: width 0.3s ease, background 0.3s ease;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.76rem; color: #888; margin-top: 4px;">
                <span>JVM Heap</span>
                <span id="ramPercentText" style="color: {{ $barColor }}; font-weight: 700; font-family: 'Orbitron', sans-serif;">{{ $pct }}%</span>
            </div>
        </div>
    </div>
    
    <div class="card summary-card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Tổng Tài Khoản</div>
            <div class="summary-val" id="totalUsersVal" style="color: var(--primary); margin-top: 8px;">{{ number_format($stats['total_users']) }}</div>
        </div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 10px;">Đăng ký trong DB</div>
    </div>
    
    <div class="card summary-card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Gift Code Khả Dụng</div>
            <div class="summary-val" id="giftCodesVal" style="color: #ff9800; margin-top: 8px;">{{ number_format($stats['total_giftcodes']) }}</div>
        </div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 10px;">Thỏi Vàng & Hồng Ngọc</div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px;">
    
    <!-- EXP Config -->
    <div class="card">
        <h3 style="color: #00e676; margin-bottom: 12px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            ⚡ HỆ SỐ EXP SERVER (NÓNG)
        </h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Đổi hệ số kinh nghiệm nhận được từ quái. Áp dụng ngay lập tức cho toàn bộ người chơi.</p>
        
        <form id="expForm">
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #fff; font-size: 0.85rem;">Hệ số:</span>
                    <strong style="color: #00e676; font-size: 1.1rem; font-family: 'Orbitron', sans-serif;" id="expDisplay">x{{ $stats['rate_exp'] ?? 2 }}</strong>
                </div>
                <input type="range" id="expRange" min="1" max="50" value="{{ $stats['rate_exp'] ?? 2 }}" style="width: 100%; accent-color: #00e676;" oninput="document.getElementById('expDisplay').innerText = 'x' + this.value">
            </div>
            <button type="submit" class="btn" style="width: 100%; font-size: 0.9rem; font-weight: 700; padding: 10px; background: #00e676; color: #000;">
                💾 LƯU TỈ LỆ EXP MỚI
            </button>
        </form>
    </div>

    <!-- Send Global Notice -->
    <div class="card">
        <h3 style="color: #ffb74d; margin-bottom: 12px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            📢 THÔNG BÁO TOÀN SERVER
        </h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Chạy dòng chữ màu vàng trực tiếp trên màn hình toàn bộ người chơi trong game.</p>
        
        <form id="notifyForm">
            <div style="margin-bottom: 12px;">
                <textarea id="notifyMessage" class="form-input" rows="2" placeholder="Nội dung thông báo..." style="width: 100%; resize: vertical;" required></textarea>
            </div>
            <button type="submit" class="btn" style="width: 100%; font-size: 0.9rem; font-weight: 700; padding: 10px;">
                🚀 BẮN THÔNG BÁO
            </button>
        </form>
    </div>

    <!-- Maintenance Trigger -->
    <div class="card">
        <h3 style="color: #ff5252; margin-bottom: 12px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            ⚠️ BẢO TRÌ ĐẾM NGƯỢC
        </h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Đếm ngược thông báo, lưu dữ liệu người chơi an toàn và đóng cổng kết nối.</p>
        
        <form id="maintenanceForm">
            <div style="margin-bottom: 12px; display: flex; gap: 10px; align-items: center;">
                <select id="maintenanceSeconds" class="form-input" style="width: 100%;">
                    <option value="15">15 Giây (Khẩn cấp)</option>
                    <option value="30" selected>30 Giây (Tiêu chuẩn)</option>
                    <option value="60">60 Giây (1 Phút)</option>
                    <option value="120">120 Giây (2 Phút)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-danger" style="width: 100%; font-size: 0.9rem; font-weight: 700; padding: 10px; background: #d32f2f;">
                🛑 ĐẾM NGƯỢC BẢO TRÌ
            </button>
        </form>
    </div>

</div>

<!-- Realtime Online Players Table -->
<div class="card" style="padding: 0; margin-bottom: 25px;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h3 style="color: #fff; font-size: 1.1rem; margin: 0; display: flex; align-items: center; gap: 8px;">
            🎮 DANH SÁCH NGƯỜI CHƠI ĐANG ONLINE TRONG RAM
        </h3>
        <span id="playersHeaderCount" style="color: #00e676; font-size: 0.9rem; font-weight: 700;">{{ $stats['online_count'] }} Người chơi</span>
    </div>

    <div id="noPlayersMsg" style="display: {{ empty($serverStats['players']) ? 'block' : 'none' }}; padding: 25px; text-align: center; color: var(--text-muted);">
        Hiện không có người chơi nào đang kết nối hoặc Game Server đang tắt.
    </div>

    <div id="playersTableContainer" style="display: {{ empty($serverStats['players']) ? 'none' : 'block' }}; width: 100%; overflow-x: auto;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Nhân Vật</th>
                    <th>Tài Khoản</th>
                    <th>Sức Mạnh</th>
                    <th>Máu Tối Đa (HP)</th>
                    <th>Hồng Ngọc</th>
                    <th>Map / Khu</th>
                </tr>
            </thead>
            <tbody id="playersTableBody">
                @if(!empty($serverStats['players']))
                    @foreach($serverStats['players'] as $p)
                        <tr>
                            <td style="font-family: 'Orbitron', sans-serif;">{{ $p['id'] }}</td>
                            <td style="font-weight: 700; color: #00e676;">{{ $p['name'] }}</td>
                            <td style="color: #fff;">{{ $p['username'] }}</td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #ffb74d;">{{ number_format($p['power']) }}</td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #ff5252;">{{ number_format($p['hpMax']) }}</td>
                            <td style="color: #e91e63; font-weight: 700;">{{ number_format($p['ruby']) }} 💎</td>
                            <td>Map {{ $p['mapId'] }} - Khu {{ $p['zoneId'] }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
// -------------------------------------------------------------
// REALTIME POLLING: Cập nhật tổng quan và RAM người chơi không cần F5
// -------------------------------------------------------------
function pollDashboardData() {
    fetch('{{ route("admin.dashboard.data") }}')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const s = res.stats;

            // 1. Cập nhật Server Status Badge
            const badgeEl = document.getElementById('serverStatusBadge');
            if (badgeEl) {
                if (s.server_online) {
                    badgeEl.innerHTML = '<span class="badge badge-success" style="font-size: 0.95rem; padding: 8px 14px; font-weight: 700;">🟢 GAME SERVER: ONLINE</span>';
                } else {
                    badgeEl.innerHTML = '<span class="badge badge-danger" style="font-size: 0.95rem; padding: 8px 14px; font-weight: 700;">🔴 GAME SERVER: OFFLINE</span>';
                }
            }

            // 2. Cập nhật 4 thẻ Metrics
            const onlineCountEl = document.getElementById('onlineCountVal');
            if (onlineCountEl) onlineCountEl.innerText = Number(s.online_count || 0).toLocaleString();

            const memUsed = Number(s.used_memory_mb || 0);
            const memMax = Number(s.max_memory_mb || 4064);
            const pct = memMax > 0 ? Math.min(Math.round((memUsed / memMax) * 1000) / 10, 100) : 0;
            const barColor = pct >= 90 ? '#ff5252' : (pct >= 75 ? '#ff9800' : '#00bcd4');

            const memUsedEl = document.getElementById('memUsedNum');
            if (memUsedEl) memUsedEl.innerText = memUsed.toLocaleString();

            const memMaxEl = document.getElementById('memMaxText');
            if (memMaxEl) memMaxEl.innerText = `/ ${memMax.toLocaleString()} MB`;

            const ramBar = document.getElementById('ramProgressBar');
            if (ramBar) {
                ramBar.style.width = `${pct}%`;
                ramBar.style.background = barColor;
            }

            const ramPctEl = document.getElementById('ramPercentText');
            if (ramPctEl) {
                ramPctEl.innerText = `${pct}%`;
                ramPctEl.style.color = barColor;
            }

            const usersEl = document.getElementById('totalUsersVal');
            if (usersEl) usersEl.innerText = Number(s.total_users || 0).toLocaleString();

            const codesEl = document.getElementById('giftCodesVal');
            if (codesEl) codesEl.innerText = Number(s.total_giftcodes || 0).toLocaleString();

            // 3. Cập nhật Danh sách người chơi online
            const headerCount = document.getElementById('playersHeaderCount');
            if (headerCount) headerCount.innerText = `${s.online_count || 0} Người chơi`;

            const players = res.players || [];
            const noMsg = document.getElementById('noPlayersMsg');
            const tblCont = document.getElementById('playersTableContainer');
            const tbody = document.getElementById('playersTableBody');

            if (players.length === 0) {
                if (noMsg) noMsg.style.display = 'block';
                if (tblCont) tblCont.style.display = 'none';
                if (tbody) tbody.innerHTML = '';
            } else {
                if (noMsg) noMsg.style.display = 'none';
                if (tblCont) tblCont.style.display = 'block';
                if (tbody) {
                    let html = '';
                    players.forEach(p => {
                        html += `
                            <tr>
                                <td style="font-family: 'Orbitron', sans-serif;">${p.id}</td>
                                <td style="font-weight: 700; color: #00e676;">${p.name}</td>
                                <td style="color: #fff;">${p.username}</td>
                                <td style="font-family: 'Orbitron', sans-serif; color: #ffb74d;">${Number(p.power || 0).toLocaleString()}</td>
                                <td style="font-family: 'Orbitron', sans-serif; color: #ff5252;">${Number(p.hpMax || 0).toLocaleString()}</td>
                                <td style="color: #e91e63; font-weight: 700;">${Number(p.ruby || 0).toLocaleString()} 💎</td>
                                <td>Map ${p.mapId} - Khu ${p.zoneId}</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                }
            }
        })
        .catch(() => {});
}

// Bắt đầu cập nhật realtime mỗi 2.5 giây
setInterval(pollDashboardData, 2500);

// -------------------------------------------------------------
// XỬ LÝ ACTIONS VỚI CYBERPUNK POPUP CONFIRMATION
// -------------------------------------------------------------
document.getElementById('notifyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = document.getElementById('notifyMessage').value.trim();
    if (!msg) return;

    const confirmed = await window.showConfirm({
        title: 'BẮN THÔNG BÁO TOÀN SERVER',
        message: `Bạn có chắc muốn gửi thông báo:<br><b style="color: #ffd700;">"${msg}"</b><br>trực tiếp tới toàn bộ người chơi đang trong game?`,
        okText: 'Gửi thông báo',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;

    fetch('{{ route("admin.server.notify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('KẾT QUẢ', d.message || 'Đã gửi thông báo thành công!', d.success ? 'success' : 'error');
        if (d.success) document.getElementById('notifyMessage').value = '';
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ Game!', 'error'));
});

document.getElementById('maintenanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const sec = document.getElementById('maintenanceSeconds').value;

    const confirmed = await window.showConfirm({
        title: 'KÍCH HOẠT BẢO TRÌ ĐẾM NGƯỢC',
        message: `Cảnh báo: Hành động này sẽ bắt đầu đếm ngược <strong style="color: #ff5252;">${sec} giây</strong>, tự động lưu dữ liệu toàn bộ tài khoản và đóng server. Tiếp tục?`,
        okText: 'Xác nhận bảo trì',
        cancelText: 'Hủy bỏ',
        isDanger: true
    });
    if (!confirmed) return;

    fetch('{{ route("admin.server.maintenance") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ seconds: parseInt(sec) })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('BẢO TRÌ', d.message || 'Đã bắt đầu đếm ngược bảo trì!', d.success ? 'success' : 'error');
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ Game!', 'error'));
});

document.getElementById('expForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const rate = document.getElementById('expRange').value;

    const confirmed = await window.showConfirm({
        title: 'ĐỔI TỈ LỆ EXP SERVER',
        message: `Bạn có muốn áp dụng ngay hệ số kinh nghiệm <strong style="color: #00e676;">x${rate}</strong> cho toàn bộ người chơi không?`,
        okText: 'Đồng ý áp dụng',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;

    fetch('{{ route("admin.server.config") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ rate_exp: parseInt(rate) })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('HỆ SỐ EXP', d.message || 'Đã cập nhật tỉ lệ EXP thành công!', d.success ? 'success' : 'error');
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ Game!', 'error'));
});
</script>
@endsection
