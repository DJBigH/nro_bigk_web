@extends('layouts.app')

@section('title', 'Đăng Nhập - NRO BigK')

@section('content')
<div style="max-width: 450px; margin: 40px auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 25px; color: var(--primary);">ĐĂNG NHẬP</h2>
        
        <form id="login-form">
            <div class="form-group">
                <label class="form-label">Tên tài khoản</label>
                <input type="text" name="username" class="form-input" placeholder="Nhập tên tài khoản game..." required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-input" placeholder="Nhập mật khẩu..." required>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="submit" id="login-btn" class="btn" style="width: 100%;">
                    ĐĂNG NHẬP
                </button>
            </div>
        </form>
        
        <div style="margin-top: 20px; text-align: center; font-size: 0.95rem; color: var(--text-muted);">
            Chưa có tài khoản? <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 700;">Đăng ký ngay</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('login-form');
        const btn = document.getElementById('login-btn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            btn.innerText = 'ĐANG XỬ LÝ...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await window.csrfFetch('{{ route("login") }}', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();

                if (result.success) {
                    window.showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    window.showAlert(result.message, 'error');
                    btn.disabled = false;
                    btn.innerText = 'ĐĂNG NHẬP';
                }
            } catch (error) {
                window.showAlert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.', 'error');
                btn.disabled = false;
                btn.innerText = 'ĐĂNG NHẬP';
            }
        });
    });
</script>
@endsection
