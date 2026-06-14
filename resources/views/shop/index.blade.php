@extends('layouts.app')

@section('title', 'Cửa Hàng Vật Phẩm - NRO BigK')

@section('content')
<div class="card" style="margin-bottom: 40px; text-align: center;">
    <h1 style="color: var(--primary); margin-bottom: 10px;">CỬA HÀNG VẬT PHẨM</h1>
    <p style="color: var(--text-muted);">
        Số dư của bạn: 
        <strong style="color: var(--secondary); font-family: 'Orbitron', sans-serif; font-size: 1.25rem;" id="user-balance">
            {{ number_format($account->vnd ?? 0) }} VND
        </strong>
    </p>
</div>

<!-- Warning message -->
<div class="card" style="margin-bottom: 30px; border-color: #f44336; background: rgba(244, 67, 54, 0.05); padding: 20px;">
    <h4 style="color: #ff5252; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        ⚠️ CẢNH BÁO QUAN TRỌNG:
    </h4>
    <ul style="padding-left: 20px; color: #ff8a80; line-height: 1.6; font-size: 1.05rem;">
        <li>Yêu cầu tài khoản game của bạn phải <strong>THOÁT GAME HOÀN TOÀN</strong> trước khi ấn mua hàng. Nếu đang online trong game mà mua hàng, vật phẩm sẽ bị ghi đè khi thoát game dẫn đến <strong>MẤT ĐỒ</strong>.</li>
        <li>Hành trang nhân vật cần đảm bảo <strong>CÒN ĐỦ Ô TRỐNG</strong>. Nếu hành trang đã đầy mà cố tình nhận thì vật phẩm sẽ rơi mất, <strong>MẤT TỰ CHỊU TRÁCH NHIỆM</strong>.</li>
    </ul>
</div>

@if($items->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 0;">
        <p style="color: var(--text-muted); font-size: 1.2rem;">Hiện tại chưa có vật phẩm nào được đăng bán trên Web Shop.</p>
    </div>
@else
    <div class="item-grid">
        @foreach($items as $item)
            @php
                $tpl = $item->template;
            @endphp
            @if($tpl)
                <div class="card shop-card">
                    <!-- Overridden image or game default icon -->
                    @if($item->image_override)
                        <img src="{{ $item->image_override }}" alt="{{ $tpl->NAME }}" class="shop-img">
                    @else
                        <!-- Fallback placeholder item rendering using template info -->
                        <div class="shop-img" style="background: rgba(255,107,0,0.1); border: 1px solid var(--border-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 15px;">
                            📦
                        </div>
                    @endif

                    <div>
                        <h3 class="shop-title">{{ $tpl->NAME }}</h3>
                        <span class="badge badge-secondary" style="margin-bottom: 10px; font-size: 0.75rem;">ID Template: {{ $tpl->id }}</span>
                        
                        <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
                            Loại vật phẩm: {{ $tpl->TYPE == 5 ? 'Cải trang' : ($tpl->TYPE == 12 ? 'Thỏi vàng/Tiền' : 'Vật phẩm') }}
                        </p>
                    </div>

                    <div>
                        <div class="shop-price">{{ number_format($item->price_vnd) }}đ</div>
                        
                        <div class="shop-stock">
                            Số lượng: 
                            @if($item->quantity === -1)
                                <span style="color: #4caf50; font-weight: 700;">Vô hạn</span>
                            @else
                                <span style="color: var(--primary); font-weight: 700;">{{ $item->quantity }}</span>
                            @endif
                        </div>

                        <button 
                            class="btn" 
                            style="width: 100%;" 
                            onclick="buyItem({{ $item->id }}, '{{ $tpl->NAME }}', {{ $item->price_vnd }})"
                        >
                            MUA NGAY
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Pagination -->
    <div style="margin-top: 30px;">
        {{ $items->links() }}
    </div>
@endif
@endsection

@section('scripts')
<script>
    async function buyItem(itemId, itemName, price) {
        if (!confirm(`Bạn có chắc chắn muốn mua "${itemName}" với giá ${price.toLocaleString()} VND?\nVui lòng đảm bảo nhân vật đã THOÁT GAME.`)) {
            return;
        }

        const balanceEl = document.getElementById('user-balance');

        try {
            const response = await window.csrfFetch('{{ route("shop.buy") }}', {
                method: 'POST',
                body: JSON.stringify({ item_id: itemId })
            });

            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                balanceEl.innerText = result.new_balance + ' VND';
                
                // Refresh layout state in 2s
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.', 'error');
        }
    }
</script>
@endsection
