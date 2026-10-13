@extends('admin.layout')

@section('title', 'Quản Lý Bot AI Realtime - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #00e5ff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #00e5ff; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🤖 QUẢN LÝ HỆ THỐNG BOT AI ẢO REALTIME
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Điều khiển quân đoàn Bot AI tự động train quái, di chuyển map và tự động chat thế giới để tạo không khí đông đúc.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI DANH SÁCH
        </button>
    </div>
</div>

<!-- Stats & Fast Control Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <!-- Stat: Active Bots -->
    <div class="card" style="border: 1px solid rgba(0, 229, 255, 0.2); background: linear-gradient(135deg, rgba(0,229,255,0.05) 0%, rgba(20,24,35,0.9) 100%);">
        <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Bot Đang Hoạt Động</div>
        <div style="font-size: 2.2rem; font-family: 'Orbitron', sans-serif; font-weight: 700; color: #00e5ff; margin: 10px 0;">
            {{ $botCount }} <span style="font-size: 1rem; color: #888;">Bot Online</span>
        </div>
        <div style="font-size: 0.85rem; color: #4caf50;">● Tự động farm quái & di chuyển</div>
    </div>

    <!-- Stat: Auto Chat Status -->
    <div class="card" style="border: 1px solid rgba(255, 152, 0, 0.2); background: linear-gradient(135deg, rgba(255,152,0,0.05) 0%, rgba(20,24,35,0.9) 100%);">
        <div style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Bot Auto Chat Thế Giới</div>
        <div style="font-size: 1.6rem; font-family: 'Orbitron', sans-serif; font-weight: 700; margin: 10px 0;">
            @if($chatEnabled)
                <span style="color: #4caf50;">ĐANG BẬT</span>
            @else
                <span style="color: #f44336;">ĐÃ TẮT</span>
            @endif
        </div>
        <div>
            <button class="btn {{ $chatEnabled ? 'btn-danger' : 'btn-success' }}" 
                    style="padding: 6px 14px; font-size: 0.8rem; font-weight: 600;" 
                    onclick="toggleChat({{ $chatEnabled ? 'false' : 'true' }})">
                {{ $chatEnabled ? '⛔ TẮT AUTO CHAT' : '✅ BẬT AUTO CHAT' }}
            </button>
        </div>
    </div>
</div>

<!-- Control Panel -->
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(0, 229, 255, 0.3);">
    <h3 style="color: #00e5ff; font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
        ⚡ BỘ ĐIỀU KHIỂN & THẢ BOT NHANH VÀO MÁY CHỦ
    </h3>
    
    <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="color: #fff; font-size: 0.9rem; font-weight: 600;">Thả Nhanh Bot Cày Cuốc:</span>
            <button class="btn" style="background: #00bcd4; padding: 8px 16px; font-weight: 600;" onclick="spawnBots(5)">
                +5 Bot
            </button>
            <button class="btn" style="background: #009688; padding: 8px 16px; font-weight: 600;" onclick="spawnBots(10)">
                +10 Bot
            </button>
            <button class="btn" style="background: #3f51b5; padding: 8px 16px; font-weight: 600;" onclick="spawnBots(20)">
                +20 Bot
            </button>
            
            <div style="display: flex; gap: 5px; margin-left: 10px;">
                <input type="number" id="customBotCount" class="form-input" placeholder="Số lượng..." min="1" max="50" style="width: 110px; margin-bottom: 0; padding: 8px 12px;">
                <button class="btn" style="background: #673ab7; padding: 8px 15px; font-weight: 600;" onclick="spawnCustomBots()">
                    🚀 Thả Số Lượng
                </button>
            </div>
        </div>

        <div>
            <button class="btn btn-danger" style="background: #d32f2f; padding: 8px 18px; font-weight: 700; box-shadow: 0 0 10px rgba(211,47,47,0.4);" onclick="stopAllBots()">
                🛑 DỌN DẸP / DỪNG TẤT CẢ BOT
            </button>
        </div>
    </div>
</div>

<!-- Active Bots List Table -->
<div class="card" style="padding: 0;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">DANH SÁCH BOT ĐANG HOẠT ĐỘNG TRONG GAME</h3>
        <span style="color: #00e5ff; font-size: 0.9rem; font-weight: 700;">{{ count($allBots ?? $bots) }} Bot đang online</span>
    </div>

    @if(empty($bots) || $bots->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 10px;">🤖</div>
            <p>Hiện tại không có Bot AI nào đang chạy hoặc Game Server chưa khởi động.</p>
            <button class="btn" style="background: #00bcd4; margin-top: 10px;" onclick="spawnBots(5)">Thả ngay 5 Bot đầu tiên</button>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table style="margin-top: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Tên Bot</th>
                        <th>Bản Đồ</th>
                        <th>Khu Vực</th>
                        <th>Sức Mạnh</th>
                        <th>Máu (HP)</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bots as $bot)
                        <tr>
                            <td style="font-family: 'Orbitron', sans-serif; color: #888;">{{ $bot['id'] ?? 'BOT' }}</td>
                            <td style="font-weight: 700; color: #00e5ff;">
                                🤖 {{ $bot['name'] ?? 'Bot AI' }}
                            </td>
                            <td style="color: #fff;">
                                <strong style="color: #00e5ff;">{{ $bot['mapName'] ?? 'Chưa vào Map' }}</strong> 
                                @if(isset($bot['mapId']) && $bot['mapId'] >= 0)
                                    <span style="color: #888; font-size: 0.82rem;">(Map {{ $bot['mapId'] }})</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif;">
                                @if(isset($bot['zoneId']) && $bot['zoneId'] >= 0)
                                    <span style="color: #ff9800; font-weight: 700;">Khu {{ $bot['zoneId'] }}</span>
                                @else
                                    <span style="color: #666;">-</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #e91e63; font-weight: 600;">
                                {{ number_format($bot['power'] ?? 0) }}
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #4caf50;">
                                {{ number_format($bot['hp'] ?? 0) }}
                            </td>
                            <td>
                                <span class="badge badge-success" style="font-size: 0.75rem; background: rgba(76,175,80,0.2); color: #4caf50; border: 1px solid #4caf50;">
                                    Auto Farm
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 15px 20px;">
            {{ $bots->links() }}
        </div>
    @endif
</div>

<script>
function spawnBots(count) {
    if (!confirm(`Bạn có chắc muốn thả thêm ${count} Bot AI vào server?`)) return;
    
    fetch("{{ route('admin.bots.spawn') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ count: count })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.status === 'success') {
            alert(data.message || `Đã thả thành công ${count} Bot AI!`);
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể kết nối Game Server!'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối tới Server!');
    });
}

function spawnCustomBots() {
    const val = parseInt(document.getElementById('customBotCount').value);
    if (!val || val <= 0 || val > 100) {
        alert('Vui lòng nhập số lượng từ 1 đến 100!');
        return;
    }
    spawnBots(val);
}

function stopAllBots() {
    if (!confirm("CẢNH BÁO: Bạn có chắc chắn muốn DỪNG & XÓA TOÀN BỘ BOT AI đang chạy trong game?")) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.status === 'success') {
            alert(data.message || 'Đã dọn dẹp toàn bộ Bot thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể dừng Bot!'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối tới Server!');
    });
}

function toggleChat(enable) {
    const actionText = enable ? 'BẬT' : 'TẮT';
    if (!confirm(`Bạn có muốn ${actionText} tính năng Bot Tự Động Chat thế giới không?`)) return;

    fetch("{{ route('admin.bots.toggle-chat') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ enabled: enable })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.status === 'success') {
            alert(data.message || `Đã ${actionText} Bot Auto Chat thành công!`);
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật!'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối tới Server!');
    });
}
</script>
@endsection
