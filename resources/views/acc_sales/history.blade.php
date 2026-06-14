@extends('layouts.app')

@section('title', 'Lịch Sử Giao Dịch Acc - NRO BigK')

@section('content')
<div style="margin-bottom: 25px;">
    <a href="{{ route('acc_sales.index') }}" style="color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
        ← Quay lại chợ Acc
    </a>
</div>

<div style="display: flex; flex-direction: column; gap: 40px;">
    
    <!-- Section 1: Purchases -->
    <div class="card">
        <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
            LỊCH SỬ MUA ACC (ĐÃ SỞ HỮU)
        </h2>

        @if($purchases->isEmpty())
            <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">Bạn chưa mua tài khoản nào trên chợ.</p>
        @else
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(255, 107, 0, 0.2); color: #fff; font-family: 'Orbitron', sans-serif; font-size: 0.9rem;">
                            <th style="padding: 12px 10px;">MÃ ĐƠN</th>
                            <th style="padding: 12px 10px;">NGÀY MUA</th>
                            <th style="padding: 12px 10px;">SỐ TIỀN</th>
                            <th style="padding: 12px 10px;">THÔNG TIN ĐĂNG NHẬP</th>
                            <th style="padding: 12px 10px;">TRẠNG THÁI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $order)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; color: #fff;">
                                <td style="padding: 12px 10px; font-family: 'Orbitron', sans-serif; font-weight: 700;">#{{ $order->id }}</td>
                                <td style="padding: 12px 10px;">{{ $order->updated_at->format('H:i d/m/Y') }}</td>
                                <td style="padding: 12px 10px; color: var(--secondary); font-family: 'Orbitron', sans-serif; font-weight: 700;">
                                    {{ number_format($order->display_price) }}đ
                                </td>
                                <td style="padding: 12px 10px;">
                                    @if($order->status === 'sold' && $order->payout_status !== 'refunded')
                                        <div style="display: inline-flex; flex-direction: column; gap: 6px; background: rgba(255,255,255,0.02); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(255,107,0,0.15); min-width: 280px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span>TK: <strong id="user-{{ $order->id }}" style="color: #ffd700;">{{ $order->username_sold }}</strong></span>
                                                <button class="btn btn-secondary" onclick="copyText('user-{{ $order->id }}')" style="font-size: 0.7rem; padding: 2px 6px;">Sao chép</button>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 5px; margin-top: 5px;">
                                                <span>MK: <strong id="pass-{{ $order->id }}" style="color: #ffd700;">{{ $order->password_sold }}</strong></span>
                                                <button class="btn btn-secondary" onclick="copyText('pass-{{ $order->id }}')" style="font-size: 0.7rem; padding: 2px 6px;">Sao chép</button>
                                            </div>
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted);">Không khả dụng</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 10px;">
                                    @if($order->payout_status === 'pending')
                                        <span class="badge badge-secondary" style="color: #ffd700; border-color: #ffd700;">Chờ Admin Duyệt</span>
                                    @elseif($order->payout_status === 'approved')
                                        <span class="badge badge-primary" style="color: #00e676; border-color: #00e676;">Đã Hoàn Tất</span>
                                    @elseif($order->payout_status === 'refunded')
                                        <span class="badge badge-secondary" style="color: #ff1744; border-color: #ff1744;">Đã Hoàn Tiền</span>
                                        @if($order->cancel_reason)
                                            <div style="font-size: 0.75rem; color: #ff5252; margin-top: 3px; max-width: 150px;">Lý do: {{ $order->cancel_reason }}</div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Section 2: Sales -->
    <div class="card">
        <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
            DANH SÁCH ACC ĐĂNG BÁN (LỊCH SỬ BÁN)
        </h2>

        @if($sales->isEmpty())
            <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">Bạn chưa đăng bán tài khoản nào.</p>
        @else
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(255, 107, 0, 0.2); color: #fff; font-family: 'Orbitron', sans-serif; font-size: 0.9rem;">
                            <th style="padding: 12px 10px;">ID</th>
                            <th style="padding: 12px 10px;">ACC GAME</th>
                            <th style="padding: 12px 10px;">GIÁ GỐC</th>
                            <th style="padding: 12px 10px;">GIÁ KHUYẾN MÃI</th>
                            <th style="padding: 12px 10px;">TRẠNG THÁI</th>
                            <th style="padding: 12px 10px;">THANH TOÁN</th>
                            <th style="padding: 12px 10px;">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; color: #fff;">
                                <td style="padding: 12px 10px; font-family: 'Orbitron', sans-serif; font-weight: 700;">#{{ $sale->id }}</td>
                                <td style="padding: 12px 10px; font-family: 'Orbitron', sans-serif;">{{ $sale->username_sold }}</td>
                                <td style="padding: 12px 10px;">{{ number_format($sale->price) }}đ</td>
                                <td style="padding: 12px 10px; color: var(--secondary); font-family: 'Orbitron', sans-serif;">
                                    {{ $sale->sale_price !== null ? number_format($sale->sale_price) . 'đ' : '--' }}
                                </td>
                                <td style="padding: 12px 10px;">
                                    @if($sale->status === 'selling')
                                        <span class="badge badge-primary" style="color: #00bcd4; border-color: #00bcd4;">Đang Bán (Đã khóa game)</span>
                                    @elseif($sale->status === 'sold')
                                        <span class="badge badge-primary" style="color: #00e676; border-color: #00e676;">Đã Bán</span>
                                    @else
                                        <span class="badge badge-secondary" style="color: #ff1744; border-color: #ff1744;">Đã Hủy</span>
                                        @if($sale->cancel_reason)
                                            <div style="font-size: 0.75rem; color: #ff5252; margin-top: 3px; max-width: 150px;">Lý do: {{ $sale->cancel_reason }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td style="padding: 12px 10px;">
                                    @if($sale->status === 'sold')
                                        @if($sale->payout_status === 'pending')
                                            <span style="color: #ffd700; font-weight: 600;">Chờ Admin duyệt ví</span>
                                        @elseif($sale->payout_status === 'approved')
                                            <span style="color: #00e676; font-weight: 600;">Đã thanh toán ví Web</span>
                                        @elseif($sale->payout_status === 'refunded')
                                            <span style="color: #ff1744; font-weight: 600;">Đã hủy/Hoàn tiền mua</span>
                                        @endif
                                    @else
                                        <span style="color: var(--text-muted);">--</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 10px;">
                                    @if($sale->status === 'selling')
                                        <button class="btn btn-secondary cancel-btn" data-id="{{ $sale->id }}" style="font-size: 0.8rem; padding: 6px 12px; border-color: #ff1744; color: #ff1744;">Hủy bán</button>
                                    @else
                                        <span style="color: var(--text-muted);">Không có</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    function copyText(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            window.showAlert('Đã sao chép: ' + text, 'success');
        }).catch(err => {
            window.showAlert('Không thể sao chép tự động.', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const cancelButtons = document.querySelectorAll('.cancel-btn');
        cancelButtons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm('Bạn có chắc chắn muốn hủy đăng bán tài khoản #' + id + '? Tài khoản game sẽ được mở khóa lại.')) {
                    return;
                }

                btn.disabled = true;
                btn.innerText = 'Đang hủy...';

                try {
                    const response = await window.csrfFetch('{{ route("acc_sales.cancel") }}', {
                        method: 'POST',
                        body: JSON.stringify({ listing_id: id })
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        window.showAlert(result.message, 'error');
                        btn.disabled = false;
                        btn.innerText = 'Hủy bán';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
                    btn.disabled = false;
                    btn.innerText = 'Hủy bán';
                }
            });
        });
    });
</script>
@endsection
