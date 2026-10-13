@extends('admin.layout')

@section('title', 'Quản Lý Gift Codes - Admin NRO BigK')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: var(--primary);">QUẢN LÝ GIFT CODES</h2>
    <button class="btn" onclick="openAddModal()">THÊM GIFT CODE</button>
</div>

<!-- Gift Codes List -->
<div class="card" style="padding: 0;">
    @if($giftcodes->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa có mã Gift Code nào được tạo.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>Mã Code</th>
                    <th>Yêu Cầu Sức Mạnh</th>
                    <th>Lượt Đã Dùng</th>
                    <th>Danh Sách Vật Phẩm Quà (ID + Tên)</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($giftcodes as $code)
                    @php
                        $itemIds = is_array($code->item_id) ? $code->item_id : (json_decode($code->item_id, true) ?? []);
                        $quantities = is_array($code->item_quantity) ? $code->item_quantity : (json_decode($code->item_quantity, true) ?? []);
                        $itemOptions = is_array($code->item_options) ? $code->item_options : (json_decode($code->item_options, true) ?? []);
                        $listUser = is_array($code->list_user) ? $code->list_user : (json_decode($code->list_user, true) ?? []);
                    @endphp
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary); font-size: 1.1rem;">
                            {{ $code->name }}
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: #fff;">
                            {{ number_format($code->power) }}
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; color: var(--text-muted);">
                            {{ count($listUser) }} tài khoản
                        </td>
                        <td style="font-size: 0.95rem;">
                            @if(count($itemIds) > 0)
                                @foreach($itemIds as $index => $itemId)
                                    @php
                                        $tpl = DB::table('item_template')->where('id', $itemId)->first();
                                        $qty = $quantities[$index] ?? 1;
                                        $opts = $itemOptions[$index] ?? [];
                                    @endphp
                                    <div style="display: flex; flex-direction: column; margin-bottom: 8px; background: rgba(255,255,255,0.01); padding: 6px; border-radius: 4px; border: 1px solid rgba(255,107,0,0.05);">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 1.2rem; cursor: help;" title="Icon ID: {{ $tpl->icon_id ?? '?' }}">📦</span>
                                            <span style="font-weight: 700; color: var(--primary); font-family: 'Orbitron', sans-serif;">ID {{ $itemId }}</span>
                                            <span style="color: #fff; font-weight: 500;">{{ $tpl ? $tpl->NAME : 'Không xác định' }}</span>
                                            <span style="color: var(--text-muted); font-size: 0.9rem;">(SL: {{ $qty }})</span>
                                        </div>
                                        @if(count($opts) > 0)
                                            <div style="margin-left: 28px; margin-top: 4px; display: flex; flex-direction: column; gap: 2px;">
                                                @foreach($opts as $opt)
                                                    @php
                                                        $optTpl = DB::table('item_option_template')->where('id', $opt[0])->first();
                                                    @endphp
                                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                        <span style="color: var(--secondary); font-weight: 700;">[{{ $opt[0] }}]</span>
                                                        <span style="color: #ddd;">{{ $optTpl ? str_replace('#', $opt[1], $optTpl->NAME) : 'Chỉ số ' . $opt[0] . ' (' . $opt[1] . ')' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Không có vật phẩm</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; margin-right: 5px;" onclick="openEditModal({{ json_encode($code) }})">
                                Sửa
                            </button>
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deleteGiftcode({{ $code->id }})">
                                Xóa
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $giftcodes->links() }}
        </div>
    @endif
</div>

<!-- Modal Add/Edit Gift Code -->
<div id="giftcode-modal" class="modal">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3 id="modal-title" style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            TẠO GIFT CODE MỚI
        </h3>
        
        <form id="giftcode-form">
            <input type="hidden" id="form-giftcode-id" name="id">
            
            <!-- Code name -->
            <div class="form-group">
                <label class="form-label">Mã Code (viết liền không dấu)</label>
                <input type="text" id="form-name" name="name" class="form-input" placeholder="VD: tanthubigk, giftcode2026..." required>
            </div>

            <!-- Power limit -->
            <div class="form-group">
                <label class="form-label">Yêu cầu Sức Mạnh tối thiểu của nhân vật</label>
                <input type="number" id="form-power" name="power" value="0" class="form-input" min="0" required>
            </div>

            <!-- Tra Cứu & Chọn Nhanh Item Panel -->
            <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(255,107,0,0.02);">
                <label class="form-label" style="color: var(--secondary); margin-bottom: 8px; display: block;">Tìm kiếm & Thêm Nhanh Vật Phẩm</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="search-item-query" class="form-input" style="padding: 8px 12px; font-size: 0.95rem;" placeholder="Nhập ID hoặc tên vật phẩm...">
                    <button type="button" class="btn" style="padding: 8px 16px; font-size: 0.9rem;" onclick="searchItemTemplates()">TÌM</button>
                </div>
                <!-- Search Results List -->
                <div id="search-results-list" style="margin-top: 10px; max-height: 150px; overflow-y: auto; background: rgba(0,0,0,0.4); border-radius: 6px; padding: 5px; display: none; border: 1px solid rgba(255,107,0,0.15);">
                    <!-- Clicking a row appends it to rewards -->
                </div>
            </div>

            <!-- Tra Cứu & Chọn Nhanh Option Panel -->
            <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(255,107,0,0.02);">
                <label class="form-label" style="color: var(--secondary); margin-bottom: 8px; display: block;">Tra Cứu Chỉ Số Vật Phẩm (Options/Stats)</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="search-option-query" class="form-input" style="padding: 8px 12px; font-size: 0.95rem;" placeholder="Nhập tên hoặc ID chỉ số (VD: HP, Sức đánh)...">
                    <button type="button" class="btn" style="padding: 8px 16px; font-size: 0.9rem;" onclick="searchOptionTemplates()">TÌM Chỉ Số</button>
                </div>
                <!-- Search results list for options -->
                <div id="search-options-results" style="margin-top: 10px; max-height: 150px; overflow-y: auto; background: rgba(0,0,0,0.4); border-radius: 6px; padding: 5px; display: none; border: 1px solid rgba(255,107,0,0.15);">
                </div>
            </div>

            <!-- Rewards Section -->
            <div style="margin-bottom: 20px;">
                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 5px;">
                    Vật phẩm trong code
                    <button type="button" class="btn" style="padding: 4px 10px; font-size: 0.8rem;" onclick="addNewEmptyRow()">+ Thêm dòng trống</button>
                </label>
                
                <div id="rewards-container" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                    <!-- Rows will be injected here -->
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
    let rewardRowCount = 0;
    let itemOptionCounts = {}; // tracks option rows per item row index

    // Search item templates AJAX
    async function searchItemTemplates() {
        const query = document.getElementById('search-item-query').value.trim();
        const resultsEl = document.getElementById('search-results-list');
        
        if (query === '') {
            window.showAlert('Vui lòng nhập từ khóa tìm kiếm.', 'error');
            return;
        }

        try {
            const response = await fetch(`{{ route('admin.shop.search-templates') }}?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            resultsEl.innerHTML = '';
            if (data.length === 0) {
                resultsEl.innerHTML = '<p style="padding: 8px; color: var(--text-muted); font-size: 0.9rem; text-align: center;">Không tìm thấy vật phẩm nào.</p>';
                resultsEl.style.display = 'block';
                return;
            }

            data.forEach(item => {
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
                        <span style="color: var(--primary); font-weight: 700; font-family: 'Orbitron', sans-serif;">ID ${item.id}</span> - ${item.NAME}
                    </div>
                    <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem;" onclick="addSearchedItem(${item.id}, '${escapeHtml(item.NAME)}')">Chọn</button>
                `;
                resultsEl.appendChild(row);
            });
            resultsEl.style.display = 'block';
        } catch (error) {
            window.showAlert('Lỗi tải danh sách mẫu.', 'error');
        }
    }

    // Search option templates AJAX
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
                    <span style="color: var(--secondary); font-size: 0.8rem; font-style: italic;">Sử dụng ID này</span>
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

    function addSearchedItem(id, name) {
        addRewardRow(id, 1, name);
    }

    // Add row helper inside modal Form
    function addRewardRow(itemId = '', quantity = 1, itemName = '') {
        const container = document.getElementById('rewards-container');
        const index = rewardRowCount;
        
        const row = document.createElement('div');
        row.id = `reward-row-${index}`;
        row.style.background = 'rgba(255,255,255,0.02)';
        row.style.padding = '12px 10px';
        row.style.borderRadius = '6px';
        row.style.border = '1px solid rgba(255,107,0,0.1)';
        row.style.marginBottom = '5px';
        
        row.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 2fr 1fr auto; gap: 10px; align-items: center;">
                <div>
                    <input type="number" name="items[${index}][id]" class="form-input" style="padding: 8px 10px; font-size: 0.95rem;" value="${itemId}" placeholder="ID..." required oninput="updateItemName(${index})">
                </div>
                <div id="item-name-text-${index}" style="color: #fff; font-size: 0.88rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${itemName}">
                    ${itemName || (itemId ? 'Đang tải...' : 'Nhập ID vật phẩm')}
                </div>
                <div>
                    <input type="number" name="items[${index}][quantity]" class="form-input" style="padding: 8px 10px; font-size: 0.95rem;" value="${quantity}" min="1" placeholder="SL..." required>
                </div>
                <button type="button" class="btn btn-danger" style="padding: 8px 10px;" onclick="removeRewardRow(${index})">Xóa</button>
            </div>
            
            <!-- Item Options Editor -->
            <div style="margin-top: 10px; padding-left: 15px; border-left: 2px solid var(--primary); background: rgba(255,255,255,0.01); padding-top: 5px; padding-bottom: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--secondary);">Chỉ số vật phẩm (Options)</span>
                    <button type="button" class="btn" style="padding: 2px 6px; font-size: 0.75rem;" onclick="addOptionToItem(${index})">+ Thêm chỉ số</button>
                </div>
                <div id="item-${index}-options-container" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Option rows for this specific item -->
                </div>
            </div>
        `;
        
        container.appendChild(row);
        rewardRowCount++;
        
        if (itemId && !itemName) {
            updateItemName(index);
        }
    }

    // Add nested option row inside an item row
    function addOptionToItem(itemIndex, optId = '', optValue = 1) {
        if (!itemOptionCounts[itemIndex]) {
            itemOptionCounts[itemIndex] = 0;
        }
        const optIndex = itemOptionCounts[itemIndex];
        const container = document.getElementById(`item-${itemIndex}-options-container`);
        if (!container) return;

        const row = document.createElement('div');
        row.id = `item-${itemIndex}-option-row-${optIndex}`;
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1.2fr 2.5fr 1.2fr auto';
        row.style.gap = '8px';
        row.style.alignItems = 'center';
        row.style.background = 'rgba(255,255,255,0.01)';
        row.style.padding = '4px 8px';
        row.style.borderRadius = '4px';
        row.style.border = '1px dashed rgba(255,255,255,0.1)';
        
        row.innerHTML = `
            <div>
                <input type="number" name="items[${itemIndex}][options][${optIndex}][id]" class="form-input" style="padding: 6px 8px; font-size: 0.85rem;" value="${optId}" placeholder="ID..." required oninput="updateItemOptionName(${itemIndex}, ${optIndex})">
            </div>
            <div id="item-${itemIndex}-opt-name-text-${optIndex}" style="color: #ccc; font-size: 0.82rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Chỉ số ID
            </div>
            <div>
                <input type="number" name="items[${itemIndex}][options][${optIndex}][value]" class="form-input" style="padding: 6px 8px; font-size: 0.85rem;" value="${optValue}" placeholder="Giá trị..." required>
            </div>
            <button type="button" class="btn btn-danger" style="padding: 6px 8px; font-size: 0.8rem;" onclick="removeItemOptionRow(${itemIndex}, ${optIndex})">Xóa</button>
        `;
        
        container.appendChild(row);
        itemOptionCounts[itemIndex]++;
        
        if (optId) {
            updateItemOptionName(itemIndex, optIndex);
        }
    }

    function removeItemOptionRow(itemIndex, optIndex) {
        const row = document.getElementById(`item-${itemIndex}-option-row-${optIndex}`);
        if (row) {
            row.remove();
        }
    }

    async function updateItemOptionName(itemIndex, optIndex) {
        const idInput = document.querySelector(`[name="items[${itemIndex}][options][${optIndex}][id]"]`);
        const textEl = document.getElementById(`item-${itemIndex}-opt-name-text-${optIndex}`);
        if (!idInput || !textEl) return;
        
        const val = idInput.value.trim();
        if (val === '') {
            textEl.innerText = 'Chỉ số ID';
            textEl.style.color = '#ccc';
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
                textEl.innerText = 'Không thấy ID';
                textEl.style.color = '#ff1744';
            }
        } catch (e) {
            textEl.innerText = 'Lỗi kết nối';
            textEl.style.color = '#ff1744';
        }
    }

    function addNewEmptyRow() {
        addRewardRow('', 1, '');
    }

    async function updateItemName(index) {
        const idInput = document.querySelector(`[name="items[${index}][id]"]`);
        const textEl = document.getElementById(`item-name-text-${index}`);
        if (!idInput || !textEl) return;
        
        const val = idInput.value.trim();
        if (val === '') {
            textEl.innerText = 'Nhập ID vật phẩm';
            textEl.style.color = '#fff';
            return;
        }

        try {
            const response = await fetch(`{{ route('admin.shop.search-templates') }}?q=${encodeURIComponent(val)}`);
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

    function removeRewardRow(index) {
        const row = document.getElementById(`reward-row-${index}`);
        if (row) {
            row.remove();
        }
    }

    // Modal control helpers
    function openAddModal() {
        mode = 'add';
        document.getElementById('modal-title').innerText = 'TẠO GIFT CODE MỚI';
        document.getElementById('form-giftcode-id').value = '';
        document.getElementById('form-name').value = '';
        document.getElementById('form-power').value = '0';
        document.getElementById('rewards-container').innerHTML = '';
        document.getElementById('search-item-query').value = '';
        document.getElementById('search-results-list').style.display = 'none';
        document.getElementById('search-option-query').value = '';
        document.getElementById('search-options-results').style.display = 'none';
        
        rewardRowCount = 0;
        itemOptionCounts = {};
        addRewardRow(); // start with one blank item row
        document.getElementById('giftcode-modal').classList.add('show');
    }

    function openEditModal(code) {
        mode = 'edit';
        document.getElementById('modal-title').innerText = 'CẬP NHẬT GIFT CODE';
        document.getElementById('form-giftcode-id').value = code.id;
        document.getElementById('form-name').value = code.name;
        document.getElementById('form-power').value = code.power;
        document.getElementById('rewards-container').innerHTML = '';
        document.getElementById('search-item-query').value = '';
        document.getElementById('search-results-list').style.display = 'none';
        document.getElementById('search-option-query').value = '';
        document.getElementById('search-options-results').style.display = 'none';
        
        rewardRowCount = 0;
        itemOptionCounts = {};
        
        // Populate existing items
        const itemIds = code.item_id || [];
        const quantities = code.item_quantity || [];
        const itemOptions = code.item_options || [];
        
        if (itemIds.length > 0) {
            itemIds.forEach((itemId, idx) => {
                const qty = quantities[idx] || 1;
                const opts = itemOptions[idx] || [];
                
                // Add reward row
                addRewardRow(itemId, qty, '');
                
                // Populate options for this item row (idx is the rewardRowCount - 1)
                const currentItemIndex = rewardRowCount - 1;
                opts.forEach(opt => {
                    addOptionToItem(currentItemIndex, opt[0], opt[1]);
                });
            });
        } else {
            addRewardRow();
        }
        
        document.getElementById('giftcode-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('giftcode-modal').classList.remove('show');
    }

    // Submit Gift Code AJAX Form
    document.getElementById('giftcode-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Ensure at least one reward item is added
        const container = document.getElementById('rewards-container');
        if (container.children.length === 0) {
            window.showAlert('Vui lòng thêm ít nhất một vật phẩm quà.', 'error');
            return;
        }

        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        const codeId = document.getElementById('form-giftcode-id').value;
        
        const payload = {
            name: document.getElementById('form-name').value,
            power: parseInt(document.getElementById('form-power').value),
            items: []
        };

        // Parse items inputs dynamically
        const rows = container.children;
        for (let row of rows) {
            const index = row.id.split('-').pop(); // itemIndex
            const idInput = document.querySelector(`[name="items[${index}][id]"]`);
            const qtyInput = document.querySelector(`[name="items[${index}][quantity]"]`);
            if (idInput && qtyInput && idInput.value.trim() !== '') {
                const itemData = {
                    id: parseInt(idInput.value),
                    quantity: parseInt(qtyInput.value),
                    options: []
                };
                
                // Parse nested options for this item
                const optContainer = document.getElementById(`item-${index}-options-container`);
                if (optContainer) {
                    const optRows = optContainer.children;
                    for (let optRow of optRows) {
                        const optIndex = optRow.id.split('-').pop();
                        const optIdInput = document.querySelector(`[name="items[${index}][options][${optIndex}][id]"]`);
                        const optValInput = document.querySelector(`[name="items[${index}][options][${optIndex}][value]"]`);
                        if (optIdInput && optValInput && optIdInput.value.trim() !== '') {
                            itemData.options.push({
                                id: parseInt(optIdInput.value),
                                value: parseInt(optValInput.value)
                            });
                        }
                    }
                }
                
                payload.items.push(itemData);
            }
        }

        let url = '{{ route("admin.giftcodes.store") }}';
        let method = 'POST';

        if (mode === 'edit') {
            url = `{{ url('admin/giftcodes') }}/${codeId}`;
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

    // Delete Code
    async function deleteGiftcode(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa Gift Code này?')) {
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ url('admin/giftcodes') }}/${id}`, {
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
