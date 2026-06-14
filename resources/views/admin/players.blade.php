@extends('admin.layout')

@section('title', 'Quản Lý Thành Viên - Admin NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 30px;">
    <h2 style="color: var(--primary); margin-bottom: 15px;">QUẢN LÝ THÀNH VIÊN</h2>
    
    <!-- Search Form -->
    <form action="{{ route('admin.players') }}" method="GET" style="display: flex; gap: 15px;">
        <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Tìm theo username tài khoản hoặc tên nhân vật game..." style="flex: 1;">
        <button type="submit" class="btn">TÌM KIẾM</button>
        @if($search)
            <a href="{{ route('admin.players') }}" class="btn btn-secondary">HỦY</a>
        @endif
    </form>
</div>

<!-- Players List -->
<div class="card" style="padding: 0;">
    @if($accounts->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Không tìm thấy người chơi nào khớp với từ khóa.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tài Khoản</th>
                    <th>Nhân Vật Game</th>
                    <th>Số Dư VND</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $acc)
                    @php
                        $player = $acc->players->first();
                    @endphp
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $acc->id }}</td>
                        <td style="font-weight: 700; color: #fff;">{{ $acc->username }}</td>
                        <td>
                            @if($player)
                                <span style="color: var(--primary); font-weight: 700;">{{ $player->name }}</span>
                                <span style="color: var(--text-muted); font-size: 0.9rem;">(SM: {{ number_format($player->power) }})</span>
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Chưa tạo NV</span>
                            @endif
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary);" id="vnd-display-{{ $acc->id }}">
                            {{ number_format($acc->vnd ?? 0) }}đ
                        </td>
                        <td>
                            @if($acc->ban > 0)
                                <span class="badge badge-danger" id="status-badge-{{ $acc->id }}">ĐÃ KHÓA</span>
                            @else
                                <span class="badge badge-success" id="status-badge-{{ $acc->id }}">HOẠT ĐỘNG</span>
                            @endif
                            
                            @if($acc->is_admin == 1)
                                <span class="badge badge-primary">ADMIN</span>
                            @endif
                        </td>
                        <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                            <!-- Add Money -->
                            <button class="btn" style="padding: 6px 12px; font-size: 0.85rem; background: #4caf50;" onclick="openAddVndModal({{ $acc->id }}, '{{ $acc->username }}')">
                                + VND
                            </button>

                            <!-- Role Toggle -->
                            @if($acc->is_admin == 1)
                                <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="toggleAdmin({{ $acc->id }}, 'remove')">
                                    Hủy Admin
                                </button>
                            @else
                                <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="toggleAdmin({{ $acc->id }}, 'set')">
                                    Làm Admin
                                </button>
                            @endif

                            <!-- Ban Toggle -->
                            @if($acc->ban > 0)
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem; background: #4caf50;" onclick="toggleBan({{ $acc->id }}, 'unban')" id="ban-btn-{{ $acc->id }}">
                                    Mở Khóa
                                </button>
                            @else
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="toggleBan({{ $acc->id }}, 'ban')" id="ban-btn-{{ $acc->id }}">
                                    Khóa TK
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $accounts->links() }}
        </div>
    @endif
</div>

<!-- Add VND Modal -->
<div id="add-vnd-modal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeAddVndModal()">&times;</button>
        <h3 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            CỘNG TIỀN VND CHO <span id="modal-username" style="color: #fff;"></span>
        </h3>
        
        <form id="add-vnd-form">
            <input type="hidden" id="modal-account-id" name="account_id">
            <div class="form-group">
                <label class="form-label">Số tiền cần cộng (VND)</label>
                <input type="number" id="modal-amount" name="amount" class="form-input" min="1" placeholder="Nhập số tiền VND..." required>
            </div>
            
            <button type="submit" id="modal-submit-btn" class="btn" style="width: 100%;">
                XÁC NHẬN CỘNG TIỀN
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Ban/Unban Trigger AJAX
    async function toggleBan(accountId, action) {
        const url = action === 'ban' 
            ? `{{ url('admin/players') }}/${accountId}/ban` 
            : `{{ url('admin/players') }}/${accountId}/unban`;
            
        if (!confirm(`Bạn có chắc chắn muốn ${action === 'ban' ? 'KHÓA' : 'MỞ KHÓA'} tài khoản này?`)) {
            return;
        }

        try {
            const response = await window.csrfFetch(url, { method: 'POST' });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                const badge = document.getElementById(`status-badge-${accountId}`);
                const btn = document.getElementById(`ban-btn-${accountId}`);
                
                if (action === 'ban') {
                    badge.className = 'badge badge-danger';
                    badge.innerText = 'ĐÃ KHÓA';
                    btn.className = 'btn btn-danger';
                    btn.innerText = 'Mở Khóa';
                    btn.style.background = '#4caf50';
                    btn.setAttribute('onclick', `toggleBan(${accountId}, 'unban')`);
                } else {
                    badge.className = 'badge badge-success';
                    badge.innerText = 'HOẠT ĐỘNG';
                    btn.className = 'btn btn-danger';
                    btn.innerText = 'Khóa TK';
                    btn.style.background = '';
                    btn.setAttribute('onclick', `toggleBan(${accountId}, 'ban')`);
                }
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Đã xảy ra lỗi kết nối.', 'error');
        }
    }

    // Role Promotion Trigger AJAX
    async function toggleAdmin(accountId, action) {
        const url = action === 'set' 
            ? `{{ url('admin/players') }}/${accountId}/set-admin` 
            : `{{ url('admin/players') }}/${accountId}/remove-admin`;
            
        if (!confirm(`Bạn có chắc muốn ${action === 'set' ? 'cấp quyền ADMIN' : 'gỡ quyền ADMIN'} cho tài khoản này?`)) {
            return;
        }

        try {
            const response = await window.csrfFetch(url, { method: 'POST' });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Đã xảy ra lỗi.', 'error');
        }
    }

    // Modal helpers for adding VND
    function openAddVndModal(id, username) {
        document.getElementById('modal-account-id').value = id;
        document.getElementById('modal-username').innerText = username.toUpperCase();
        document.getElementById('add-vnd-modal').classList.add('show');
    }

    function closeAddVndModal() {
        document.getElementById('add-vnd-modal').classList.remove('show');
        document.getElementById('add-vnd-form').reset();
    }

    document.getElementById('add-vnd-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const accountId = document.getElementById('modal-account-id').value;
        const amount = document.getElementById('modal-amount').value;
        const btn = document.getElementById('modal-submit-btn');
        
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        try {
            const response = await window.csrfFetch(`{{ url('admin/players') }}/${accountId}/add-vnd`, {
                method: 'POST',
                body: JSON.stringify({ amount: amount })
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                document.getElementById(`vnd-display-${accountId}`).innerText = result.new_vnd + 'đ';
                closeAddVndModal();
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'XÁC NHẬN CỘNG TIỀN';
        }
    });
</script>
@endsection
