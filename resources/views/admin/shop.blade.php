@extends('admin.layout')

@section('title', 'Quản Lý Cửa Hàng - Admin NRO BigK')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: var(--primary);">QUẢN LÝ CỬA HÀNG</h2>
    <button class="btn" onclick="openAddModal()">THÊM VẬT PHẨM</button>
</div>

<!-- Shop Items List -->
<div class="card" style="padding: 0;">
    @if($shopItems->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Cửa hàng chưa có vật phẩm nào.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID Shop</th>
                    <th>ID Item</th>
                    <th style="text-align: center;">Ảnh/Icon</th>
                    <th>Tên Vật Phẩm</th>
                    <th>Giá VND</th>
                    <th>Số Lượng</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shopItems as $item)
                    @php
                        $tpl = $item->template;
                    @endphp
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $item->id }}</td>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--primary);">{{ $item->item_template_id }}</td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if($item->image_override)
                                <img src="{{ $item->image_override }}" style="max-height: 40px; max-width: 40px; border-radius: 4px; object-fit: contain; border: 1px solid var(--border-color); padding: 2px; background: rgba(255,255,255,0.05);">
                            @else
                                <span style="font-size: 1.5rem;" title="Icon ID: {{ $tpl->icon_id ?? '?' }}">📦</span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: #fff;">
                            @if($tpl)
                                {{ $tpl->NAME }}
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Không xác định</span>
                            @endif
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary);">
                            {{ number_format($item->price_vnd) }}đ
                        </td>
                        <td>
                            @if($item->quantity === -1)
                                <span style="color: #4caf50; font-weight: 700;">Vô hạn</span>
                            @else
                                <span style="font-family: 'Orbitron', sans-serif;">{{ $item->quantity }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active == 1)
                                <span class="badge badge-success">ĐANG BÁN</span>
                            @else
                                <span class="badge badge-danger">TẠM ĐÓNG</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="openEditModal({{ $item }})">
                                Sửa
                            </button>
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deleteItem({{ $item->id }})">
                                Xóa
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $shopItems->links() }}
        </div>
    @endif
</div>

<!-- Modal Add/Edit Shop Item -->
<div id="shop-modal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3 id="modal-title" style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            THÊM VẬT PHẨM VÀO SHOP
        </h3>
        
        <form id="shop-form">
            <input type="hidden" id="form-item-id" name="id">
            
            <!-- Template search (only visible on ADD mode) -->
            <div class="form-group" id="search-section">
                <label class="form-label">Tìm kiếm vật phẩm game</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="search-input" class="form-input" placeholder="Nhập tên vật phẩm game hoặc ID template...">
                    <button type="button" class="btn" style="padding: 10px 18px;" onclick="searchItemTemplates()">TÌM</button>
                </div>
                <select id="template-select" name="item_template_id" class="form-input" style="margin-top: 10px;" required>
                    <option value="">-- Chọn vật phẩm từ kết quả tìm kiếm --</option>
                </select>
            </div>

            <!-- Price input -->
            <div class="form-group">
                <label class="form-label">Giá bán VND</label>
                <input type="number" id="form-price" name="price_vnd" class="form-input" min="0" placeholder="Nhập giá bằng VND..." required>
            </div>

            <!-- Stock quantity -->
            <div class="form-group">
                <label class="form-label">Số lượng kho (-1 = Vô hạn)</label>
                <input type="number" id="form-qty" name="quantity" class="form-input" placeholder="Nhập số lượng..." required>
            </div>

            <!-- Custom image url -->
            <div class="form-group">
                <label class="form-label">Ảnh custom (Không bắt buộc)</label>
                <input type="text" id="form-img" name="image_override" class="form-input" placeholder="Nhập đường dẫn URL ảnh...">
            </div>

            <!-- Active flag -->
            <div class="form-group">
                <label class="form-label">Trạng thái hoạt động</label>
                <select id="form-active" name="is_active" class="form-input" required>
                    <option value="1">Đang bán</option>
                    <option value="0">Tạm đóng</option>
                </select>
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
    let mode = 'add'; // 'add' or 'edit'

    // Template query search AJAX
    async function searchItemTemplates() {
        const query = document.getElementById('search-input').value;
        const select = document.getElementById('template-select');
        
        if (query.trim() === '') {
            window.showAlert('Vui lòng nhập từ khóa tìm kiếm.', 'error');
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ route('admin.shop.search-templates') }}?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Chọn vật phẩm từ kết quả --</option>';
            if (data.length === 0) {
                window.showAlert('Không tìm thấy vật phẩm nào khớp.', 'error');
                return;
            }

            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.innerText = `[ID Template: ${item.id}] ${item.NAME} (Loại: ${item.TYPE})`;
                select.appendChild(opt);
            });
        } catch (error) {
            window.showAlert('Lỗi tải danh sách mẫu.', 'error');
        }
    }

    // Modal control helper
    function openAddModal() {
        mode = 'add';
        document.getElementById('modal-title').innerText = 'THÊM VẬT PHẨM WEB SHOP';
        document.getElementById('form-item-id').value = '';
        document.getElementById('shop-form').reset();
        document.getElementById('search-section').style.display = 'block';
        document.getElementById('template-select').required = true;
        document.getElementById('shop-modal').classList.add('show');
    }

    function openEditModal(item) {
        mode = 'edit';
        document.getElementById('modal-title').innerText = 'CẬP NHẬT VẬT PHẨM SHOP';
        document.getElementById('form-item-id').value = item.id;
        document.getElementById('form-price').value = item.price_vnd;
        document.getElementById('form-qty').value = item.quantity;
        document.getElementById('form-img').value = item.image_override || '';
        document.getElementById('form-active').value = item.is_active;
        
        // Hide search section during edits
        document.getElementById('search-section').style.display = 'none';
        document.getElementById('template-select').required = false;
        
        document.getElementById('shop-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('shop-modal').classList.remove('show');
    }

    // Submit form handler
    document.getElementById('shop-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        const itemId = document.getElementById('form-item-id').value;
        const formData = new FormData(document.getElementById('shop-form'));
        
        let url = '{{ route("admin.shop.store") }}';
        let method = 'POST';

        if (mode === 'edit') {
            url = `{{ url('admin/shop') }}/${itemId}`;
            method = 'PUT';
        }

        const data = Object.fromEntries(formData.entries());
        if (mode === 'edit') {
            delete data.item_template_id; // omit template on edit
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
            window.showAlert('Đã xảy ra lỗi kết nối.', 'error');
            btn.disabled = false;
            btn.innerText = 'XÁC NHẬN';
        }
    });

    // Delete Shop Item
    async function deleteItem(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa vật phẩm này khỏi Web Shop?')) {
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ url('admin/shop') }}/${id}`, {
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
