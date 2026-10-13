@extends('admin.layout')

@section('title', 'Quản Lý Rương Đồ Web - Admin NRO BigK')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: var(--primary);">QUẢN LÝ RƯƠNG ĐỒ WEB</h2>
    <button class="btn" onclick="openSendModal()">GỬI QUÀ VÀO RƯƠNG WEB</button>
</div>

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 30px;">
    <form action="{{ route('admin.web_chest') }}" method="GET" style="display: flex; gap: 15px;">
        <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Tìm kiếm theo username tài khoản nhận..." style="flex: 1;">
        <button type="submit" class="btn">TÌM KIẾM</button>
        @if($search)
            <a href="{{ route('admin.web_chest') }}" class="btn btn-secondary">HỦY</a>
        @endif
    </form>
</div>

<!-- Web Chest Items Table -->
<div class="card" style="padding: 0;">
    @if($chestItems->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Hiện tại chưa có vật phẩm nào trong Rương đồ Web của người chơi.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người Nhận (Username)</th>
                    <th>ID Template</th>
                    <th>Tên Vật Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Chỉ Số (Stats)</th>
                    <th>Ngày Tạo</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chestItems as $item)
                    @php
                        $tpl = $item->template;
                        $opts = is_array($item->options) ? $item->options : (json_decode($item->options, true) ?? []);
                    @endphp
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $item->id }}</td>
                        <td style="font-weight: 700; color: #fff;">{{ $item->account->username ?? 'Không xác định' }}</td>
                        <td style="font-family: 'Orbitron', sans-serif; color: var(--primary);">{{ $item->item_template_id }}</td>
                        <td style="font-weight: 700; color: #fff;">
                            @if($tpl)
                                {{ $tpl->NAME }}
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Không xác định</span>
                            @endif
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: #00bcd4;">x{{ $item->quantity }}</td>
                        <td>
                            @if(count($opts) > 0)
                                @foreach($opts as $opt)
                                    @php
                                        $optTpl = DB::table('item_option_template')->where('id', $opt[0] ?? $opt['id'] ?? null)->first();
                                        $optName = $optTpl ? str_replace('#', $opt[1] ?? $opt['value'] ?? '', $optTpl->NAME) : 'Chỉ số ' . ($opt[0] ?? $opt['id'] ?? '') . ' (x' . ($opt[1] ?? $opt['value'] ?? '') . ')';
                                    @endphp
                                    <div style="font-size: 0.85rem; margin-bottom: 3px; display: flex; align-items: center; gap: 4px;">
                                        <span style="color: var(--secondary); font-weight: 700; font-family: 'Orbitron', sans-serif;">[{{ $opt[0] ?? $opt['id'] ?? '' }}]</span>
                                        <span style="color: #fff;">{{ $optName }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.85rem;">Mặc định</span>
                            @endif
                        </td>
                        <td style="font-size: 0.9rem; color: var(--text-muted);">{{ $item->created_at }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deleteChestItem({{ $item->id }}, '{{ $tpl->NAME ?? 'vật phẩm' }}', '{{ $item->account->username ?? '' }}')">
                                Xóa/Thu hồi
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $chestItems->links() }}
        </div>
    @endif
</div>

<!-- Modal Send Gift to Web Chest -->
<div id="send-modal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            GỬI VẬT PHẨM VÀO RƯƠNG ĐỒ WEB
        </h3>
        
        <form id="send-form">
            <!-- Account Username -->
            <div class="form-group">
                <label class="form-label">Tài khoản nhận (Username)</label>
                <input type="text" id="form-username" name="username" class="form-input" placeholder="Nhập tên tài khoản game..." required>
            </div>
            
            <!-- Template search -->
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

            <!-- Quantity -->
            <div class="form-group">
                <label class="form-label">Số lượng gửi</label>
                <input type="number" id="form-qty" name="quantity" class="form-input" min="1" value="1" placeholder="Nhập số lượng..." required>
            </div>

            <!-- Options Editor Section -->
            <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(255,107,0,0.02);">
                <label class="form-label" style="color: var(--secondary); margin-bottom: 8px; display: block;">Tra Cứu & Thêm Chỉ Số (Options/Stats)</label>
                <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                    <input type="text" id="search-option-query" class="form-input" style="padding: 8px 12px; font-size: 0.95rem;" placeholder="Nhập ID hoặc tên chỉ số (HP, Sức đánh)...">
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
                XÁC NHẬN GỬI VẬT PHẨM
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
            const response = await fetch(`{{ route('admin.shop.search-templates') }}?q=${encodeURIComponent(query)}`);
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
        const nameText = document.getElementById(`opt-name-text-${index}`);
        if (!idInput) return;
        const id = idInput.value.trim();
        if (id === '') {
            nameText.innerText = 'Nhập ID chỉ số';
            nameText.title = '';
            return;
        }
        try {
            const response = await fetch(`{{ route('admin.shop.search-options') }}?q=${id}`);
            const data = await response.json();
            const found = data.find(item => item.id == id);
            if (found) {
                nameText.innerText = found.NAME;
                nameText.title = found.NAME;
            } else {
                nameText.innerText = 'ID không hợp lệ';
                nameText.title = '';
            }
        } catch(e) {
            nameText.innerText = 'Lỗi tải...';
        }
    }

    // Modal control helper
    function openSendModal() {
        document.getElementById('send-form').reset();
        document.getElementById('options-container').innerHTML = '';
        document.getElementById('search-input').value = '';
        document.getElementById('template-select').innerHTML = '<option value="">-- Chọn vật phẩm từ kết quả tìm kiếm --</option>';
        document.getElementById('search-option-query').value = '';
        document.getElementById('search-options-results').style.display = 'none';
        optionRowCount = 0;
        
        document.getElementById('send-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('send-modal').classList.remove('show');
    }

    // Submit form handler
    document.getElementById('send-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        const payload = {
            username: document.getElementById('form-username').value.trim(),
            item_template_id: parseInt(document.getElementById('template-select').value),
            quantity: parseInt(document.getElementById('form-qty').value),
            options: []
        };

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
        
        try {
            const response = await window.csrfFetch('{{ route("admin.web_chest.send") }}', {
                method: 'POST',
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
                btn.innerText = 'XÁC NHẬN GỬI VẬT PHẨM';
            }
        } catch (error) {
            window.showAlert('Đã xảy ra lỗi kết nối.', 'error');
            btn.disabled = false;
            btn.innerText = 'XÁC NHẬN GỬI VẬT PHẨM';
        }
    });

    // Delete/Revoke item from web chest
    async function deleteChestItem(id, itemName, username) {
        if (!confirm(`Bạn có chắc chắn muốn XÓA / THU HỒI vật phẩm "${itemName}" khỏi Rương Web của tài khoản "${username}"?`)) {
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ url('admin/web-chest') }}/${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
        }
    }
</script>
@endsection
