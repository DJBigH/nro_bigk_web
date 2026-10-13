@extends('admin.layout')

@section('title', 'Quản Lý Sự Kiện Game Realtime - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 22px; border-left: 4px solid #f85149; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #f85149; font-size: 1.35rem; display: flex; align-items: center; gap: 10px; margin: 0 0 6px 0; font-family: 'Orbitron', sans-serif;">
            🎉 QUẢN LÝ SỰ KIỆN MÁY CHỦ (EVENT MANAGER)
        </h2>
        <p style="color: #8b949e; font-size: 0.88rem; margin: 0;">
            Điều chỉnh trực tiếp 11 sự kiện toàn máy chủ. Hỗ trợ kích hoạt <strong>Realtime ngay trên RAM</strong> không cần dừng server, hoặc hẹn giờ bảo trì kick all để làm mới dữ liệu client!
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <span class="badge" style="background: {{ $isGameActive ? 'rgba(35, 134, 54, 0.2)' : 'rgba(218, 54, 51, 0.2)' }}; color: {{ $isGameActive ? '#3fb950' : '#f85149' }}; border: 1px solid {{ $isGameActive ? '#238636' : '#da3633' }}; font-family: monospace; font-size: 0.82rem; padding: 6px 12px; border-radius: 6px;">
            ● Port 14446: {{ $isGameActive ? 'REALTIME ONLINE' : 'OFFLINE' }}
        </span>
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 7px 15px;">
            🔄 LÀM MỚI
        </button>
    </div>
</div>

<!-- Status & Live Active Event -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; margin-bottom: 22px;">
    <!-- Active Event Box -->
    <div class="card" style="background: linear-gradient(135deg, rgba(22, 27, 34, 0.95), rgba(13, 17, 23, 0.95)); border: 1px solid {{ $activeEvent['color'] ?? '#30363d' }}; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -15px; top: -15px; font-size: 6rem; opacity: 0.08; pointer-events: none;">
            {{ $activeEvent['icon'] }}
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1px; color: #8b949e; font-weight: 700;">SỰ KIỆN ĐANG DIỄN RA</span>
            <span class="badge" style="background: {{ $activeEvent['color'] }}22; color: {{ $activeEvent['color'] }}; border: 1px solid {{ $activeEvent['color'] }}66; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px;">
                {{ $activeEvent['badge'] }}
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
            <div style="font-size: 2.4rem; width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.04); border-radius: 10px; border: 1px solid rgba(255,255,255,0.08);">
                {{ $activeEvent['icon'] }}
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.25rem; color: #fff; font-weight: 700;">
                    [{{ $activeEvent['id'] }}] {{ $activeEvent['name'] }}
                </h3>
                <div style="color: {{ $activeEvent['color'] }}; font-size: 0.82rem; margin-top: 3px; font-weight: 600;">
                    {{ $activeEvent['highlight'] }}
                </div>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(48, 54, 61, 0.6); padding-top: 10px; display: flex; justify-content: space-between; font-size: 0.82rem; color: #8b949e;">
            <span>⏳ Thời hạn: <strong style="color: #58a6ff;">{{ $timeRemaining }}</strong></span>
            <span>🧙 NPC Làng: <strong style="color: #ffd700;">{{ $activeEvent['npc'] }}</strong></span>
        </div>
    </div>

    <!-- Quick Server Stats -->
    <div class="card" style="background: rgba(13, 17, 23, 0.95); border: 1px solid #30363d; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1px; color: #8b949e; font-weight: 700; margin-bottom: 10px;">
                HƯỚNG DẪN VẬN HÀNH SỰ KIỆN
            </div>
            <ul style="margin: 0; padding-left: 18px; color: #c9d1d9; font-size: 0.82rem; line-height: 1.7;">
                <li><strong style="color: #3fb950;">Cập nhật Realtime:</strong> Server tức thời nạp NPC tại 3 Làng, gửi thông báo toàn server. Người chơi không bị ngắt kết nối.</li>
                <li><strong style="color: #f85149;">Đổi sự kiện & Bảo trì:</strong> Bật sự kiện và đếm ngược bảo trì kick toàn bộ người chơi để khi mở lại client game sẽ tải hình ảnh cây cối chuẩn nhất.</li>
                <li><strong style="color: #58a6ff;">Thời hạn:</strong> Nhập số giờ mong muốn diễn ra (nhập <code>0</code> = Vĩnh viễn không giới hạn).</li>
            </ul>
        </div>
        <div style="margin-top: 10px; display: flex; gap: 8px;">
            <button type="button" class="btn" onclick="clearClientCache()" style="background: #21262d; border: 1px solid #30363d; color: #8b949e; font-size: 0.78rem; padding: 5px 12px; border-radius: 6px;">
                🧹 Xóa Cache Nền Client
            </button>
            <a href="{{ route('admin.map_editor') }}" class="btn" style="background: #21262d; border: 1px solid #30363d; color: #58a6ff; font-size: 0.78rem; padding: 5px 12px; border-radius: 6px; text-decoration: none;">
                🗺️ Xem trước trên Map Editor
            </a>
        </div>
    </div>
</div>

<!-- Main Controller: Giống Control Admin của Java -->
<div class="card" style="margin-bottom: 24px; border: 1px solid rgba(248, 81, 73, 0.3); background: rgba(13, 17, 23, 0.98); padding: 22px;">
    <h3 style="color: #f85149; font-size: 1.05rem; margin-top: 0; margin-bottom: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; font-family: 'Orbitron', sans-serif;">
        🎮 BẢNG ĐIỀU KHIỂN SỰ KIỆN (CONTROL PANEL)
    </h3>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 16px;" class="ctrl-grid">
        <!-- Event Select -->
        <div>
            <label style="display: block; font-weight: 600; color: #f0f6fc; font-size: 0.85rem; margin-bottom: 6px;">
                Chọn sự kiện muốn kích hoạt (11 sự kiện):
            </label>
            <select id="eventSelect" class="form-input" style="width: 100%; height: 42px; background: #161b22; border: 1px solid #30363d; color: #f0f6fc; font-size: 0.9rem; font-weight: 600;" onchange="onSelectEventChange(this.value)">
                @foreach($events as $ev)
                    <option value="{{ $ev['id'] }}" {{ $currentEventId == $ev['id'] ? 'selected' : '' }}>
                        {{ $ev['icon'] }} {{ $ev['id'] }}: {{ $ev['name'] }} ({{ $ev['badge'] }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Duration Hours -->
        <div>
            <label style="display: block; font-weight: 600; color: #f0f6fc; font-size: 0.85rem; margin-bottom: 6px;">
                Thời hạn sự kiện:
            </label>
            <div style="display: flex; align-items: center; gap: 8px;">
                <input type="number" id="eventDuration" class="form-input" value="0" min="0" max="99999" style="width: 110px; height: 42px; background: #161b22; border: 1px solid #30363d; color: #f0f6fc; font-size: 0.92rem; font-family: monospace; text-align: center;">
                <span style="font-size: 0.82rem; color: #8b949e;">giờ (0 = VĨNH VIỄN)</span>
            </div>
        </div>
    </div>

    <!-- Event Detail Info Box -->
    <div id="eventDetailBox" style="background: rgba(22, 27, 34, 0.8); border: 1px solid #30363d; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span id="detailIcon" style="font-size: 1.5rem;">{{ $activeEvent['icon'] }}</span>
            <span id="detailName" style="color: #58a6ff; font-weight: 700; font-size: 0.95rem;">
                {{ $activeEvent['name'] }}
            </span>
            <span id="detailBadge" class="badge" style="background: {{ $activeEvent['color'] }}22; color: {{ $activeEvent['color'] }}; font-size: 0.72rem; padding: 2px 7px;">
                {{ $activeEvent['badge'] }}
            </span>
        </div>
        <div id="detailDesc" style="color: #c9d1d9; font-size: 0.84rem; line-height: 1.6; margin-bottom: 6px;">
            {{ $activeEvent['desc'] }}
        </div>
        <div style="font-size: 0.8rem; color: #8b949e;">
            🎁 <strong>Vật phẩm & Quà tặng:</strong> <span id="detailItems" style="color: #ffd700;">{{ $activeEvent['items'] }}</span>
        </div>
    </div>

    <!-- Action Buttons Row (3 Large Buttons matching AdminControlPanel) -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <button type="button" class="btn" onclick="submitEventAction(false)" style="background: linear-gradient(180deg, #2ea043, #238636); color: #fff; font-weight: 700; padding: 12px 20px; font-size: 0.92rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 2px 8px rgba(35, 134, 54, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span>⚡</span>
            <span>1. CẬP NHẬT REALTIME (KHÔNG DỪNG GAME)</span>
        </button>

        <button type="button" class="btn" onclick="submitEventAction(true)" style="background: linear-gradient(180deg, #da3633, #b62324); color: #fff; font-weight: 700; padding: 12px 20px; font-size: 0.92rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 2px 8px rgba(218, 54, 51, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span>⚠️</span>
            <span>2. ĐỔI SỰ KIỆN & BẢO TRÌ (KICK ALL 10s)</span>
        </button>
    </div>
</div>

<!-- 11 Events Showcase Grid -->
<div style="margin-bottom: 14px;">
    <h3 style="color: #f0f6fc; font-size: 1.05rem; margin: 0 0 14px 0; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        📚 DANH SÁCH TOÀN BỘ 11 SỰ KIỆN MÁY CHỦ
    </h3>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
    @foreach($events as $ev)
        @php $isCurrent = ($currentEventId == $ev['id']); @endphp
        <div class="card event-card" style="background: rgba(13, 17, 23, 0.9); border: 1px solid {{ $isCurrent ? $ev['color'] : '#30363d' }}; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s;" onmouseover="this.style.borderColor='{{ $ev['color'] }}'" onmouseout="this.style.borderColor='{{ $isCurrent ? $ev['color'] : '#30363d' }}'">
            @if($isCurrent)
                <div style="position: absolute; top: 10px; right: 10px; background: {{ $ev['color'] }}; color: #000; font-size: 0.68rem; font-weight: 800; padding: 2px 8px; border-radius: 12px; text-transform: uppercase;">
                    ĐANG BẬT
                </div>
            @endif

            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                    <div style="font-size: 2rem; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.06);">
                        {{ $ev['icon'] }}
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1rem; color: #fff; font-weight: 700;">
                            [{{ $ev['id'] }}] {{ $ev['name'] }}
                        </h4>
                        <span style="font-size: 0.72rem; color: {{ $ev['color'] }}; font-weight: 600;">
                            {{ $ev['badge'] }} · {{ $ev['npc'] }}
                        </span>
                    </div>
                </div>

                <p style="color: #8b949e; font-size: 0.8rem; line-height: 1.5; margin: 0 0 10px 0;">
                    {{ $ev['desc'] }}
                </p>

                <div style="font-size: 0.75rem; color: #ffd700; background: rgba(0,0,0,0.3); padding: 6px 10px; border-radius: 4px; margin-bottom: 12px;">
                    🎁 <strong>Vật phẩm:</strong> {{ $ev['items'] }}
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(48,54,61,0.5); padding-top: 10px;">
                <span style="font-size: 0.75rem; color: #8b949e;">ID: <code>{{ $ev['id'] }}</code></span>
                <button type="button" class="btn" onclick="quickApplyEvent({{ $ev['id'] }})" style="background: {{ $isCurrent ? '#238636' : '#21262d' }}; border: 1px solid {{ $isCurrent ? '#2ea043' : '#30363d' }}; color: {{ $isCurrent ? '#fff' : '#c9d1d9' }}; font-size: 0.78rem; padding: 4px 12px; border-radius: 6px; font-weight: 600;">
                    {{ $isCurrent ? '✓ Đang kích hoạt' : '⚡ Kích hoạt ngay' }}
                </button>
            </div>
        </div>
    @endforeach
</div>

<script>
const eventsMeta = @json($events);

function onSelectEventChange(id) {
    const ev = eventsMeta[id];
    if (!ev) return;
    document.getElementById('detailIcon').innerText = ev.icon;
    document.getElementById('detailName').innerText = ev.name;
    document.getElementById('detailBadge').innerText = ev.badge;
    document.getElementById('detailBadge').style.color = ev.color;
    document.getElementById('detailBadge').style.borderColor = ev.color + '66';
    document.getElementById('detailBadge').style.backgroundColor = ev.color + '22';
    document.getElementById('detailDesc').innerText = ev.desc;
    document.getElementById('detailItems').innerText = ev.items;
}

function quickApplyEvent(id) {
    document.getElementById('eventSelect').value = id;
    onSelectEventChange(id);
    submitEventAction(false);
}

function submitEventAction(isMaintenance) {
    const eventId = parseInt(document.getElementById('eventSelect').value);
    const duration = parseInt(document.getElementById('eventDuration').value) || 0;
    const ev = eventsMeta[eventId];
    const evName = ev ? ev.name : ('Sự kiện #' + eventId);

    const confirmMsg = isMaintenance 
        ? `⚠️ XÁC NHẬN BẬT SỰ KIỆN & BẢO TRÌ:\n- Sự kiện: [${eventId}] ${evName}\n- Thời hạn: ${duration > 0 ? duration + ' giờ' : 'Vĩnh viễn'}\n\nServer sẽ đếm ngược 10 giây và kick toàn bộ người chơi để cập nhật cache client. Bạn có chắc chắn?`
        : `⚡ XÁC NHẬN CẬP NHẬT REALTIME:\n- Sự kiện: [${eventId}] ${evName}\n- Thời hạn: ${duration > 0 ? duration + ' giờ' : 'Vĩnh viễn'}\n\nCập nhật ngay trên RAM mà không cần tắt game. Tiếp tục?`;

    if (!confirm(confirmMsg)) return;

    fetch("{{ route('admin.events.update') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            event_id: eventId,
            duration_hours: duration,
            maintenance: isMaintenance,
            delay_seconds: 10
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else {
            alert('Lỗi: ' + res.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi gửi yêu cầu kích hoạt sự kiện!');
    });
}

function clearClientCache() {
    if (!confirm('Bạn có chắc muốn xóa file cache nền client (bgItem, blend, map)?')) return;
    fetch("{{ route('admin.events.clear_cache') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message || 'Đã xóa cache thành công!');
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi xóa cache client!');
    });
}
</script>

<style>
@media (max-width: 768px) {
    .ctrl-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection
