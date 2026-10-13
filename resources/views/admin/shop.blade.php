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
                    <th>Chỉ Số (Stats)</th>
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
                        $opts = is_array($item->options) ? $item->options : (json_decode($item->options, true) ?? []);
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
                        <td>
                            @if(count($opts) > 0)
                                @foreach($opts as $opt)
                                    @php
                                        $optTpl = DB::table('item_option_template')->where('id', $opt[0])->first();
                                    @endphp
                                    <div style="font-size: 0.85rem; margin-bottom: 3px; display: flex; align-items: center; gap: 4px;">
                                        <span style="color: var(--secondary); font-weight: 700; font-family: 'Orbitron', sans-serif;">[{{ $opt[0] }}]</span>
                                        <span style="color: #fff;">{{ $optTpl ? str_replace('#', $opt[1], $optTpl->NAME) : 'Chỉ số ' . $opt[0] . ' (x' . $opt[1] . ')' }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.85rem;">Không có</span>
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
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="openEditModal({{ $item }})">
                                    Sửa
                                </button>
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deleteItem({{ $item->id }})">
                                    Xóa
                                </button>
                            </div>
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

            <!-- Options Editor Section -->
            <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(255,107,0,0.02);">
                <label class="form-label" style="color: var(--secondary); margin-bottom: 8px; display: block;">Tra Cứu & Thêm Chỉ Số (Options/Stats)</label>
                <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                    <input type="text" id="search-option-query" class="form-input" style="padding: 8px 12px; font-size: 0.95rem;" placeholder="Nhập ID hoặc tên chỉ số (Ví dụ: HP, Sức đánh)...">
                    <button type="button" class="btn" style="padding: 8px 16px; font-size: 0.9rem;" onclick="searchOptionTemplates()">TÌM Chỉ Số</button>
                </div>
                <!-- Search results list for options -->
                <div id="search-options-results" style="max-height: 150px; overflow-y: auto; background: rgba(0,0,0,0.4); border-radius: 6px; padding: 5px; display: none; border: 1px solid rgba(255,107,0,0.15); margin-bottom: 10px;">
                </div>

                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 5px; margin-top: 10px;">
                    Danh sách chỉ số đã gán
                    <button type="button" class="btn" style="padding: 4px 10px; font-size: 0.8rem;" onclick="addEmptyOptionRow()">+ Thêm chỉ số trống</button>
                </label>
                
                <div id="options-container" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                    <!-- Option rows will be injected here -->
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
    let mode = 'add'; // 'add' or 'edit'
    let optionRowCount = 0;

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

    // Option template query search AJAX
    async function searchOptionTemplates() {
        const query = document.getElementById('search-option-query').value.trim();
        const resultsEl = document.getElementById('search-options-results');
        
        if (query === '') {
            window.showAlert('Vui lòng nhập từ khóa tìm kiếm chỉ số.', 'error');
            return;
        }

        try {
            const response = await fetch(`{{ route('admin.shop.search-options') }}?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            resultsEl.innerHTML = '';
            if (data.length === 0) {
                resultsEl.innerHTML = '<p style="padding: 8px; color: var(--text-muted); font-size: 0.9rem; text-align: center;">Không tìm thấy chỉ số nào.</p>';
                resultsEl.style.display = 'block';
                return;
            }

            data.forEach(opt => {
                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.justify = 'space-between';
                row.style.alignItems = 'center';
                row.style.padding = '6px 10px';
                row.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
                row.style.cursor = 'pointer';
                row.style.transition = 'background 0.2s';
                row.style.fontSize = '0.9rem';
                
                row.innerHTML = `
                    <div style="color: #fff; font-weight: 500;">
                        <span style="color: var(--primary); font-weight: 700; font-family: 'Orbitron', sans-serif;">ID ${opt.id}</span> - ${opt.NAME}
                    </div>
                    <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem;" onclick="addSearchedOption(${opt.id}, '${escapeHtml(opt.NAME)}')">Chọn</button>
                `;
                resultsEl.appendChild(row);
            });
            resultsEl.style.display = 'block';
        } catch (error) {
            window.showAlert('Lỗi tải danh sách chỉ số.', 'error');
        }
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function addSearchedOption(id, name) {
        addOptionRow(id, 1, name);
    }

    // Add option row helper inside modal Form
    function addOptionRow(optId = '', value = 1, optName = '') {
        const container = document.getElementById('options-container');
        const index = optionRowCount;
        
        const row = document.createElement('div');
        row.id = `option-row-${index}`;
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1fr 2fr 1fr auto';
        row.style.gap = '10px';
        row.style.alignItems = 'center';
        row.style.background = 'rgba(255,255,255,0.02)';
        row.style.padding = '8px';
        row.style.borderRadius = '6px';
        row.style.border = '1px solid rgba(255,107,0,0.1)';
        
        row.innerHTML = `
            <div>
                <input type="number" name="options[${index}][id]" class="form-input" style="padding: 8px 10px; font-size: 0.95rem;" value="${optId}" placeholder="ID..." required oninput="updateOptionName(${index})">
            </div>
            <div id="opt-name-text-${index}" style="color: #fff; font-size: 0.88rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${optName}">
                ${optName || (optId ? 'Đang tải...' : 'Nhập ID chỉ số')}
            </div>
            <div>
                <input type="number" name="options[${index}][value]" class="form-input" style="padding: 8px 10px; font-size: 0.95rem;" value="${value}" placeholder="Giá trị..." required>
            </div>
            <button type="button" class="btn btn-danger" style="padding: 8px 10px;" onclick="removeOptionRow(${index})">Xóa</button>
        `;
        
        container.appendChild(row);
        optionRowCount++;
        
        if (optId && !optName) {
            updateOptionName(index);
        }
    }

    function addEmptyOptionRow() {
        addOptionRow('', 1, '');
    }

    function removeOptionRow(index) {
        const row = document.getElementById(`option-row-${index}`);
        if (row) {
            row.remove();
        }
    }

    async function updateOptionName(index) {
        const idInput = document.querySelector(`[name="options[${index}][id]"]`);
        const textEl = document.getElementById(`opt-name-text-${index}`);
        if (!idInput || !textEl) return;
        
        const val = idInput.value.trim();
        if (val === '') {
            textEl.innerText = 'Nhập ID chỉ số';
            textEl.style.color = '#fff';
            return;
        }

        try {
            const response = await fetch(`{{ route('admin.shop.search-options') }}?q=${encodeURIComponent(val)}`);
            const data = await response.json();
            const match = data.find(item => item.id == val);
            if (match) {
                textEl.innerText = match.NAME;
                textEl.title = match.NAME;
                textEl.style.color = 'var(--secondary)';
            } else {
                textEl.innerText = 'Không tìm thấy ID';
                textEl.style.color = '#ff1744';
            }
        } catch (e) {
            textEl.innerText = 'Lỗi kết nối';
            textEl.style.color = '#ff1744';
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
        
        document.getElementById('options-container').innerHTML = '';
        document.getElementById('search-option-query').value = '';
        document.getElementById('search-options-results').style.display = 'none';
        optionRowCount = 0;
        
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
        
        document.getElementById('options-container').innerHTML = '';
        document.getElementById('search-option-query').value = '';
        document.getElementById('search-options-results').style.display = 'none';
        optionRowCount = 0;

        // Populate options
        const opts = item.options || [];
        if (opts.length > 0) {
            opts.forEach(opt => {
                addOptionRow(opt[0], opt[1], '');
            });
        }
        
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
        
        const payload = {
            price_vnd: parseInt(document.getElementById('form-price').value),
            quantity: parseInt(document.getElementById('form-qty').value),
            image_override: document.getElementById('form-img').value,
            is_active: parseInt(document.getElementById('form-active').value),
            options: []
        };

        if (mode === 'add') {
            payload.item_template_id = parseInt(document.getElementById('template-select').value);
        }

        // Parse options inputs dynamically
        const optContainer = document.getElementById('options-container');
        const optRows = optContainer.children;
        for (let row of optRows) {
            const index = row.id.split('-').pop();
            const idInput = document.querySelector(`[name="options[${index}][id]"]`);
            const valInput = document.querySelector(`[name="options[${index}][value]"]`);
            if (idInput && valInput && idInput.value.trim() !== '') {
                payload.options.push({
                    id: parseInt(idInput.value),
                    value: parseInt(valInput.value)
                });
            }
        }
        
        let url = '{{ route("admin.shop.store") }}';
        let method = 'POST';

        if (mode === 'edit') {
            url = `{{ url('admin/shop') }}/${itemId}`;
            method = 'PUT';
        }

        try {
            const response = await window.csrfFetch(url, {
                method: method,
                body: JSON.stringify(payload)
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
