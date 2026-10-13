@extends('admin.layout')

@section('title', 'Cấu Hình Vòng Quay - Admin NRO BigK')

@section('admin_content')
<!-- General Settings -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
        CÀI ĐẶT CHUNG VÒNG QUAY
    </h2>
    <form id="settings-form" style="display: grid; grid-template-columns: 1.2fr 1fr auto; gap: 20px; align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Giá mỗi lượt quay (VND)</label>
            <input type="number" name="spin_price_vnd" value="{{ $spinPrice }}" class="form-input" min="0" required>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Trạng thái vòng quay</label>
            <select name="spin_enabled" class="form-input" required>
                <option value="1" {{ $spinEnabled ? 'selected' : '' }}>Hoạt động</option>
                <option value="0" {{ !$spinEnabled ? 'selected' : '' }}>Tạm dừng</option>
            </select>
        </div>

        <button type="submit" id="settings-btn" class="btn">LƯU CÀI ĐẶT</button>
    </form>
</div>

<!-- Segment Items list header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: var(--primary);">DANH SÁCH Ô PHẦN THƯỞNG</h2>
    <button class="btn" onclick="openAddModal()">THÊM Ô GIẢI THƯỞNG</button>
</div>

<!-- List Table -->
<div class="card" style="padding: 0;">
    @if($spinItems->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa cấu hình ô giải thưởng nào.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>Sắp Xếp</th>
                    <th>Tên Hiển Thị</th>
                    <th>Phần Thưởng Thực</th>
                    <th>Tỉ Lệ</th>
                    <th>Màu Ô</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @php $sumRatio = 0; @endphp
                @foreach($spinItems as $item)
                    @php if ($item->is_active == 1) $sumRatio += $item->ratio; @endphp
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $item->sort_order }}</td>
                        <td style="font-weight: 700; color: #fff;">{{ $item->display_name }}</td>
                        <td>
                            @if($item->reward_type === 'item')
                                <span class="badge badge-primary">ITEM</span>
                                <span style="font-size: 0.95rem;">Template ID: {{ $item->item_template_id }} (SL: {{ $item->reward_value }})</span>
                            @elseif($item->reward_type === 'vnd')
                                <span class="badge badge-success">VND</span>
                                <span style="font-size: 0.95rem; color: var(--secondary); font-weight: 700;">+{{ number_format($item->reward_value) }} đ</span>
                            @else
                                <span class="badge badge-secondary">XU GAME</span>
                                <span style="font-size: 0.95rem;">+{{ number_format($item->reward_value) }} Xu</span>
                            @endif
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary);">
                            {{ $item->ratio }}%
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 20px; height: 20px; border-radius: 4px; border: 1px solid #fff; background-color: {{ $item->color }};"></div>
                                <span style="font-size: 0.9rem; font-family: 'Orbitron', sans-serif;">{{ $item->color }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->is_active == 1)
                                <span class="badge badge-success">ACTIVE</span>
                            @else
                                <span class="badge badge-danger">DISABLE</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="openEditModal({{ $item }})">
                                    Sửa
                                </button>
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deleteSpinItem({{ $item->id }})">
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="background: rgba(255, 107, 0, 0.05); padding: 15px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <span style="color: var(--text-muted); font-size: 0.95rem;">Lưu ý: Tổng tỉ lệ của tất cả các ô hoạt động nên bằng chính xác 100%.</span>
            <strong style="color: var(--secondary); font-family: 'Orbitron', sans-serif;">
                TỔNG TỈ LỆ HIỆN TẠI: <span style="color: {{ abs($sumRatio - 100) < 0.05 ? '#4caf50' : '#f44336' }}">{{ $sumRatio }}%</span>
            </strong>
        </div>
    @endif
</div>

<!-- Modal Spin Item CRUD -->
<div id="spin-item-modal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3 id="modal-title" style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            THÊM Ô GIẢI THƯỞNG VÒNG QUAY
        </h3>
        
        <form id="spin-item-form">
            <input type="hidden" id="form-item-id" name="id">

            <!-- Display Name -->
            <div class="form-group">
                <label class="form-label">Tên hiển thị trên vòng quay</label>
                <input type="text" id="form-name" name="display_name" class="form-input" placeholder="VD: Thỏi Vàng x1, 5.000đ VND..." required>
            </div>

            <!-- Reward type selector -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Loại phần thưởng</label>
                    <select id="form-reward-type" name="reward_type" class="form-input" required onchange="onRewardTypeChange()">
                        <option value="item">Vật phẩm game</option>
                        <option value="vnd">VND cộng số dư</option>
                        <option value="coin">Xu game</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Giá trị (Số lượng / số tiền)</label>
                    <input type="number" id="form-reward-val" name="reward_value" value="1" class="form-input" min="0" required>
                </div>
            </div>

            <!-- Game Item ID (only enabled on 'item' reward type) -->
            <div class="form-group" id="item-template-group">
                <label class="form-label">ID Template vật phẩm game</label>
                <input type="number" id="form-template-id" name="item_template_id" class="form-input" placeholder="Nhập ID vật phẩm trong game...">
            </div>

            <!-- Odds Ratio & Color Hex -->
            <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Tỉ lệ trúng (%)</label>
                    <input type="number" id="form-ratio" name="ratio" step="0.01" min="0" max="100" class="form-input" placeholder="VD: 15.5" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Màu ô hiển thị (Hex)</label>
                    <input type="color" id="form-color" name="color" value="#ff6b00" style="padding: 0; height: 50px; cursor: pointer;" class="form-input" required>
                </div>
            </div>

            <!-- Sorting index & Active status -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Thứ tự hiển thị</label>
                    <input type="number" id="form-sort" name="sort_order" value="1" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái</label>
                    <select id="form-active" name="is_active" class="form-input" required>
                        <option value="1">Kích hoạt</option>
                        <option value="0">Tạm tắt</option>
                    </select>
                </div>
            </div>

            <button type="submit" id="modal-submit-btn" class="btn" style="width: 100%;">
                XÁC NHẬN
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let mode = 'add';

    function onRewardTypeChange() {
        const type = document.getElementById('form-reward-type').value;
        const group = document.getElementById('item-template-group');
        const input = document.getElementById('form-template-id');
        
        if (type === 'item') {
            group.style.display = 'block';
            input.required = true;
        } else {
            group.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    // Modal helpers
    function openAddModal() {
        mode = 'add';
        document.getElementById('modal-title').innerText = 'THÊM Ô PHẦN THƯỞNG VÒNG QUAY';
        document.getElementById('form-item-id').value = '';
        document.getElementById('spin-item-form').reset();
        onRewardTypeChange();
        document.getElementById('spin-item-modal').classList.add('show');
    }

    function openEditModal(item) {
        mode = 'edit';
        document.getElementById('modal-title').innerText = 'CẬP NHẬT Ô GIẢI THƯỞNG';
        document.getElementById('form-item-id').value = item.id;
        document.getElementById('form-name').value = item.display_name;
        document.getElementById('form-reward-type').value = item.reward_type;
        document.getElementById('form-reward-val').value = item.reward_value;
        document.getElementById('form-template-id').value = item.item_template_id || '';
        document.getElementById('form-ratio').value = item.ratio;
        document.getElementById('form-color').value = item.color;
        document.getElementById('form-sort').value = item.sort_order;
        document.getElementById('form-active').value = item.is_active;
        onRewardTypeChange();
        document.getElementById('spin-item-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('spin-item-modal').classList.remove('show');
    }

    // Submit general settings
    document.getElementById('settings-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('settings-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG LƯU...';

        const formData = new FormData(document.getElementById('settings-form'));
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await window.csrfFetch('{{ route("admin.spin.settings") }}', {
                method: 'POST',
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'LƯU CÀI ĐẶT';
        }
    });

    // Submit spin item AJAX Form
    document.getElementById('spin-item-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        const itemId = document.getElementById('form-item-id').value;
        const formData = new FormData(document.getElementById('spin-item-form'));
        const data = Object.fromEntries(formData.entries());

        let url = '{{ route("admin.spin.items.store") }}';
        let method = 'POST';

        if (mode === 'edit') {
            url = `{{ url('admin/spin/items') }}/${itemId}`;
            method = 'PUT';
        }

        try {
            const response = await window.csrfFetch(url, {
                method: method,
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                closeModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                window.showAlert(result.message, 'error');
                btn.disabled = false;
                btn.innerText = 'XÁC NHẬN';
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
            btn.disabled = false;
            btn.innerText = 'XÁC NHẬN';
        }
    });

    // Delete item
    async function deleteSpinItem(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa ô giải thưởng này?')) {
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ url('admin/spin/items') }}/${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
        }
    }
</script>
@endsection
