@extends('layouts.app')

@section('title', 'Nạp Tiền - NRO BigK')

@section('content')
<div class="card" style="margin-bottom: 40px; text-align: center;">
    <h1 style="color: var(--primary); margin-bottom: 10px;">NẠP TIỀN ĐỒNG HÀNH</h1>
    <p style="color: var(--text-muted);">
        Số dư tài khoản hiện tại của bạn: 
        <strong style="color: var(--secondary); font-family: 'Orbitron', sans-serif; font-size: 1.3rem;" id="user-balance">
            {{ number_format($account->vnd ?? 0) }} VND
        </strong>
    </p>
</div>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
    
    <!-- Packages Section -->
    <div>
        <h2 style="color: #fff; margin-bottom: 20px; border-left: 4px solid var(--primary); padding-left: 12px;">CHỌN GÓI NẠP</h2>
        
        <form id="recharge-form">
            <div class="item-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); margin-top: 0;">
                @foreach($packages as $pkg)
                    <label class="card" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; border-color: rgba(255, 107, 0, 0.15); position: relative; transition: var(--transition);">
                        <input type="radio" name="package_id" value="{{ $pkg->id }}" style="position: absolute; top: 12px; left: 12px; accent-color: var(--primary);" required>
                        
                        <div style="font-size: 2rem; margin-top: 10px;">💵</div>
                        <h4 style="color: #fff; font-size: 1.1rem; text-transform: uppercase;">{{ $pkg->name }}</h4>
                        <div style="font-family: 'Orbitron', sans-serif; font-size: 1.25rem; color: var(--secondary); font-weight: 700;">
                            {{ number_format($pkg->amount_vnd) }} đ
                        </div>
                        
                        @if($pkg->bonus_vnd > 0)
                            <span class="badge badge-success" style="font-size: 0.75rem;">
                                KHUYẾN MÃI +{{ number_format($pkg->bonus_vnd) }}đ
                            </span>
                        @endif

                        @if($pkg->is_popular)
                            <div style="position: absolute; top: 0; right: 0; background: var(--primary); color: #fff; font-size: 0.7rem; font-family: 'Orbitron', sans-serif; padding: 2px 8px; border-bottom-left-radius: 6px; font-weight: 900;">
                                HOT
                            </div>
                        @endif
                    </label>
                @endforeach
            </div>

            <!-- Payment instructions mock -->
            <div class="card" style="margin-top: 30px; border-color: var(--secondary);">
                <h3 style="color: var(--secondary); margin-bottom: 15px;">THÔNG TIN CHUYỂN KHOẢN (BẢN DEMO)</h3>
                <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.6; margin-bottom: 15px;">
                    Để nạp tiền vào tài khoản, vui lòng thực hiện chuyển khoản vào ví điện tử Momo hoặc ngân hàng. Sau đó bấm nút <strong>"TÔI ĐÃ THANH TOÁN"</strong> bên dưới để hệ thống tự động cộng tiền (Cơ chế nạp tự động demo).
                </p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 6px; font-size: 0.95rem;">
                    <div>
                        <strong style="color: var(--primary);">NGÂN HÀNG MBBANK</strong><br>
                        Số TK: <strong>0386789999</strong><br>
                        Chủ TK: <strong>NGUYEN VAN A</strong><br>
                        Nội dung: <strong>nro {{ $account->username }}</strong>
                    </div>
                    <div>
                        <strong style="color: #e91e63;">VÍ MOMO</strong><br>
                        Số ĐT: <strong>0386789999</strong><br>
                        Chủ TK: <strong>NGUYEN VAN A</strong><br>
                        Nội dung: <strong>nro {{ $account->username }}</strong>
                    </div>
                </div>
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" id="recharge-btn" class="btn" style="width: 100%; font-size: 1.2rem; padding: 16px;">
                    TÔI ĐÃ THANH TOÁN
                </button>
            </div>
        </form>
    </div>

    <!-- Recharge History -->
    <div>
        <h2 style="color: #fff; margin-bottom: 20px; border-left: 4px solid var(--secondary); padding-left: 12px;">LỊCH SỬ NẠP</h2>
        
        <div class="card" style="padding: 0; max-height: 520px; overflow-y: auto;">
            @if($transactions->isEmpty())
                <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa có giao dịch nạp tiền nào.</p>
            @else
                <table style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th>Mã GD</th>
                            <th>Số Tiền</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-body">
                        @foreach($transactions as $tx)
                            <tr>
                                <td style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem;">
                                    {{ $tx->transaction_code }}
                                </td>
                                <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary);">
                                    {{ number_format($tx->amount) }}đ
                                </td>
                                <td>
                                    @if($tx->status === 'success')
                                        <span class="badge badge-success">Thành công</span>
                                    @elseif($tx->status === 'failed')
                                        <span class="badge badge-danger">Thất bại</span>
                                    @else
                                        <span class="badge badge-secondary">Đang chờ</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('recharge-form');
        const btn = document.getElementById('recharge-btn');
        const balanceEl = document.getElementById('user-balance');
        const tbody = document.getElementById('transactions-body');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            btn.innerText = 'ĐANG KIỂM TRA GIAO DỊCH...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await window.csrfFetch('{{ route("recharge.confirm") }}', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();

                if (result.success) {
                    window.showAlert(result.message, 'success');
                    balanceEl.innerText = result.new_balance + ' VND';
                    
                    // Reload window after 1.5s to refresh list or dynamically append
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    window.showAlert(result.message, 'error');
                    btn.disabled = false;
                    btn.innerText = 'TÔI ĐÃ THANH TOÁN';
                }
            } catch (error) {
                window.showAlert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.', 'error');
                btn.disabled = false;
                btn.innerText = 'TÔI ĐÃ THANH TOÁN';
            }
        });
    });
</script>
@endsection
