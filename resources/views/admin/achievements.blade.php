@extends('admin.layout')

@section('title', 'Quản Lý Nhiệm Vụ Bò Mộng & Thành Tựu - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #ff9800; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #ff9800; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🎯 QUẢN LÝ NHIỆM VỤ BÒ MỘNG & THÀNH TỰU
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Cấu hình nội dung các mốc nhiệm vụ hàng ngày và phần thưởng Hồng Ngọc (Ruby) nhận tại NPC Bò Mộng trong game.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn" style="background: #ff9800; color: #000; font-weight: 700; font-size: 0.85rem; padding: 8px 16px;" onclick="openAddModal()">
            ➕ THÊM MỐC MỚI
        </button>
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI
        </button>
    </div>
</div>

<!-- Achievements Table -->
<div class="card" style="padding: 0;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">DANH SÁCH MỐC THÀNH TỰU GAME ({{ count($achievements) }} MỐC)</h3>
        <span style="color: #ff9800; font-size: 0.9rem; font-weight: 700;">Hồng Ngọc (Ruby)</span>
    </div>

    @if($achievements->isEmpty())
        <p style="padding: 30px; text-align: center; color: var(--text-muted);">Chưa có mốc thành tựu nào trong database.</p>
    @else
        <div style="overflow-x: auto;">
            <table style="margin-top: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 220px;">Nhóm Nhiệm Vụ</th>
                        <th>Yêu Cầu / Mô Tả Hoàn Thành</th>
                        <th style="width: 180px;">Thưởng Hồng Ngọc</th>
                        <th style="width: 140px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($achievements as $a)
                        <tr>
                            <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: #00e5ff;">
                                #{{ $a->id }}
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,152,0,0.15); color: #ff9800; border: 1px solid rgba(255,152,0,0.3); font-weight: 600; font-size: 0.85rem;">
                                    {{ $a->info1 }}
                                </span>
                            </td>
                            <td style="color: #fff; font-weight: 500;">
                                {{ str_replace('\n', ' - ', $a->info2) }}
                            </td>
                            <td>
                                <span style="font-family: 'Orbitron', sans-serif; color: #e91e63; font-weight: 700; font-size: 1.05rem;">
                                    💎 +{{ number_format($a->money) }}
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                                    <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.78rem;" 
                                            onclick="openEditModal({{ $a->id }}, '{{ addslashes($a->info1) }}', '{{ addslashes($a->info2) }}', {{ $a->money }})">
                                        ✏️ Sửa
                                    </button>
                                    <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.78rem; background: #d32f2f;" 
                                            onclick="deleteAchievement({{ $a->id }})">
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
            {{ $achievements->links() }}
        </div>
    @endif
</div>

<!-- Modal Thêm/Sửa Thành Tựu -->
<div id="achievementModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 500px; border: 1px solid #ff9800; box-shadow: 0 0 25px rgba(255,152,0,0.3); margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 id="modalTitle" style="color: #ff9800; font-size: 1.15rem; margin: 0;">
                🎯 CẤU HÌNH MỐC THÀNH TỰU
            </h3>
            <button onclick="closeModal()" style="background: none; border: none; color: #888; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form id="achievementForm">
            <input type="hidden" id="editId">

            <div style="margin-bottom: 14px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Nhóm Nhiệm Vụ (info1):</label>
                <input type="text" id="info1" class="form-input" placeholder="Ví dụ: Cày Cuốc, Tiêu Diệt Boss, Tiến Bộ..." required style="margin-bottom: 0;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Mô Tả / Yêu Cầu Hoàn Thành (info2):</label>
                <input type="text" id="info2" class="form-input" placeholder="Ví dụ: Tiêu diệt 10.000 Quái..." required style="margin-bottom: 0;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="color: #bbb; font-size: 0.85rem; margin-bottom: 5px; display: block;">Phần Thưởng Hồng Ngọc (Ruby):</label>
                <input type="number" id="money" class="form-input" placeholder="Số lượng Hồng Ngọc nhận được..." min="0" required style="margin-bottom: 0; font-family: 'Orbitron', sans-serif;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">HỦY</button>
                <button type="submit" class="btn" style="background: #ff9800; color: #000; font-weight: 700;">
                    💾 LƯU DỮ LIỆU
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditing = false;

function openAddModal() {
    isEditing = false;
    document.getElementById('editId').value = '';
    document.getElementById('info1').value = '';
    document.getElementById('info2').value = '';
    document.getElementById('money').value = 5000;
    document.getElementById('modalTitle').innerText = '➕ THÊM MỐC THÀNH TỰU MỚI';
    document.getElementById('achievementModal').style.display = 'flex';
}

function openEditModal(id, info1, info2, money) {
    isEditing = true;
    document.getElementById('editId').value = id;
    document.getElementById('info1').value = info1;
    document.getElementById('info2').value = info2;
    document.getElementById('money').value = money;
    document.getElementById('modalTitle').innerText = '✏️ CHỈNH SỬA MỐC THÀNH TỰU #' + id;
    document.getElementById('achievementModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('achievementModal').style.display = 'none';
}

document.getElementById('achievementForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const info1 = document.getElementById('info1').value.trim();
    const info2 = document.getElementById('info2').value.trim();
    const money = parseInt(document.getElementById('money').value);
    const id = document.getElementById('editId').value;

    const url = isEditing ? `/admin/achievements/${id}` : `{{ route('admin.achievements.store') }}`;
    const method = isEditing ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            info1: info1,
            info2: info2,
            money: money
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Thao tác thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể lưu!'));
        }
    })
    .catch(() => alert('Lỗi kết nối tới Server!'));
});

function deleteAchievement(id) {
    if (!confirm(`Bạn có chắc muốn xóa mốc thành tựu #${id}?`)) return;

    fetch(`/admin/achievements/${id}`, {
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
