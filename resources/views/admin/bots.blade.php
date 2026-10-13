@extends('admin.layout')

@section('title', 'Quản Lý Bot AI Realtime - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #00e5ff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #00e5ff; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🤖 QUẢN LÝ HỆ THỐNG BOT AI TỰ ĐỘNG REALTIME
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Hệ thống sinh Bot AI thông minh tự động đăng nhập, farm quái, biến hình, hợp thể Porata và chat giả lập người chơi thật. Thao tác điều khiển trực tiếp trên RAM máy chủ!
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI
        </button>
    </div>
</div>

<!-- Auto Chat Status Card -->
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(0, 229, 255, 0.2); background: rgba(13, 20, 36, 0.6);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 2rem;">💬</div>
            <div>
                <h3 style="color: #fff; font-size: 1.05rem; margin: 0 0 4px 0;">Tính Năng Bot Tự Động Chat Thế Giới (Fake Chat)</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                    Bot tự động gửi tin nhắn chat vui nhộn, hỏi đáp game lên kênh chat Thế giới mỗi vài phút để tạo cảm giác đông đúc náo nhiệt.
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 15px;">
            <div>
                @if($chatEnabled)
                    <span class="badge" style="background: rgba(0, 230, 118, 0.15); color: #00e676; border: 1px solid #00e676; font-size: 0.85rem; padding: 6px 14px; font-weight: 700;">
                        ● ĐANG BẬT TỰ ĐỘNG CHAT
                    </span>
                @else
                    <span class="badge" style="background: rgba(255, 23, 68, 0.15); color: #ff1744; border: 1px solid #ff1744; font-size: 0.85rem; padding: 6px 14px; font-weight: 700;">
                        ○ ĐÃ TẮT TỰ ĐỘNG CHAT
                    </span>
                @endif
            </div>

            <div>
                @if($chatEnabled)
                    <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff5252; padding: 6px 14px; font-size: 0.85rem;" onclick="toggleChat(false)">
                        Tắt Auto Chat
                    </button>
                @else
                    <button class="btn" style="background: #00e676; padding: 6px 14px; font-size: 0.85rem; font-weight: 700;" onclick="toggleChat(true)">
                        Bật Auto Chat
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Control Panel -->
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(0, 229, 255, 0.3);">
    <h3 style="color: #00e5ff; font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
        ⚡ BỘ ĐIỀU KHIỂN & THẢ BOT NHANH VÀO MÁY CHỦ
    </h3>
    
    <div style="display: flex; flex-direction: column; gap: 15px;">
        <!-- Row 1: Thả Bot -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="color: #fff; font-size: 0.9rem; font-weight: 600; min-width: 170px;">Thả Nhanh Bot Cày Cuốc:</span>
            <button class="btn" style="background: #00bcd4; padding: 7px 15px; font-weight: 600;" onclick="spawnBots(5)">
                +5 Bot
            </button>
            <button class="btn" style="background: #009688; padding: 7px 15px; font-weight: 600;" onclick="spawnBots(10)">
                +10 Bot
            </button>
            <button class="btn" style="background: #3f51b5; padding: 7px 15px; font-weight: 600;" onclick="spawnBots(20)">
                +20 Bot
            </button>
            
            <div style="display: flex; gap: 5px; margin-left: 10px;">
                <input type="number" id="customBotCount" class="form-input" placeholder="Số lượng..." min="1" max="50" style="width: 110px; margin-bottom: 0; padding: 7px 12px;">
                <button class="btn" style="background: #673ab7; padding: 7px 15px; font-weight: 600;" onclick="spawnCustomBots()">
                    🚀 Thả Số Lượng
                </button>
            </div>
        </div>

        <!-- Row 2: Dừng Nhanh Bot -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 12px;">
            <span style="color: #ff8a80; font-size: 0.9rem; font-weight: 600; min-width: 170px;">Dừng Nhanh Số Lượng Bot:</span>
            <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 7px 14px; font-weight: 600;" onclick="stopCountBots(1)">
                -1 Bot
            </button>
            <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 7px 14px; font-weight: 600;" onclick="stopCountBots(3)">
                -3 Bot
            </button>
            <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 7px 14px; font-weight: 600;" onclick="stopCountBots(5)">
                -5 Bot
            </button>
            
            <div style="margin-left: auto;">
                <button class="btn btn-danger" style="background: #d32f2f; padding: 7px 18px; font-weight: 700; box-shadow: 0 0 10px rgba(211,47,47,0.4);" onclick="stopAllBots()">
                    🛑 DỌN DẸP / DỪNG TẤT CẢ BOT
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Active Bots List Table -->
<div class="card" style="padding: 0;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">DANH SÁCH BOT ĐANG HOẠT ĐỘNG TRONG GAME</h3>
            <button id="btnStopSelected" class="btn btn-danger" style="display: none; padding: 4px 12px; font-size: 0.8rem; background: #e53935; font-weight: 700; box-shadow: 0 0 10px rgba(229,57,53,0.5);" onclick="stopSelectedBots()">
                🛑 DỪNG <span id="selectedCount">0</span> BOT ĐÃ CHỌN
            </button>
        </div>
        <span style="color: #00e5ff; font-size: 0.9rem; font-weight: 700;">{{ count($allBots ?? $bots) }} Bot đang online</span>
    </div>

    @if(empty($bots) || $bots->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 10px;">🤖</div>
            <p>Hiện tại không có Bot AI nào đang chạy hoặc Game Server chưa khởi động.</p>
            <button class="btn" style="background: #00bcd4; margin-top: 10px;" onclick="spawnBots(5)">Thả ngay 5 Bot đầu tiên</button>
        </div>
    @else
        <div style="width: 100%; overflow-x: auto;">
            <table style="margin-top: 0; width: 100%; table-layout: fixed; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center; padding: 10px 6px;">
                            <input type="checkbox" id="selectAllBots" onclick="toggleSelectAllBots(this)" style="cursor: pointer; width: 15px; height: 15px;" title="Chọn tất cả bot trên trang">
                        </th>
                        <th style="width: 50px; padding: 10px 6px; font-size: 0.8rem;">ID</th>
                        <th style="width: 18%; padding: 10px 8px; font-size: 0.8rem;">Tên Bot</th>
                        <th style="width: 20%; padding: 10px 8px; font-size: 0.8rem;">Bản Đồ</th>
                        <th style="width: 10%; padding: 10px 8px; font-size: 0.8rem;">Khu Vực</th>
                        <th style="width: 14%; padding: 10px 8px; font-size: 0.8rem;">Sức Mạnh</th>
                        <th style="width: 14%; padding: 10px 8px; font-size: 0.8rem;">Máu (HP)</th>
                        <th style="width: 16%; text-align: right; padding: 10px 8px; font-size: 0.8rem;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bots as $bot)
                        @php
                            $safeBotName = addslashes($bot['name'] ?? 'Bot AI');
                        @endphp
                        <tr>
                            <td style="text-align: center; padding: 10px 6px;">
                                <input type="checkbox" class="bot-checkbox" value="{{ $bot['id'] }}" onchange="onBotCheckChange()" style="cursor: pointer; width: 15px; height: 15px;">
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #888; font-size: 0.8rem; padding: 10px 6px;">{{ $bot['id'] ?? 'BOT' }}</td>
                            <td style="font-weight: 700; color: #00e5ff; font-size: 0.85rem; padding: 10px 8px; word-break: break-word;">
                                🤖 {{ $bot['name'] ?? 'Bot AI' }}
                            </td>
                            <td style="color: #fff; font-size: 0.82rem; padding: 10px 8px; word-break: break-word;">
                                <strong style="color: #00e5ff;">{{ $bot['mapName'] ?? 'Chưa vào Map' }}</strong> 
                                @if(isset($bot['mapId']) && $bot['mapId'] >= 0)
                                    <span style="color: #888; font-size: 0.75rem;">(Map {{ $bot['mapId'] }})</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; padding: 10px 8px;">
                                @if(isset($bot['zoneId']) && $bot['zoneId'] >= 0)
                                    <span style="color: #ff9800; font-weight: 700; font-size: 0.82rem;">Khu {{ $bot['zoneId'] }}</span>
                                @else
                                    <span style="color: #666;">-</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #e91e63; font-weight: 600; font-size: 0.8rem; padding: 10px 8px;">
                                {{ number_format($bot['power'] ?? 0) }}
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #4caf50; font-size: 0.8rem; padding: 10px 8px;">
                                {{ number_format($bot['hp'] ?? 0) }}
                            </td>
                            <td style="text-align: right; white-space: nowrap; padding: 10px 8px;">
                                <button class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; background: #d32f2f; white-space: nowrap; font-weight: 700;" onclick="stopSingleBot({{ $bot['id'] }}, '{{ $safeBotName }}')">
                                    🛑 Dừng Bot
                                </button>
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
// Thả Bot
async function spawnBots(count) {
    const confirmed = await window.showConfirm({
        title: 'THẢ BOT AI',
        message: `Bạn có đồng ý thả thêm <strong>${count} Bot AI</strong> vào máy chủ không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;
    
    fetch("{{ route('admin.bots.spawn') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ count: count })
    })
    .then(r => r.json())
    .then(async data => {
        if (data.success || data.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', data.message || `Đã thả thành công ${count} Bot AI!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', data.message || 'Không thể kết nối Game Server!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

function spawnCustomBots() {
    const val = parseInt(document.getElementById('customBotCount').value);
    if (!val || val <= 0 || val > 100) {
        window.showModalAlert('CẢNH BÁO', 'Vui lòng nhập số lượng từ 1 đến 100!', 'warning');
        return;
    }
    spawnBots(val);
}

// Dừng 1 con bot cụ thể theo ID
async function stopSingleBot(botId, botName) {
    const confirmed = await window.showConfirm({
        title: 'DỪNG BOT AI',
        message: `Bạn có đồng ý dừng <strong>Bot "${botName}"</strong> (ID: ${botId}) không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ bot_id: botId })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã dừng Bot ${botName} thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể dừng bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng N con bot nhanh
async function stopCountBots(count) {
    const confirmed = await window.showConfirm({
        title: 'DỪNG BOT AI',
        message: `Bạn có đồng ý dừng <strong>${count} Bot AI</strong> đang hoạt động không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ count: count })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã dừng ${count} bot thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể dừng bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng nhiều bot được tích chọn bằng checkbox
function toggleSelectAllBots(master) {
    const boxes = document.querySelectorAll('.bot-checkbox');
    boxes.forEach(b => b.checked = master.checked);
    onBotCheckChange();
}

function onBotCheckChange() {
    const checked = document.querySelectorAll('.bot-checkbox:checked');
    const btn = document.getElementById('btnStopSelected');
    const cnt = document.getElementById('selectedCount');
    if (checked.length > 0) {
        cnt.innerText = checked.length;
        btn.style.display = 'inline-flex';
    } else {
        btn.style.display = 'none';
    }
}

async function stopSelectedBots() {
    const checked = document.querySelectorAll('.bot-checkbox:checked');
    if (checked.length === 0) return;

    const ids = Array.from(checked).map(c => parseInt(c.value));
    const confirmed = await window.showConfirm({
        title: 'DỪNG NHIỀU BOT ĐÃ CHỌN',
        message: `Bạn có đồng ý dừng <strong>${ids.length} Bot AI</strong> đã chọn khỏi game không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ bot_ids: ids })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã dừng ${ids.length} Bot đã chọn thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể dừng bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng tất cả bot
async function stopAllBots() {
    const confirmed = await window.showConfirm({
        title: 'CẢNH BÁO: DỪNG TOÀN BỘ BOT',
        message: 'Bạn có chắc chắn muốn <strong>DỪNG & XÓA TOÀN BỘ BOT AI</strong> đang chạy trên máy chủ không?',
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(r => r.json())
    .then(async data => {
        if (data.success || data.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', data.message || 'Đã dọn dẹp toàn bộ Bot thành công!', 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', data.message || 'Không thể dừng Bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

async function toggleChat(enable) {
    const actionText = enable ? 'BẬT' : 'TẮT';
    const confirmed = await window.showConfirm({
        title: `${actionText} BOT AUTO CHAT`,
        message: `Bạn có đồng ý <strong>${actionText}</strong> tính năng Bot Tự Động Chat thế giới không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.toggle-chat') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ enabled: enable })
    })
    .then(r => r.json())
    .then(async data => {
        if (data.success || data.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', data.message || `Đã ${actionText} Bot Auto Chat thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', data.message || 'Không thể cập nhật!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
</script>
@endsection
