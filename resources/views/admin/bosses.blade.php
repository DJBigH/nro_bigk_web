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
    foreach($bosses as $b) {
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
                <button type="button" class="badge" style="cursor: pointer; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 4px 10px;" onclick="filterBoss('all')">
                    Tất cả ({{ count($bosses) }})
                </button>
                <button type="button" class="badge" style="cursor: pointer; background: rgba(0, 230, 118, 0.2); color: #00e676; border: 1px solid #00e676; padding: 4px 10px;" onclick="filterBoss('live')">
                    ● LIVE ({{ $liveCount }})
                </button>
                <button type="button" class="badge" style="cursor: pointer; background: rgba(255, 23, 68, 0.2); color: #ff1744; border: 1px solid #ff1744; padding: 4px 10px;" onclick="filterBoss('die')">
                    ○ DIE ({{ $dieCount }})
                </button>
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
        <div style="overflow-x: auto;">
            <table style="margin-top: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID Boss</th>
                        <th>Tên Boss</th>
                        <th>Trạng Thái</th>
                        <th>Vị Trí Bản Đồ</th>
                        <th>Khu Vực</th>
                        <th>Máu Hiện Tại (HP)</th>
                        <th>Hồi Sinh Sau</th>
                        <th style="text-align: right;">Hành Động Realtime</th>
                    </tr>
                </thead>
                <tbody id="bossTableBody">
                    @foreach($bosses as $b)
                        @php
                            $isLive = ($b['status'] ?? 'LIVE') === 'LIVE';
                        @endphp
                        <tr class="boss-row" data-status="{{ $isLive ? 'live' : 'die' }}">
                            <td style="font-family: 'Orbitron', sans-serif; color: #aaa;">{{ $b['id'] }}</td>
                            <td style="font-weight: 700; color: {{ $isLive ? '#ff5722' : '#888' }};">
                                {{ $b['name'] }}
                            </td>
                            <td>
                                @if($isLive)
                                    <span class="badge" style="background: rgba(0, 230, 118, 0.15); color: #00e676; border: 1px solid #00e676; font-weight: 700; padding: 3px 8px;">
                                        ● LIVE
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(255, 23, 68, 0.15); color: #ff1744; border: 1px solid #ff1744; font-weight: 700; padding: 3px 8px;">
                                        ○ DIE
                                    </span>
                                @endif
                            </td>
                            <td style="color: #fff;">
                                {{ $b['mapName'] ?? 'Chưa xuất hiện' }}
                                @if(!empty($b['mapId']) && $b['mapId'] > 0)
                                    <span style="color: #888; font-size: 0.85rem;">(Map {{ $b['mapId'] }})</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif;">
                                @if($isLive && isset($b['zoneId']) && $b['zoneId'] >= 0)
                                    <span style="color: #00bcd4; font-weight: 700;">Khu {{ $b['zoneId'] }}</span>
                                @else
                                    <span style="color: #555;">-</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif;">
                                @if($isLive)
                                    <span style="color: #00e676; font-weight: 700;">{{ number_format($b['hp']) }}</span> / 
                                    <span style="color: #888;">{{ number_format($b['hpMax']) }}</span>
                                @else
                                    <span style="color: #555;">0</span> / 
                                    <span style="color: #666;">{{ number_format($b['hpMax']) }}</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif;">
                                @if(!$isLive && !empty($b['respawnIn']) && $b['respawnIn'] > 0)
                                    <span style="color: #ff9800; font-size: 0.85rem;">⏱️ {{ $b['respawnIn'] }}s</span>
                                @else
                                    <span style="color: #555;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($isLive)
                                    <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem; background: #d32f2f;" onclick="killBoss({{ $b['id'] }}, '{{ $b['name'] }}')">
                                        🛑 THU HỒI
                                    </button>
                                @else
                                    <button class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #ff5722; font-weight: 700;" onclick="spawnDirectBoss({{ $b['id'] }}, '{{ $b['name'] }}')">
                                        ⚡ HỒI SINH NGAY
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
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
    const bossId = document.getElementById('bossSelect').value;
    spawnDirectBoss(bossId, 'Boss');
});

function spawnDirectBoss(bossId, bossName) {
    if (!confirm('Bạn có muốn triệu hồi / hồi sinh ' + bossName + ' ngay lập tức?')) return;

    fetch('{{ route("admin.bosses.spawn") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ boss_id: parseInt(bossId) })
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message || 'Thực hiện thành công!');
        if (d.success || d.status === 'success') location.reload();
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
}

function killBoss(bossId, bossName) {
    if (!confirm('Bạn có chắc chắn muốn thu hồi boss ' + bossName + ' không?')) return;

    fetch('{{ route("admin.bosses.kill") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ boss_id: parseInt(bossId) })
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message || 'Thực hiện thành công!');
        if (d.success || d.status === 'success') location.reload();
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
}
</script>
@endsection
