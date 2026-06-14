@extends('layouts.app')

@section('title', 'Đăng Bán Acc - NRO BigK')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    
    <div style="margin-bottom: 25px;">
        <a href="{{ route('acc_sales.index') }}" style="color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
            ← Quay lại chợ Acc
        </a>
    </div>

    <div class="card">
        <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif; text-shadow: 0 0 8px rgba(255,107,0,0.2);">
            ĐĂNG BÁN TÀI KHOẢN GAME
        </h2>

        <!-- Warning Box -->
        <div style="background: rgba(255, 23, 68, 0.05); border: 1px solid rgba(255, 23, 68, 0.2); border-radius: 8px; padding: 15px; margin-bottom: 25px; color: #ff5252; font-size: 0.9rem; line-height: 1.5;">
            <strong>⚠️ LƯU Ý QUAN TRỌNG KHI ĐĂNG BÁN:</strong>
            <ul style="margin: 8px 0 0 20px; padding: 0;">
                <li>Tài khoản đăng bán sẽ <strong>tự động bị khóa hoạt động</strong> trong game (`ban = 1`) ngay lập tức để bảo vệ vật phẩm tránh tẩu tán hoặc phá hoại trong quá trình bán.</li>
                <li>Bạn được quyền **Hủy bán** bất cứ lúc nào nếu tài khoản chưa được mua. Khi hủy bán, tài khoản game sẽ tự động mở khóa bình thường.</li>
                <li>Bạn cam kết thông tin tài khoản và mật khẩu nhập vào là chính xác. Nếu nhập sai, giao dịch sẽ bị admin từ chối và hoàn tiền cho người mua.</li>
                <li>Khi tài khoản được mua thành công, số tiền bán (sau khi trừ đi phí sàn do admin duyệt) sẽ được chuyển vào số dư Web của bạn sau khi Admin phê duyệt thông tin.</li>
                <li>Tiền bán acc trên Web <strong>không thể rút ra mặt</strong>, chỉ sử dụng để nạp vào game chơi tiếp hoặc tiêu dùng dịch vụ trên website.</li>
            </ul>
        </div>

        @if($playerCount === 0)
            <div style="background: rgba(255, 152, 0, 0.05); border: 1px solid rgba(255, 152, 0, 0.2); border-radius: 8px; padding: 15px; text-align: center; color: #ffb74d; font-size: 0.95rem;">
                ⚠️ <strong>Cảnh Báo:</strong> Tài khoản web hiện tại của bạn chưa tạo nhân vật trong game. Bạn vẫn có thể đăng bán tài khoản game khác, nhưng hãy đảm bảo rằng bạn nhập đúng thông tin tài khoản cần bán.
            </div>
            <br>
        @endif

        <form id="sellacc-form" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div class="form-group">
                    <label class="form-label">Tài khoản đăng nhập game cần bán</label>
                    <input type="text" name="username_sold" class="form-input" placeholder="Nhập username game..." required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mật khẩu game hiện tại</label>
                    <input type="password" name="password_sold" class="form-input" placeholder="Nhập password game..." required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Giá mong muốn bán (VND)</label>
                <input type="number" name="price" class="form-input" placeholder="Ví dụ: 100000" min="1000" required>
                <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 5px;">
                    Mức giá mong muốn người mua phải thanh toán để sở hữu acc (chưa bao gồm hoa hồng sàn).
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Mô tả chi tiết tài khoản</label>
                <textarea name="description" class="form-input" style="height: 120px; resize: vertical;" placeholder="Nhập các chỉ số nổi bật: Sức mạnh, hành tinh, các đệ tử, trang bị vip, cải trang hiện có..." required></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">Hình ảnh minh họa tài khoản (Tải lên tối đa 5 ảnh)</label>
                <input type="file" name="images[]" class="form-input" accept="image/*" multiple>
                <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 5px;">
                    Hãy chụp rõ Hành trang, Rương đồ, Thông tin nhân vật và các Cải trang vip của bạn.
                </small>
            </div>

            <button type="submit" id="sellacc-btn" class="btn" style="width: 100%; font-family: 'Orbitron', sans-serif; letter-spacing: 1px;">
                XÁC NHẬN ĐĂNG BÁN TÀI KHOẢN
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('sellacc-form');
        const btn = document.getElementById('sellacc-btn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            btn.innerText = 'ĐANG ĐĂNG BÁN & KHÓA TÀI KHOẢN GAME...';

            const formData = new FormData(form);

            try {
                // Fetch for multipart form data requires sending FormData directly (no json stringify)
                const response = await fetch('{{ route("acc_sales.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();

                if (result.success) {
                    window.showAlert(result.message, 'success');
                    form.reset();
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 2000);
                } else {
                    window.showAlert(result.message, 'error');
                    btn.disabled = false;
                    btn.innerText = 'XÁC NHẬN ĐĂNG BÁN TÀI KHOẢN';
                }
            } catch (error) {
                window.showAlert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.', 'error');
                btn.disabled = false;
                btn.innerText = 'XÁC NHẬN ĐĂNG BÁN TÀI KHOẢN';
            }
        });
    });
</script>
@endsection
