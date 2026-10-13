@extends('admin.layout')

@section('title', 'Trung Tâm Điều Khiển Quản Trị - NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 25px; border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="color: var(--primary); font-size: 1.4rem;">TRUNG TÂM ĐIỀU KHIỂN QUẢN TRỊ VIÊN (CYBER ADMIN)</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">Giám sát hệ thống & Điều khiển Game Server Realtime (Cổng RAM 14446).</p>
    </div>
    <div>
        @if($stats['server_online'])
            <span class="badge badge-success" style="font-size: 0.95rem; padding: 8px 14px; font-weight: 700;">🟢 GAME SERVER: ONLINE</span>
        @else
            <span class="badge badge-danger" style="font-size: 0.95rem; padding: 8px 14px; font-weight: 700;">🔴 GAME SERVER: OFFLINE</span>
        @endif
    </div>
</div>

<!-- Stats Grid Realtime -->
<div class="summary-grid" style="grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Người Chơi Online (RAM)</div>
        <div class="summary-val" style="color: #00e676;">{{ number_format($stats['online_count']) }}</div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">Đang kết nối trực tiếp</div>
    </div>
    
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">RAM Máy Chủ Sử Dụng</div>
        <div class="summary-val" style="color: #00bcd4;">{{ $stats['used_memory_mb'] }} <span style="font-size: 1rem; color: #888;">/ {{ $stats['max_memory_mb'] }} MB</span></div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">JVM Heap Memory</div>
    </div>
    
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Tổng Tài Khoản</div>
        <div class="summary-val" style="color: var(--primary);">{{ number_format($stats['total_users']) }}</div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">Đăng ký trong DB</div>
    </div>
    
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Gift Code Khả Dụng</div>
        <div class="summary-val" style="color: #ff9800;">{{ number_format($stats['total_giftcodes']) }}</div>
        <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">Thỏi Vàng & Hồng Ngọc</div>
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
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">DANH SÁCH NGƯỜI CHƠI ĐANG ONLINE TRONG RAM</h3>
        <span style="color: #00e676; font-size: 0.9rem; font-weight: 700;">{{ $stats['online_count'] }} Người chơi</span>
    </div>

    @if(empty($serverStats['players']))
        <p style="padding: 25px; text-align: center; color: var(--text-muted);">Hiện không có người chơi nào đang kết nối hoặc Game Server đang tắt.</p>
    @else
        <table style="margin-top: 0;">
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
            <tbody>
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
            </tbody>
        </table>
    @endif
</div>

<script>
document.getElementById('notifyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('notifyMessage').value;
    if (!msg) return;

    fetch('{{ route("admin.server.notify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if (d.success) document.getElementById('notifyMessage').value = '';
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
});

document.getElementById('maintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const sec = document.getElementById('maintenanceSeconds').value;
    if (!confirm('Bạn có chắc chắn muốn kích hoạt bảo trì đếm ngược ' + sec + ' giây không?')) return;

    fetch('{{ route("admin.server.maintenance") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ seconds: parseInt(sec) })
    })
    .then(r => r.json())
    .then(d => alert(d.message))
    .catch(() => alert('Lỗi kết nối máy chủ'));
});

document.getElementById('expForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const rate = document.getElementById('expRange').value;

    fetch('{{ route("admin.server.config") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ rate_exp: parseInt(rate) })
    })
    .then(r => r.json())
    .then(d => alert(d.message))
    .catch(() => alert('Lỗi kết nối máy chủ'));
});
</script>
@endsection
