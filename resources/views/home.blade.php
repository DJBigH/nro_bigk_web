@extends('layouts.app')

@section('title', 'Ngọc Rồng BigK - Game NRO Private Hấp Dẫn')

@section('content')
<!-- Hero Section -->
<div class="hero">
    <h1>NGỌC RỒNG BIGK</h1>
    <p>Máy chủ Ngọc Rồng Online phiên bản v7 thế hệ mới, hỗ trợ cực kỳ mượt mà, cộng đồng đông đảo và các tính năng độc quyền cực chất!</p>
    <div>
        @auth
            <a href="{{ route('dashboard') }}" class="btn">VÀO TÀI KHOẢN</a>
        @else
            <a href="{{ route('register') }}" class="btn">ĐĂNG KÝ NGAY</a>
            <a href="{{ route('login') }}" class="btn btn-secondary" style="margin-left: 10px;">ĐĂNG NHẬP</a>
        @endauth
    </div>
</div>

<!-- Features / Actions Grid -->
<div class="item-grid" style="margin-top: 40px;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 15px; color: var(--primary);">🛒</div>
        <h3 style="margin-bottom: 10px;">CỬA HÀNG WEB</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Mua sắm thỏi vàng, trang bị cực vip gửi trực tiếp vào hành trang nhân vật game.</p>
        <a href="{{ route('shop') }}" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">VÀO SHOP</a>
    </div>
    
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 15px; color: var(--secondary);">🎡</div>
        <h3 style="margin-bottom: 10px;">VÒNG QUAY MAY MẮN</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Thử vận may cực hạn với vòng quay nhận VND, xu game hoặc vật phẩm quý hiếm.</p>
        <a href="{{ route('spin') }}" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">THỬ VẬN MAY</a>
    </div>
    
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 15px; color: #ffd700;">🪙</div>
        <h3 style="margin-bottom: 10px;">CỬA HÀNG VẬT PHẨM</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Mua sắm thỏi vàng, hồng ngọc và các vật phẩm cày cuốc độc quyền cho tài khoản.</p>
        <a href="{{ route('shop') }}" class="btn" style="padding: 8px 16px; font-size: 0.9rem; background: #ffd700; color: #000; font-weight: 700; box-shadow: 0 0 10px rgba(255,215,0,0.3);">VÀO CỬA HÀNG</a>
    </div>
</div>

<!-- Guides & Articles Section -->
<div style="margin-top: 60px;">
    <h2 style="margin-bottom: 20px; border-left: 4px solid var(--primary); padding-left: 15px;">TIN TỨC & HƯỚNG DẪN</h2>
    <div class="card" style="padding: 0;">
        @if($posts->isEmpty())
            <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa có bài viết hướng dẫn nào.</p>
        @else
            <div style="display: flex; flex-direction: column;">
                @foreach($posts as $post)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid rgba(255, 107, 0, 0.1);">
                        <div>
                            @if($post->up_top == 1)
                                <span class="badge badge-primary" style="margin-right: 8px;">GHIM</span>
                            @endif
                            <a href="{{ route('guide.show', $post->id_post) }}" style="color: #fff; text-decoration: none; font-size: 1.2rem; font-weight: 700; font-family: 'Orbitron', sans-serif;">
                                {{ $post->title }}
                            </a>
                            <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">
                                Đăng lúc: {{ $post->time_post ? $post->time_post->format('d/m/Y H:i') : '' }}
                            </div>
                        </div>
                        <a href="{{ route('guide.show', $post->id_post) }}" class="btn" style="padding: 6px 14px; font-size: 0.85rem;">CHI TIẾT</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
