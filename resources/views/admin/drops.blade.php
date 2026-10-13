@extends('admin.layout')

@section('title', 'Quản Lý Rơi Đồ Quái (Mob Drops) - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #ff4081; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #ff4081; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🎁 QUẢN LÝ HỆ THỐNG RƠI ĐỒ QUÁI (MOB DROPS)
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Tự do thêm/bớt vật phẩm rơi từ quái, thiết lập tỉ lệ rơi % và số lượng min-max theo từng Map hoặc toàn máy chủ!
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn" style="background: #ff4081; color: #fff; font-weight: 700; font-size: 0.85rem; padding: 8px 16px;" onclick="openAddDropModal()">
            ➕ THÊM RƠI ĐỒ MỚI
        </button>
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px;">
    <form action="{{ route('admin.drops') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
            <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Lọc Theo Bản Đồ (Map):</label>
            <select name="map_id" class="form-input" style="margin-bottom: 0;">
                <option value="">Tất cả bản đồ</option>
                <option value="-1" {{ $mapId === '-1' ? 'selected' : '' }}>Toàn server (Mọi Map)</option>
                @foreach($maps as $m)
                    <option value="{{ $m->id }}" {{ (string)$mapId === (string)$m->id ? 'selected' : '' }}>
                        Map {{ $m->id }}: {{ $m->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn" style="background: #00e5ff; color: #000; font-weight: 700; padding: 10px 20px;">
            🔍 LỌC DỮ LIỆU
        </button>

        @if($mapId !== null && $mapId !== '')
            <a href="{{ route('admin.drops') }}" class="btn btn-secondary" style="padding: 10px 15px; text-decoration: none;">
                RESET
            </a>
        @endif
    </form>
</div>

<!-- Drops Table -->
<div class="card" style="padding: 0;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">CẤU HÌNH RƠI VẬT PHẨM TỪ QUÁI</h3>
        <span style="color: #ff4081; font-size: 0.9rem; font-weight: 700;">{{ $drops->total() }} Dòng cấu hình</span>
    </div>

    @if($drops->isEmpty())
        <p style="padding: 30px; text-align: center; color: var(--text-muted);">Không tìm thấy cấu hình rơi đồ nào.</p>
    @else
        <div style="overflow-x: auto;">
            <table style="margin-top: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Bản Đồ (Map)</th>
                        <th>Vật Phẩm Rơi</th>
                        <th>Số Lượng (Min - Max)</th>
                        <th>Tỉ Lệ Rơi (%)</th>
                        <th style="text-align: center; width: 130px;">Trạng Thái</th>
                        <th style="text-align: right; width: 140px;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drops as $d)
                        <tr>
                            <td style="font-family: 'Orbitron', sans-serif; color: #888;">#{{ $d->id }}</td>
                            <td>
                                @if($d->map_id == -1)
                                    <span class="badge" style="background: rgba(0,229,255,0.15); color: #00e5ff; border: 1px solid rgba(0,229,255,0.3);">
                                        🌐 Mọi Map Server
                                    </span>
                                @else
                                    <span style="color: #fff; font-weight: 600;">{{ $d->map_name ?? 'Map ' . $d->map_id }}</span>
                                    <span style="color: #888; font-size: 0.8rem;">(ID {{ $d->map_id }})</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <img src="/images/icons/{{ $d->icon_id }}.png" style="width: 28px; height: 28px; object-fit: contain;" onerror="this.onerror=null; this.src='/images/no_image.png';">
                                    <div>
                                        <strong style="color: #ffd700;">{{ $d->item_name ?? 'Vật phẩm #' . $d->item_id }}</strong>
                                        <div style="font-size: 0.78rem; color: #888; font-family: 'Orbitron', sans-serif;">Item ID: {{ $d->item_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #4caf50; font-weight: 700;">
                                x{{ $d->quantity_min }} ~ x{{ $d->quantity_max }}
                            </td>
                            <td>
                                <span style="font-family: 'Orbitron', sans-serif; color: #ff9800; font-weight: 700; font-size: 1.05rem;">
                                    {{ $d->rate }}%
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn {{ $d->is_active ? 'btn-success' : 'btn-danger' }}" 
                                        style="padding: 4px 10px; font-size: 0.75rem; font-weight: 600;" 
                                        onclick="toggleDrop({{ $d->id }})">
                                    {{ $d->is_active ? '● ĐANG BẬT' : '○ ĐÃ TẮT' }}
                                </button>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                                    <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.78rem;" 
                                            onclick="openEditDropModal({{ $d->id }}, {{ $d->quantity_min }}, {{ $d->quantity_max }}, {{ $d->rate }})">
                                        ✏️ Sửa
                                    </button>
                                    <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.78rem; background: #d32f2f;" 
                                            onclick="deleteDrop({{ $d->id }})">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 15px 20px;">
            {{ $drops->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>

<!-- Modal Thêm Rơi Đồ Mới -->
<div id="addDropModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 480px; border: 1px solid #ff4081; box-shadow: 0 0 25px rgba(255,64,129,0.3); margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="color: #ff4081; font-size: 1.15rem; margin: 0;">➕ THÊM CẤU HÌNH RƠI ĐỒ QUÁI</h3>
            <button onclick="closeAddDropModal()" style="background: none; border: none; color: #888; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form id="addDropForm">
            <div style="margin-bottom: 14px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Bản Đồ Áp Dụng:</label>
                <select id="newMapId" class="form-input" style="margin-bottom: 0;">
                    <option value="-1">🌐 Toàn server (Mọi bản đồ)</option>
                    @foreach($maps as $m)
                        <option value="{{ $m->id }}">Map {{ $m->id }}: {{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">ID Vật Phẩm Rơi (item_id):</label>
                <input type="number" id="newItemId" class="form-input" placeholder="Ví dụ: 457 (Thỏi vàng), 861 (Hồng ngọc)..." required style="margin-bottom: 0; font-family: 'Orbitron', sans-serif;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div>
                    <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Số lượng Tối thiểu (Min):</label>
                    <input type="number" id="newQtyMin" class="form-input" value="1" min="1" required style="margin-bottom: 0;">
                </div>
                <div>
                    <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Số lượng Tối đa (Max):</label>
                    <input type="number" id="newQtyMax" class="form-input" value="1" min="1" required style="margin-bottom: 0;">
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Tỉ Lệ Rơi (% Thành Công):</label>
                <input type="number" id="newRate" class="form-input" value="10.0" min="0.01" max="100" step="0.1" required style="margin-bottom: 0; font-family: 'Orbitron', sans-serif;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddDropModal()">HỦY</button>
                <button type="submit" class="btn" style="background: #ff4081; color: #fff; font-weight: 700;">
                    🚀 THÊM VÀO MÁY CHỦ
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Rơi Đồ -->
<div id="editDropModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 450px; border: 1px solid #00e5ff; margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="color: #00e5ff; font-size: 1.15rem; margin: 0;">✏️ SỬA TỈ LỆ & SỐ LƯỢNG RƠI</h3>
            <button onclick="closeEditDropModal()" style="background: none; border: none; color: #888; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form id="editDropForm">
            <input type="hidden" id="editDropId">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div>
                    <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">SL Tối thiểu:</label>
                    <input type="number" id="editQtyMin" class="form-input" min="1" required style="margin-bottom: 0;">
                </div>
                <div>
                    <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">SL Tối đa:</label>
                    <input type="number" id="editQtyMax" class="form-input" min="1" required style="margin-bottom: 0;">
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Tỉ Lệ Rơi (%):</label>
                <input type="number" id="editRate" class="form-input" min="0.01" max="100" step="0.1" required style="margin-bottom: 0; font-family: 'Orbitron', sans-serif;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditDropModal()">HỦY</button>
                <button type="submit" class="btn" style="background: #00e5ff; color: #000; font-weight: 700;">
                    💾 LƯU THAY ĐỔI
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddDropModal() {
    document.getElementById('addDropModal').style.display = 'flex';
}
function closeAddDropModal() {
    document.getElementById('addDropModal').style.display = 'none';
}

function openEditDropModal(id, qMin, qMax, rate) {
    document.getElementById('editDropId').value = id;
    document.getElementById('editQtyMin').value = qMin;
    document.getElementById('editQtyMax').value = qMax;
    document.getElementById('editRate').value = rate;
    document.getElementById('editDropModal').style.display = 'flex';
}
function closeEditDropModal() {
    document.getElementById('editDropModal').style.display = 'none';
}

document.getElementById('addDropForm').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch("{{ route('admin.drops.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            map_id: document.getElementById('newMapId').value,
            item_id: document.getElementById('newItemId').value,
            quantity_min: document.getElementById('newQtyMin').value,
            quantity_max: document.getElementById('newQtyMax').value,
            rate: document.getElementById('newRate').value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Thêm rơi đồ thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể thêm!'));
        }
    })
    .catch(() => alert('Lỗi kết nối tới Server!'));
});

document.getElementById('editDropForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editDropId').value;

    fetch(`/admin/drops/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            quantity_min: document.getElementById('editQtyMin').value,
            quantity_max: document.getElementById('editQtyMax').value,
            rate: document.getElementById('editRate').value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Cập nhật thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật!'));
        }
    })
    .catch(() => alert('Lỗi kết nối tới Server!'));
});

function toggleDrop(id) {
    fetch(`/admin/drops/${id}/toggle`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể đổi trạng thái!'));
        }
    })
    .catch(() => alert('Lỗi kết nối tới Server!'));
}

function deleteDrop(id) {
    if (!confirm(`Bạn có chắc muốn xóa dòng rơi đồ này?`)) return;

    fetch(`/admin/drops/${id}`, {
        method: "DELETE",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Đã xóa thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa!'));
        }
    })
    .catch(() => alert('Lỗi kết nối tới Server!'));
}
</script>
@endsection
