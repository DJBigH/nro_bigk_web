@extends('admin.layout')

@section('admin_content')
<div style="display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="color: #fff; margin: 0; font-family: 'Orbitron', sans-serif;">🛡️ QUẢN LÝ CHỢ MUA BÁN ACC</h2>
    </div>

    <!-- Commission Setting Card -->
    <div class="card" style="background: rgba(255, 107, 0, 0.03); border: 1px solid rgba(255, 107, 0, 0.15);">
        <h4 style="color: var(--primary); margin-bottom: 12px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">CÀI ĐẶT PHÍ HOA HỒNG SÀN GIAO DỊCH</h4>
        <form id="commission-form" style="display: flex; gap: 15px; align-items: center; max-width: 500px;">
            <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                <input type="number" name="acc_sale_commission" class="form-input" min="0" max="100" value="{{ $commission }}" required style="width: 100px; text-align: center;">
                <span style="color: #fff; font-family: 'Orbitron', sans-serif; font-weight: 700;">% Commission (Phí hoa hồng)</span>
            </div>
            <button type="submit" id="commission-btn" class="btn" style="padding: 0 20px; font-size: 0.85rem;">CẬP NHẬT PHÍ</button>
        </form>
        <small style="color: var(--text-muted); display: block; margin-top: 8px;">Phần trăm này sẽ bị trừ trực tiếp từ giá bán của tài khoản khi giao dịch được admin duyệt. Số tiền còn lại sẽ được cộng cho người bán.</small>
    </div>

    <!-- Listings Table Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #fff; font-family: 'Orbitron', sans-serif; margin: 0; font-size: 1.1rem;">DANH SÁCH GIAO DỊCH</h3>
            
            <form action="{{ route('admin.acc_sales') }}" method="GET" style="display: flex; gap: 10px;">
                <select name="status" class="form-input" style="padding: 5px 10px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="selling" {{ $status === 'selling' ? 'selected' : '' }}>Đang Bán</option>
                    <option value="sold" {{ $status === 'sold' ? 'selected' : '' }}>Đã Bán</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Đã Hủy</option>
                </select>
            </form>
        </div>

        @if($listings->isEmpty())
            <p style="text-align: center; color: var(--text-muted); padding: 30px 0;">Không tìm thấy giao dịch nào.</p>
        @else
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(255, 107, 0, 0.2); color: #fff; font-family: 'Orbitron', sans-serif; font-size: 0.85rem;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">NGƯỜI BÁN</th>
                            <th style="padding: 10px;">NGƯỜI MUA</th>
                            <th style="padding: 10px;">TÀI KHOẢN GAME</th>
                            <th style="padding: 10px;">GIÁ GỐC</th>
                            <th style="padding: 10px; width: 180px;">GIÁ SALE</th>
                            <th style="padding: 10px;">TRẠNG THÁI</th>
                            <th style="padding: 10px;">THANH TOÁN</th>
                            <th style="padding: 10px; width: 220px; text-align: right;">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $item)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.85rem; color: #fff;">
                                <td style="padding: 10px; font-family: 'Orbitron', sans-serif; font-weight: 700;">#{{ $item->id }}</td>
                                <td style="padding: 10px; color: var(--primary);">{{ $item->seller->username ?? 'N/A' }} (ID: {{ $item->seller_id }})</td>
                                <td style="padding: 10px; color: #00bcd4;">{{ $item->buyer->username ?? '--' }}</td>
                                <td style="padding: 10px;">{{ $item->username_sold }}</td>
                                <td style="padding: 10px;">{{ number_format($item->price) }}đ</td>
                                <td style="padding: 10px;">
                                    @if($item->status === 'selling')
                                        <form class="update-price-form" data-id="{{ $item->id }}" style="display: flex; gap: 5px; align-items: center;">
                                            <input type="number" class="form-input sale-price-input" style="padding: 2px 5px; font-size: 0.8rem; width: 90px;" value="{{ $item->sale_price }}" placeholder="Giá sale">
                                            <button type="submit" class="btn btn-secondary" style="padding: 2px 6px; font-size: 0.75rem;">Lưu</button>
                                        </form>
                                    @else
                                        <span>{{ $item->sale_price !== null ? number_format($item->sale_price) . 'đ' : '--' }}</span>
                                    @endif
                                </td>
                                <td style="padding: 10px;">
                                    @if($item->status === 'selling')
                                        <span class="badge badge-primary" style="color: #00bcd4; border-color: #00bcd4; font-size: 0.75rem;">Đang bán</span>
                                    @elseif($item->status === 'sold')
                                        <span class="badge badge-primary" style="color: #00e676; border-color: #00e676; font-size: 0.75rem;">Đã bán</span>
                                    @else
                                        <span class="badge badge-secondary" style="color: #ff1744; border-color: #ff1744; font-size: 0.75rem;">Đã hủy</span>
                                        @if($item->cancel_reason)
                                            <div style="font-size: 0.7rem; color: #ff5252; margin-top: 3px;">{{ $item->cancel_reason }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td style="padding: 10px;">
                                    @if($item->status === 'sold')
                                        @if($item->payout_status === 'pending')
                                            <span style="color: #ffd700; font-weight: 700; font-size: 0.8rem;">Chờ duyệt</span>
                                        @elseif($item->payout_status === 'approved')
                                            <span style="color: #00e676; font-weight: 700; font-size: 0.8rem;">Đã duyệt ví</span>
                                        @elseif($item->payout_status === 'refunded')
                                            <span style="color: #ff1744; font-weight: 700; font-size: 0.8rem;">Đã hoàn tiền</span>
                                        @endif
                                    @else
                                        <span>--</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; justify-content: flex-end; gap: 8px;">
                                        @if($item->status === 'selling')
                                            <button class="btn btn-secondary cancel-listing-btn" data-id="{{ $item->id }}" style="font-size: 0.75rem; padding: 4px 8px; border-color: #ff1744; color: #ff1744;">Hủy bán</button>
                                        @elseif($item->status === 'sold' && $item->payout_status === 'pending')
                                            <button class="btn approve-payout-btn" data-id="{{ $item->id }}" style="font-size: 0.75rem; padding: 4px 8px; background: #00e676; border-color: #00e676;">Duyệt ví</button>
                                            <button class="btn btn-secondary refund-btn" data-id="{{ $item->id }}" style="font-size: 0.75rem; padding: 4px 8px; border-color: #ff1744; color: #ff1744;">Hoàn tiền</button>
                                        @else
                                            <span style="color: var(--text-muted);">Không</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px;">
                {{ $listings->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Admin Modals for Cancel / Refund Reasons -->
<div id="reason-modal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3 id="modal-title" style="color: var(--primary); font-family: 'Orbitron', sans-serif; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">HỦY BÁN / HOÀN TIỀN</h3>
        <form id="reason-form">
            <input type="hidden" id="modal-listing-id" name="listing_id">
            <input type="hidden" id="modal-action-type" name="action_type"> <!-- 'cancel' or 'refund' -->
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" id="modal-label">Lý do thực hiện hành động</label>
                <textarea name="cancel_reason" id="modal-reason-input" class="form-input" style="height: 100px; resize: vertical;" placeholder="Nhập lý do gửi đến người chơi..." required></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeReasonModal()" style="padding: 0 15px;">Đóng</button>
                <button type="submit" id="reason-submit-btn" class="btn" style="padding: 0 20px; background: #ff1744; border-color: #ff1744;">XÁC NHẬN</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openReasonModal(id, type, title, label) {
        document.getElementById('modal-listing-id').value = id;
        document.getElementById('modal-action-type').value = type;
        document.getElementById('modal-title').innerText = title;
        document.getElementById('modal-label').innerText = label;
        document.getElementById('modal-reason-input').value = '';
        
        document.getElementById('reason-modal').classList.add('show');
    }

    function closeReasonModal() {
        document.getElementById('reason-modal').classList.remove('show');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Commission Form Handler
        const cForm = document.getElementById('commission-form');
        const cBtn = document.getElementById('commission-btn');
        cForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            cBtn.disabled = true;
            cBtn.innerText = 'CHỜ...';

            const formData = new FormData(cForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await window.csrfFetch('{{ route("admin.acc_sales.settings") }}', {
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
                window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
            } finally {
                cBtn.disabled = false;
                cBtn.innerText = 'CẬP NHẬT PHÍ';
            }
        });

        // Inline Price Updates
        const priceForms = document.querySelectorAll('.update-price-form');
        priceForms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id = form.getAttribute('data-id');
                const salePriceInput = form.querySelector('.sale-price-input').value;
                const saveBtn = form.querySelector('button');

                saveBtn.disabled = true;
                saveBtn.innerText = '...';

                try {
                    const response = await window.csrfFetch(`{{ url('admin/acc-sales') }}/${id}/price`, {
                        method: 'PUT',
                        body: JSON.stringify({ sale_price: salePriceInput || null })
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
                    saveBtn.disabled = false;
                    saveBtn.innerText = 'Lưu';
                }
            });
        });

        // Cancel Listing Buttons (Admin)
        const cancelListingBtns = document.querySelectorAll('.cancel-listing-btn');
        cancelListingBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                openReasonModal(
                    id, 
                    'cancel', 
                    'HỦY ĐĂNG BÁN ACC #' + id, 
                    'Lý do hủy bán tài khoản (người bán sẽ nhận lại acc game và mở khóa):'
                );
            });
        });

        // Refund Payout Buttons
        const refundBtns = document.querySelectorAll('.refund-btn');
        refundBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                openReasonModal(
                    id, 
                    'refund', 
                    'HỦY ĐƠN & HOÀN TIỀN #' + id, 
                    'Lý do hoàn tiền (VND sẽ trả lại cho người mua, tài khoản game trả lại người bán):'
                );
            });
        });

        // Approve Payout Buttons
        const approveBtns = document.querySelectorAll('.approve-payout-btn');
        approveBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!true) {
                    return;
                }

                btn.disabled = true;
                btn.innerText = 'Đang duyệt...';

                try {
                    const response = await window.csrfFetch(`{{ url('admin/acc-sales') }}/${id}/approve`, {
                        method: 'POST'
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        window.showAlert(result.message, 'error');
                        btn.disabled = false;
                        btn.innerText = 'Duyệt ví';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối.', 'error');
                    btn.disabled = false;
                    btn.innerText = 'Duyệt ví';
                }
            });
        });

        // Modal Form Submit
        const modalForm = document.getElementById('reason-form');
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('modal-listing-id').value;
            const type = document.getElementById('modal-action-type').value;
            const reason = document.getElementById('modal-reason-input').value;
            const submitBtn = document.getElementById('reason-submit-btn');

            submitBtn.disabled = true;
            submitBtn.innerText = 'ĐANG XỬ LÝ...';

            let url = '';
            if (type === 'cancel') {
                url = `{{ url('admin/acc-sales') }}/${id}/cancel`;
            } else if (type === 'refund') {
                url = `{{ url('admin/acc-sales') }}/${id}/refund`;
            }

            try {
                const response = await window.csrfFetch(url, {
                    method: 'POST',
                    body: JSON.stringify({ cancel_reason: reason })
                });
                const result = await response.json();

                if (result.success) {
                    window.showAlert(result.message, 'success');
                    closeReasonModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.showAlert(result.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'XÁC NHẬN';
                }
            } catch (error) {
                window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerText = 'XÁC NHẬN';
            }
        });
    });
</script>
@endsection
