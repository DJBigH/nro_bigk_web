@extends('admin.layout')

@section('title', 'Quản Lý & Triệu Hồi Boss - Cyber Admin NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #ff5722; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #ff5722; font-size: 1.3rem; display: flex; align-items: center; gap: 8px;">
            👹 QUẢN LÝ & TRIỆU HỒI BOSS REALTIME
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Giám sát trạng thái <strong>LIVE (Đang sống)</strong> hoặc <strong>DIE (Đã chết / Chờ hồi sinh)</strong> của toàn bộ Boss trong thế giới game.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI DANH SÁCH
        </button>
    </div>
</div>

<!-- Summon Boss Panel -->
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(255,87,34,0.3);">
    <h3 style="color: #ff9800; font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
        ⚡ TRIỆU HỒI BOSS MỚI VÀO MÁY CHỦ
    </h3>
    
    <form id="summonBossForm" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: flex-end;">
        <div>
            <label style="color: #fff; font-size: 0.85rem; margin-bottom: 6px; display: block;">Chọn Boss Muốn Triệu Hồi:</label>
            <select id="bossSelect" class="form-input" style="width: 100%; margin-bottom: 0;" required>
                <optgroup label="Boss Cày Cuốc Cơ Bản">
                    <option value="-20">Kuku (Trái Đất)</option>
                    <option value="-21">Mập Đầu Đinh (Namếc)</option>
                    <option value="-22">Rambo (Xayda)</option>
                    <option value="-1">Broly</option>
                    <option value="-10">Super Broly 24</option>
                </optgroup>
                <optgroup label="Boss Fide & Tiểu Đội">
                    <option value="-9">Fide Đại Vương</option>
                    <option value="-11">Fide Robot</option>
                    <option value="-12">Vua Cold (King Cold)</option>
                </optgroup>
                <optgroup label="Boss Android & Cell">
                    <option value="-13">Xen Bọ Hung (Cấp 1 & 2)</option>
                    <option value="-14">Siêu Bọ Hung (Hoàn thiện)</option>
                    <option value="-15">Xen Con (Cell Jr)</option>
                    <option value="-16">Super Android 17</option>
                </optgroup>
                <optgroup label="Boss Thần Thoại & Đỉnh Cao">
                    <option value="-24">Black Goku (Hắc Ám)</option>
                    <option value="-25">Gohan Beast</option>
                    <option value="-26">Thần Hủy Diệt Beerus (Bill)</option>
                    <option value="-27">Thiên Sứ Whis</option>
                    <option value="-28">Zamasu Tối Thượng</option>
                    <option value="-29">Ma Bư (Mabu 12h)</option>
                    <option value="-31">Devil Vegeta (Vegeta Ác Thần)</option>
                    <option value="-32">Goku SSJ5</option>
                    <option value="-30">Đệ Tử Khủng</option>
                </optgroup>
            </select>
        </div>
        <div>
            <label style="color: #fff; font-size: 0.85rem; margin-bottom: 6px; display: block;">Vị trí xuất hiện:</label>
            <input type="text" class="form-input" value="Map Mặc Định Của Boss" disabled style="width: 100%; opacity: 0.7; margin-bottom: 0;">
        </div>
        <button type="submit" class="btn" style="background: #ff5722; font-weight: 700; padding: 11px 20px;">
            🚀 TRIỆU HỒI NGAY
        </button>
    </form>
</div>

<!-- Active Bosses List -->
@php
    $liveCount = 0;
    $dieCount = 0;
    $bossList = $allBosses ?? $bosses;
    foreach($bossList as $b) {
        if(($b['status'] ?? 'LIVE') === 'LIVE') {
            $liveCount++;
        } else {
            $dieCount++;
        }
    }
@endphp

<div class="card" style="padding: 0;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">DANH SÁCH TOÀN BỘ BOSS TRÊN MÁY CHỦ</h3>
            <div style="display: flex; gap: 6px;">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" class="badge" style="text-decoration: none; cursor: pointer; background: {{ ($statusFilter ?? 'all') === 'all' ? 'var(--primary)' : 'rgba(255,255,255,0.1)' }}; color: {{ ($statusFilter ?? 'all') === 'all' ? '#000' : '#fff' }}; border: 1px solid rgba(255,255,255,0.2); padding: 4px 10px; font-weight: 700;">
                    Tất cả ({{ count($bossList) }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'live', 'page' => 1]) }}" class="badge" style="text-decoration: none; cursor: pointer; background: {{ ($statusFilter ?? '') === 'live' ? '#00e676' : 'rgba(0, 230, 118, 0.2)' }}; color: {{ ($statusFilter ?? '') === 'live' ? '#000' : '#00e676' }}; border: 1px solid #00e676; padding: 4px 10px; font-weight: 700;">
                    ● LIVE ({{ $liveCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'die', 'page' => 1]) }}" class="badge" style="text-decoration: none; cursor: pointer; background: {{ ($statusFilter ?? '') === 'die' ? '#ff1744' : 'rgba(255, 23, 68, 0.2)' }}; color: {{ ($statusFilter ?? '') === 'die' ? '#fff' : '#ff1744' }}; border: 1px solid #ff1744; padding: 4px 10px; font-weight: 700;">
                    ○ DIE ({{ $dieCount }})
                </a>
            </div>
        </div>
        <div style="font-size: 0.85rem; color: #888;">
            <span style="color: #00e676; font-weight: 700;">{{ $liveCount }} Đang Sống</span> | 
            <span style="color: #ff1744; font-weight: 700;">{{ $dieCount }} Chờ Hồi Sinh</span>
        </div>
    </div>

    @if(empty($bosses))
        <p style="padding: 30px; text-align: center; color: var(--text-muted);">Hiện tại không thể kết nối tới Game Server hoặc danh sách Boss đang rỗng.</p>
    @else
        <div style="width: 100%; overflow-x: auto;">
            <table style="margin-top: 0; width: 100%; table-layout: fixed; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 55px; padding: 10px 8px; font-size: 0.8rem;">ID</th>
                        <th style="width: 18%; padding: 10px 8px; font-size: 0.8rem;">Tên Boss</th>
                        <th style="width: 10%; padding: 10px 8px; font-size: 0.8rem;">Trạng Thái</th>
                        <th style="width: 20%; padding: 10px 8px; font-size: 0.8rem;">Vị Trí Bản Đồ</th>
                        <th style="width: 8%; padding: 10px 8px; font-size: 0.8rem;">Khu Vực</th>
                        <th style="width: 16%; padding: 10px 8px; font-size: 0.8rem;">Máu (HP)</th>
                        <th style="width: 13%; padding: 10px 8px; font-size: 0.8rem;">Hồi Sinh Sau</th>
                        <th style="width: 15%; padding: 10px 8px; font-size: 0.8rem; text-align: right;">Hành Động Realtime</th>
                    </tr>
                </thead>
                <tbody id="bossTableBody">
                    @foreach($bosses as $b)
                        @php
                            $isLive = ($b['status'] ?? 'LIVE') === 'LIVE';
                            $safeBossName = addslashes($b['name'] ?? 'Boss');
                        @endphp
                        <tr class="boss-row" data-status="{{ $isLive ? 'live' : 'die' }}">
                            <td style="font-family: 'Orbitron', sans-serif; color: #888; font-size: 0.8rem; padding: 10px 8px;">{{ $b['id'] }}</td>
                            <td style="font-weight: 700; color: {{ $isLive ? '#ff5722' : '#888' }}; font-size: 0.85rem; padding: 10px 8px; word-break: break-word;">
                                {{ $b['name'] }}
                            </td>
                            <td style="padding: 10px 8px;">
                                @if($isLive)
                                    <span class="badge" style="background: rgba(0, 230, 118, 0.15); color: #00e676; border: 1px solid #00e676; font-weight: 700; padding: 2px 7px; font-size: 0.75rem;">
                                        ● LIVE
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(255, 23, 68, 0.15); color: #ff1744; border: 1px solid #ff1744; font-weight: 700; padding: 2px 7px; font-size: 0.75rem;">
                                        ○ DIE
                                    </span>
                                @endif
                            </td>
                            <td style="color: #fff; font-size: 0.82rem; padding: 10px 8px; word-break: break-word;">
                                {{ $b['mapName'] ?? 'Chưa xuất hiện' }}
                                @if(!empty($b['mapId']) && $b['mapId'] > 0)
                                    <span style="color: #888; font-size: 0.75rem;">(Map {{ $b['mapId'] }})</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; padding: 10px 8px;">
                                @if($isLive && isset($b['zoneId']) && $b['zoneId'] >= 0)
                                    <span style="color: #00bcd4; font-weight: 700; font-size: 0.82rem;">Khu {{ $b['zoneId'] }}</span>
                                @else
                                    <span style="color: #555;">-</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; font-size: 0.78rem; padding: 10px 8px;">
                                @if($isLive)
                                    <span style="color: #00e676; font-weight: 700;">{{ number_format($b['hp']) }}</span>
                                    <div style="color: #666; font-size: 0.72rem;">/ {{ number_format($b['hpMax']) }}</div>
                                @else
                                    <span style="color: #555;">0</span>
                                    <div style="color: #555; font-size: 0.72rem;">/ {{ number_format($b['hpMax']) }}</div>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; padding: 10px 8px;">
                                @if(!$isLive && !empty($b['respawnIn']) && $b['respawnIn'] > 0)
                                    <span class="respawn-ticker" data-seconds="{{ $b['respawnIn'] }}" style="color: #ff9800; font-size: 0.82rem; font-weight: 700; display: inline-block;">
                                        ⏱️ {{ $b['respawnIn'] }}s
                                    </span>
                                @elseif(!$isLive)
                                    <span style="color: #00e676; font-size: 0.78rem; font-weight: 700;">⚡ Sẵn sàng</span>
                                @else
                                    <span style="color: #555;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap; padding: 10px 8px;">
                                @if($isLive)
                                    <button class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; background: #d32f2f; white-space: nowrap;" onclick="killBoss({{ $b['id'] }}, '{{ $safeBossName }}')">
                                        🛑 THU HỒI
                                    </button>
                                @else
                                    <button class="btn" style="padding: 4px 10px; font-size: 0.75rem; background: #ff5722; font-weight: 700; white-space: nowrap;" onclick="spawnDirectBoss({{ $b['id'] }}, '{{ $safeBossName }}')">
                                        ⚡ HỒI SINH
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="padding: 15px 20px;">
            {{ $bosses->links() }}
        </div>
    @endif
</div>

<script>
// Realtime Client Countdown Ticker (Runs every 1000ms without F5)
setInterval(() => {
    document.querySelectorAll('.respawn-ticker').forEach(el => {
        let sec = parseInt(el.getAttribute('data-seconds')) || 0;
        if (sec > 0) {
            sec--;
            el.setAttribute('data-seconds', sec);
            el.innerText = '⏱️ ' + sec + 's';
            if (sec === 0) {
                el.innerText = '⚡ Sẵn sàng';
                el.style.color = '#00e676';
            }
        }
    });
}, 1000);

function filterBoss(type) {
    const rows = document.querySelectorAll('.boss-row');
    rows.forEach(r => {
        if (type === 'all' || r.getAttribute('data-status') === type) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

document.getElementById('summonBossForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const bossSelect = document.getElementById('bossSelect');
    const bossId = bossSelect.value;
    const bossName = bossSelect.options[bossSelect.selectedIndex]?.text || 'Boss';
    spawnDirectBoss(bossId, bossName);
});

async function spawnDirectBoss(bossId, bossName) {
    const confirmed = await window.showConfirm({
        title: 'HỒI SINH BOSS NGAY',
        message: `Bạn có đồng ý <strong>HỒI SINH NGAY LẬP TỨC</strong> boss <strong>"${bossName}"</strong> vào bản đồ không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;

    fetch('{{ route("admin.bosses.spawn") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ boss_id: parseInt(bossId) })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('THÀNH CÔNG', d.message || 'Boss đã được hồi sinh và vào map!', (d.success || d.status === 'success') ? 'success' : 'error');
        if (d.success || d.status === 'success') location.reload();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}

async function killBoss(bossId, bossName) {
    const confirmed = await window.showConfirm({
        title: 'THU HỒI BOSS',
        message: `Bạn có đồng ý <strong>THU HỒI</strong> boss <strong>"${bossName}"</strong> rời khỏi bản đồ không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch('{{ route("admin.bosses.kill") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ boss_id: parseInt(bossId) })
    })
    .then(r => r.json())
    .then(async d => {
        await window.showModalAlert('THÀNH CÔNG', d.message || 'Đã thu hồi boss thành công!', (d.success || d.status === 'success') ? 'success' : 'error');
        if (d.success || d.status === 'success') location.reload();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ', 'error'));
}
</script>
@endsection
