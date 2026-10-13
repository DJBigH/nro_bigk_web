@extends('admin.layout')

@section('title', 'Thư Viện Vật Phẩm Game (Item Grid) - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 20px; border-left: 4px solid #00e676; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #00e676; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            📦 THƯ VIỆN VẬT PHẨM GAME (ITEM GRID)
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Tổng hợp toàn bộ {{ number_format($totalItems) }} vật phẩm trong máy chủ. Hỗ trợ tra cứu nhanh ID, lọc theo 40 danh mục và gửi trực tiếp vào Rương Web của người chơi!
        </p>
    </div>
    <div style="font-family: 'Orbitron', sans-serif; font-size: 1.1rem; color: #00e676; background: rgba(0,230,118,0.1); padding: 8px 16px; border-radius: 6px; border: 1px solid rgba(0,230,118,0.3);">
        {{ number_format($totalItems) }} ITEMS DATA
    </div>
</div>

<!-- Search & Filter Form -->
<div class="card" style="margin-bottom: 20px; padding: 18px 20px; border: 1px solid rgba(255,255,255,0.08);">
    <form action="{{ route('admin.items.lookup') }}" method="GET">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 12px; align-items: flex-end;">
            <div>
                <label style="color: #bbb; font-size: 0.8rem; margin-bottom: 5px; display: block;">Từ khóa / ID:</label>
                <input type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Nhập ID số hoặc tên vật phẩm..." style="margin-bottom: 0; padding: 9px 12px;">
            </div>

            <div>
                <label style="color: #bbb; font-size: 0.8rem; margin-bottom: 5px; display: block;">Hành tinh / Giới tính:</label>
                <select name="gender" class="form-input" style="margin-bottom: 0; padding: 9px 12px;">
                    <option value="-1" {{ $gender === '-1' || $gender === null ? 'selected' : '' }}>Tất cả hành tinh</option>
                    <option value="0" {{ $gender === '0' ? 'selected' : '' }}>🌍 Trái Đất</option>
                    <option value="1" {{ $gender === '1' ? 'selected' : '' }}>🌱 Namec</option>
                    <option value="2" {{ $gender === '2' ? 'selected' : '' }}>⚡ Xayda</option>
                    <option value="3" {{ $gender === '3' ? 'selected' : '' }}>✨ Dùng chung</option>
                </select>
            </div>

            <div>
                <label style="color: #bbb; font-size: 0.8rem; margin-bottom: 5px; display: block;">Số lượng mỗi trang:</label>
                <select name="per_page" class="form-input" style="margin-bottom: 0; padding: 9px 12px;">
                    <option value="24" {{ $perPage == 24 ? 'selected' : '' }}>24 Items</option>
                    <option value="48" {{ $perPage == 48 ? 'selected' : '' }}>48 Items</option>
                    <option value="96" {{ $perPage == 96 ? 'selected' : '' }}>96 Items</option>
                    <option value="120" {{ $perPage == 120 ? 'selected' : '' }}>120 Items</option>
                </select>
            </div>

            @if($type !== null && $type !== '')
                <input type="hidden" name="type" value="{{ $type }}">
            @endif

            <button type="submit" class="btn" style="background: #00e676; color: #000; font-weight: 700; padding: 10px 20px;">
                🔍 TÌM KIẾM
            </button>

            @if($search || ($gender !== null && $gender !== '-1') || ($type !== null && $type !== ''))
                <a href="{{ route('admin.items.lookup') }}" class="btn btn-secondary" style="padding: 10px 15px; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    RESET
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Type Categories Pills (Tag Filter) -->
<div class="card" style="margin-bottom: 25px; padding: 15px 20px;">
    <div style="color: #bbb; font-size: 0.85rem; margin-bottom: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
        🏷️ Lọc Nhanh Theo Danh Mục Tag ({{ count($itemTypes) }} Loại):
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 140px; overflow-y: auto; padding-right: 5px;">
        <a href="{{ route('admin.items.lookup', array_merge(request()->except(['type', 'page']), ['type' => ''])) }}" 
           class="badge" 
           style="text-decoration: none; padding: 6px 12px; font-size: 0.82rem; cursor: pointer; {{ ($type === null || $type === '') ? 'background: #00e676; color: #000; font-weight: 700;' : 'background: rgba(255,255,255,0.06); color: #ccc;' }}">
            Tất cả ({{ number_format($totalItems) }})
        </a>

        @foreach($itemTypes as $t)
            @php
                $cnt = $typeCounts[$t->id] ?? 0;
            @endphp
            @if($cnt > 0)
                <a href="{{ route('admin.items.lookup', array_merge(request()->except(['type', 'page']), ['type' => $t->id])) }}" 
                   class="badge" 
                   style="text-decoration: none; padding: 6px 12px; font-size: 0.82rem; cursor: pointer; {{ ((string)$type === (string)$t->id) ? 'background: #00e676; color: #000; font-weight: 700;' : 'background: rgba(255,255,255,0.06); color: #ccc; border: 1px solid rgba(255,255,255,0.1);' }}">
                    {{ $t->NAME }} <span style="opacity: 0.7; font-size: 0.75rem;">({{ $cnt }})</span>
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- Grid Items List -->
@if($items->isEmpty())
    <div class="card" style="padding: 50px 20px; text-align: center; color: var(--text-muted);">
        <div style="font-size: 3rem; margin-bottom: 10px;">🔍</div>
        <p style="font-size: 1.1rem; color: #fff;">Không tìm thấy vật phẩm nào phù hợp với bộ lọc!</p>
        <p style="font-size: 0.9rem;">Hãy thử tìm kiếm với từ khóa khác hoặc bấm nút "RESET" ở trên.</p>
        <a href="{{ route('admin.items.lookup') }}" class="btn" style="background: #00e676; color: #000; margin-top: 15px; font-weight: 700;">
            Xem toàn bộ vật phẩm
        </a>
    </div>
@else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; margin-bottom: 25px;">
        @php
            $typeIcons = [
                0 => '🥋', 1 => '👖', 2 => '🥊', 3 => '👟', 4 => '📡',
                5 => '🦹', 6 => '🫘', 7 => '📜', 8 => '📜', 9 => '💰',
                11 => '🎒', 12 => '🪙', 14 => '💎', 23 => '🐉', 27 => '📦',
                28 => '🚩', 30 => '⭐', 72 => '🦊', 75 => '⚡'
            ];
            $typeColors = [
                5 => '#e040fb', // Cải trang tím
                12 => '#ffd700', // Thỏi vàng vàng
                75 => '#ff5722', // Heroes Z cam đỏ
                72 => '#00e5ff', // Linh thú xanh cyan
                14 => '#00bcd4', // Đá nâng cấp
                7 => '#ff9800', // Sách kỹ năng
                11 => '#8bc34a', // Đeo lưng
            ];
        @endphp

        @foreach($items as $item)
            @php
                $itemIcon = $typeIcons[$item->TYPE] ?? '📦';
                $nameColor = $typeColors[$item->TYPE] ?? '#ffffff';
                
                // Hành tinh
                $genderText = 'Chung';
                $genderBadge = 'background: rgba(255,255,255,0.1); color: #ccc;';
                if ($item->gender == 0) {
                    $genderText = 'Trái Đất';
                    $genderBadge = 'background: rgba(33,150,243,0.15); color: #2196f3; border: 1px solid rgba(33,150,243,0.3);';
                } elseif ($item->gender == 1) {
                    $genderText = 'Namếc';
                    $genderBadge = 'background: rgba(76,175,80,0.15); color: #4caf50; border: 1px solid rgba(76,175,80,0.3);';
                } elseif ($item->gender == 2) {
                    $genderText = 'Xayda';
                    $genderBadge = 'background: rgba(244,67,54,0.15); color: #f44336; border: 1px solid rgba(244,67,54,0.3);';
                }
            @endphp
            
            <div class="card" style="padding: 16px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid rgba(255,255,255,0.08); background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(18,22,32,0.95) 100%); transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.borderColor='rgba(0,230,118,0.4)'; this.style.transform='translateY(-3px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.transform='none'">
                <!-- Top Row: ID & Icon -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: #00e676; font-size: 1.1rem; background: rgba(0,230,118,0.1); padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(0,230,118,0.25);">
                            #{{ $item->id }}
                        </span>
                        <span style="font-size: 0.75rem; color: #888; font-family: 'Orbitron', sans-serif;" title="Icon ID: {{ $item->icon_id }}">
                            Icon: {{ $item->icon_id }}
                        </span>
                    </div>

                    <!-- Icon & Name -->
                    <div style="text-align: center; margin: 10px 0 12px 0;">
                        <div style="height: 52px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                            <img src="/images/icons/{{ $item->icon_id }}.png" alt="{{ $item->NAME }}" style="max-height: 48px; max-width: 48px; object-fit: contain; filter: drop-shadow(0 0 6px rgba(255,255,255,0.25));" onerror="this.onerror=null; this.src='/images/no_image.png';">
                        </div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: {{ $nameColor }}; min-height: 40px; display: flex; align-items: center; justify-content: center; line-height: 1.3;" title="{{ $item->NAME }}">
                            {{ $item->NAME }}
                        </div>
                    </div>

                    <!-- Badges -->
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-bottom: 10px;">
                        <span class="badge" style="font-size: 0.72rem; background: rgba(255,255,255,0.08); color: #ddd; border: 1px solid rgba(255,255,255,0.1);">
                            Loại {{ $item->TYPE }}
                        </span>
                        <span class="badge" style="font-size: 0.72rem; {{ $genderBadge }}">
                            {{ $genderText }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p style="color: var(--text-muted); font-size: 0.8rem; height: 36px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-bottom: 12px; line-height: 1.35; text-align: center;" title="{{ $item->description }}">
                        {{ $item->description ? $item->description : 'Không có mô tả chi tiết.' }}
                    </p>

                    @if($item->power_require > 0)
                        <div style="font-size: 0.75rem; color: #ff9800; text-align: center; margin-bottom: 12px; font-family: 'Orbitron', sans-serif;">
                            Y/c SM: {{ number_format($item->power_require) }}
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 12px;">
                    <button type="button" class="btn btn-secondary" style="padding: 6px 8px; font-size: 0.75rem; font-weight: 600;" onclick="copyId({{ $item->id }})">
                        📋 Copy ID
                    </button>
                    <button type="button" class="btn" style="background: #00e676; color: #000; padding: 6px 8px; font-size: 0.75rem; font-weight: 700;" onclick="openSendWebChest({{ $item->id }}, '{{ addslashes($item->NAME) }}')">
                        🎁 Gửi Rương
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="card" style="padding: 15px 20px; display: flex; justify-content: center;">
        {{ $items->appends(request()->except('page'))->links() }}
    </div>
@endif

<!-- Modal Gửi Vật Phẩm Vào Rương Web -->
<div id="sendChestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 480px; border: 1px solid #00e676; box-shadow: 0 0 25px rgba(0,230,118,0.3); margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="color: #00e676; font-size: 1.15rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                🎁 GỬI VẬT PHẨM VÀO RƯƠNG WEB
            </h3>
            <button onclick="closeSendWebChest()" style="background: none; border: none; color: #888; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form id="sendChestForm">
            <input type="hidden" id="modalItemId" name="item_template_id">

            <div style="margin-bottom: 14px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Vật Phẩm Được Chọn:</label>
                <div id="modalItemName" style="color: #fff; font-weight: 700; font-size: 1rem; background: rgba(255,255,255,0.05); padding: 10px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Tên Tài Khoản (Username) Nhận:</label>
                <input type="text" id="modalUsername" name="username" class="form-input" placeholder="Nhập username người chơi..." required style="margin-bottom: 0;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Số Lượng Gửi:</label>
                <input type="number" id="modalQuantity" name="quantity" class="form-input" value="1" min="1" max="9999" required style="margin-bottom: 0;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <button type="button" class="btn btn-secondary" onclick="closeSendWebChest()">HỦY</button>
                <button type="submit" class="btn" style="background: #00e676; color: #000; font-weight: 700;">
                    🚀 GỬI VÀO RƯƠNG NGAY
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyId(id) {
    navigator.clipboard.writeText(id).then(() => {
        alert(`Đã sao chép ID vật phẩm: ${id} vào clipboard!`);
    }).catch(() => {
        prompt('Copy ID vật phẩm:', id);
    });
}

function openSendWebChest(id, name) {
    document.getElementById('modalItemId').value = id;
    document.getElementById('modalItemName').innerText = `[#${id}] ${name}`;
    document.getElementById('sendChestModal').style.display = 'flex';
}

function closeSendWebChest() {
    document.getElementById('sendChestModal').style.display = 'none';
}

document.getElementById('sendChestForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const itemId = document.getElementById('modalItemId').value;
    const username = document.getElementById('modalUsername').value.trim();
    const quantity = parseInt(document.getElementById('modalQuantity').value);

    if (!username) {
        alert('Vui lòng nhập username người nhận!');
        return;
    }

    fetch("{{ route('admin.web_chest.send') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            username: username,
            item_template_id: itemId,
            quantity: quantity,
            options: []
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(`Gửi thành công x${quantity} vật phẩm vào Rương Web của tài khoản [${username}]!`);
            closeSendWebChest();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể gửi vật phẩm!'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối tới Server!');
    });
});
</script>
@endsection
