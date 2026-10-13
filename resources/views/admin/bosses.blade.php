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
        <button class="btn btn-secondary" onclick="pollBossesData(true)" style="font-size: 0.85rem; padding: 8px 15px;">
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
                    <option value="-1">Kuku (Trái Đất)</option>
                    <option value="-2">Mập Đầu Đinh (Namếc)</option>
                    <option value="-3">Rambo (Xayda)</option>
                    <option value="-44">Broly</option>
                    <option value="-2434">Super Broly 24</option>
                    <option value="-2424">Đệ Tử Khủng</option>
                </optgroup>
                <optgroup label="Boss Fide & Tiểu Đội">
                    <option value="-19">Fide Đại Vương</option>
                    <option value="-18">Tiểu Đội Sát Thủ</option>
                    <option value="-23">Fide Robot</option>
                    <option value="-22">Vua Cold (King Cold)</option>
                    <option value="-21">Cooler Vàng (Cooler Gold)</option>
                </optgroup>
                <optgroup label="Boss Android & Cell">
                    <option value="-27">Android 19</option>
                    <option value="-28">Dr.Kore</option>
                    <option value="-24">Android 13</option>
                    <option value="-25">Android 14</option>
                    <option value="-26">Android 15</option>
                    <option value="-29">Pic</option>
                    <option value="-30">Poc</option>
                    <option value="-31">King Kong</option>
                    <option value="-47">Super Android 17</option>
                    <option value="-32">Xen Bọ Hung</option>
                    <option value="-33">Siêu Bọ Hung (Hoàn thiện)</option>
                    <option value="-34">Xen Con (Cell Jr)</option>
                </optgroup>
                <optgroup label="Boss Thần Thoại & Đỉnh Cao">
                    <option value="-203">Black Goku (Hắc Ám)</option>
                    <option value="-242">Super Black Goku 2</option>
                    <option value="-999">Gohan Beast</option>
                    <option value="-482">Thần Hủy Diệt Beerus (Bill)</option>
                    <option value="-436">Thiên Sứ Whis</option>
                    <option value="-984">Zamasu Tối Thượng</option>
                    <option value="-40">Ma Bư (Mabu)</option>
                    <option value="-236">Mabu 12 Giờ</option>
                    <option value="-610">Goku SSJ5</option>
                    <option value="-611">Devil Vegeta (Vegeta Ác Thần)</option>
                </optgroup>
                <optgroup label="Boss Phụ Bản & Khác">
                    <option value="-50">Drabura</option>
                    <option value="-234">Bùi Bùi</option>
                    <option value="-235">Yacôn</option>
                    <option value="-51">Tàu Pảy Pảy M</option>
                    <option value="-43">Ăn Trộm</option>
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
                    Tất cả (<span id="badgeCountAll">{{ count($bossList) }}</span>)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'live', 'page' => 1]) }}" class="badge" style="text-decoration: none; cursor: pointer; background: {{ ($statusFilter ?? '') === 'live' ? '#00e676' : 'rgba(0, 230, 118, 0.2)' }}; color: {{ ($statusFilter ?? '') === 'live' ? '#000' : '#00e676' }}; border: 1px solid #00e676; padding: 4px 10px; font-weight: 700;">
                    ● LIVE (<span id="badgeCountLive">{{ $liveCount }}</span>)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'die', 'page' => 1]) }}" class="badge" style="text-decoration: none; cursor: pointer; background: {{ ($statusFilter ?? '') === 'die' ? '#ff1744' : 'rgba(255, 23, 68, 0.2)' }}; color: {{ ($statusFilter ?? '') === 'die' ? '#fff' : '#ff1744' }}; border: 1px solid #ff1744; padding: 4px 10px; font-weight: 700;">
                    ○ DIE (<span id="badgeCountDie">{{ $dieCount }}</span>)
                </a>
            </div>
        </div>
        <div style="font-size: 0.85rem; color: #888;">
            <span style="color: #00e676; font-weight: 700;"><span id="subLiveCount">{{ $liveCount }}</span> Đang Sống</span> | 
            <span style="color: #ff1744; font-weight: 700;"><span id="subDieCount">{{ $dieCount }}</span> Chờ Hồi Sinh</span>
        </div>
    </div>

    @if(empty($bosses))
        <p id="emptyBossMsg" style="padding: 30px; text-align: center; color: var(--text-muted);">Hiện tại không thể kết nối tới Game Server hoặc danh sách Boss đang rỗng.</p>
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
                        <tr class="boss-row" data-id="{{ $b['id'] }}" data-status="{{ $isLive ? 'live' : 'die' }}">
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
const currentStatusFilter = '{{ $statusFilter ?? 'all' }}';
const currentPageNum = {{ request()->get('page', 1) }};
const currentPerPage = {{ $perPage ?? 5 }};

// -------------------------------------------------------------
// Realtime Client Countdown Ticker (Chạy mỗi 1000ms đếm lùi)
// -------------------------------------------------------------
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

// -------------------------------------------------------------
// REALTIME POLLING: Tự động cập nhật danh sách Boss không cần F5
// -------------------------------------------------------------
function pollBossesData(showToastNotice = false) {
    const url = `{{ route('admin.bosses.data') }}?status=${currentStatusFilter}&page=${currentPageNum}&per_page=${currentPerPage}`;
    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;

            // 1. Cập nhật số đếm thống kê
            const countAll = document.getElementById('badgeCountAll');
            if (countAll) countAll.innerText = res.totalCount || 0;

            const countLive = document.getElementById('badgeCountLive');
            if (countLive) countLive.innerText = res.liveCount || 0;

            const countDie = document.getElementById('badgeCountDie');
            if (countDie) countDie.innerText = res.dieCount || 0;

            const subLive = document.getElementById('subLiveCount');
            if (subLive) subLive.innerText = res.liveCount || 0;

            const subDie = document.getElementById('subDieCount');
            if (subDie) subDie.innerText = res.dieCount || 0;

            // 2. Cập nhật các hàng Boss trong bảng
            const tbody = document.getElementById('bossTableBody');
            if (!tbody || !res.bosses) return;

            let html = '';
            res.bosses.forEach(b => {
                const isLive = (b.status === 'LIVE');
                const safeName = (b.name || 'Boss').replace(/'/g, "\\'");
                const hpFormatted = Number(b.hp || 0).toLocaleString();
                const hpMaxFormatted = Number(b.hpMax || 0).toLocaleString();

                let respawnHtml = '<span style="color: #555;">-</span>';
                if (!isLive) {
                    if (b.respawnIn && b.respawnIn > 0) {
                        respawnHtml = `<span class="respawn-ticker" data-seconds="${b.respawnIn}" style="color: #ff9800; font-size: 0.82rem; font-weight: 700; display: inline-block;">⏱️ ${b.respawnIn}s</span>`;
                    } else {
                        respawnHtml = `<span style="color: #00e676; font-size: 0.78rem; font-weight: 700;">⚡ Sẵn sàng</span>`;
                    }
                }

                const actionHtml = isLive
                    ? `<button class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; background: #d32f2f; white-space: nowrap;" onclick="killBoss(${b.id}, '${safeName}')">🛑 THU HỒI</button>`
                    : `<button class="btn" style="padding: 4px 10px; font-size: 0.75rem; background: #ff5722; font-weight: 700; white-space: nowrap;" onclick="spawnDirectBoss(${b.id}, '${safeName}')">⚡ HỒI SINH</button>`;

                const statusBadge = isLive
                    ? `<span class="badge" style="background: rgba(0, 230, 118, 0.15); color: #00e676; border: 1px solid #00e676; font-weight: 700; padding: 2px 7px; font-size: 0.75rem;">● LIVE</span>`
                    : `<span class="badge" style="background: rgba(255, 23, 68, 0.15); color: #ff1744; border: 1px solid #ff1744; font-weight: 700; padding: 2px 7px; font-size: 0.75rem;">○ DIE</span>`;

                const zoneHtml = (isLive && b.zoneId !== undefined && b.zoneId >= 0)
                    ? `<span style="color: #00bcd4; font-weight: 700; font-size: 0.82rem;">Khu ${b.zoneId}</span>`
                    : `<span style="color: #555;">-</span>`;

                const mapLabel = (b.mapName || 'Chưa xuất hiện') + (b.mapId && b.mapId > 0 ? ` <span style="color: #888; font-size: 0.75rem;">(Map ${b.mapId})</span>` : '');

                html += `
                    <tr class="boss-row" data-id="${b.id}" data-status="${isLive ? 'live' : 'die'}">
                        <td style="font-family: 'Orbitron', sans-serif; color: #888; font-size: 0.8rem; padding: 10px 8px;">${b.id}</td>
                        <td style="font-weight: 700; color: ${isLive ? '#ff5722' : '#888'}; font-size: 0.85rem; padding: 10px 8px; word-break: break-word;">${b.name}</td>
                        <td style="padding: 10px 8px;">${statusBadge}</td>
                        <td style="color: #fff; font-size: 0.82rem; padding: 10px 8px; word-break: break-word;">${mapLabel}</td>
                        <td style="font-family: 'Orbitron', sans-serif; padding: 10px 8px;">${zoneHtml}</td>
                        <td style="font-family: 'Orbitron', sans-serif; font-size: 0.78rem; padding: 10px 8px;">
                            ${isLive ? `<span style="color: #00e676; font-weight: 700;">${hpFormatted}</span>` : '<span style="color: #555;">0</span>'}
                            <div style="color: #666; font-size: 0.72rem;">/ ${hpMaxFormatted}</div>
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; padding: 10px 8px;">${respawnHtml}</td>
                        <td style="text-align: right; white-space: nowrap; padding: 10px 8px;">${actionHtml}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            if (showToastNotice && window.showToast) {
                window.showToast('Đã làm mới danh sách Boss realtime', 'info');
            }
        })
        .catch(() => {});
}

// Tự động thăm dò cập nhật danh sách Boss mỗi 3 giây
setInterval(pollBossesData, 3000);

// -------------------------------------------------------------
// XỬ LÝ TRIỆU HỒI VÀ THU HỒI BOSS
// -------------------------------------------------------------
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
        message: `Bạn có đồng ý <strong>HỒI SINH NGAY LẬP TỨC</strong> boss <strong style="color: #ff5722;">"${bossName}"</strong> vào bản đồ không?`,
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
        const isSuccess = (d.success || d.status === 'success');
        await window.showModalAlert('KẾT QUẢ', d.message || 'Boss đã được hồi sinh và vào map!', isSuccess ? 'success' : 'error');
        if (isSuccess) pollBossesData();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ Game', 'error'));
}

async function killBoss(bossId, bossName) {
    const confirmed = await window.showConfirm({
        title: 'THU HỒI BOSS',
        message: `Bạn có đồng ý <strong>THU HỒI</strong> boss <strong style="color: #ff1744;">"${bossName}"</strong> rời khỏi bản đồ không?`,
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
        const isSuccess = (d.success || d.status === 'success');
        await window.showModalAlert('KẾT QUẢ', d.message || 'Đã thu hồi boss thành công!', isSuccess ? 'success' : 'error');
        if (isSuccess) pollBossesData();
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối máy chủ Game', 'error'));
}
</script>
@endsection
