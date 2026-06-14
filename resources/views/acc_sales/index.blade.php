@extends('layouts.app')

@section('title', 'Chợ Acc - NRO BigK')

@section('content')
<div style="margin-bottom: 40px;">
    <!-- Marketplace Header -->
    <div style="text-align: center; margin-bottom: 35px;">
        <h1 style="color: var(--primary); font-family: 'Orbitron', sans-serif; font-size: 2.5rem; margin-bottom: 10px; text-shadow: 0 0 15px rgba(255, 107, 0, 0.4);">
            CHỢ MUA BÁN ACC
        </h1>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto; font-size: 1rem;">
            Nơi giao dịch tài khoản Ngọc Rồng BigK an toàn, minh bạch. Hệ thống tự động khóa tài khoản khi đăng bán để bảo vệ vật phẩm tuyệt đối.
        </p>
    </div>

    <!-- Policy Alerts Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <div style="background: rgba(0, 230, 118, 0.05); border: 1px solid rgba(0, 230, 118, 0.2); border-radius: 8px; padding: 15px; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">🛡️</div>
            <h4 style="color: #00e676; margin-bottom: 5px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">BẢO HÀNH 1 GIỜ</h4>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Hoàn tiền 100% nếu sai thông tin đăng nhập trong vòng 1 tiếng.</p>
        </div>
        <div style="background: rgba(255, 107, 0, 0.05); border: 1px solid rgba(255, 107, 0, 0.2); border-radius: 8px; padding: 15px; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">📸</div>
            <h4 style="color: var(--primary); margin-bottom: 5px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">ĐÚNG NHƯ MÔ TẢ</h4>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Tài khoản nhận được cam kết giống hoàn toàn với mô tả và ảnh chụp.</p>
        </div>
        <div style="background: rgba(0, 188, 212, 0.05); border: 1px solid rgba(0, 188, 212, 0.2); border-radius: 8px; padding: 15px; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">🔒</div>
            <h4 style="color: #00bcd4; margin-bottom: 5px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">KHÓA ACC KHI BÁN</h4>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Tự động khóa acc game khi đăng bán để tránh phá đồ hay tẩu tán.</p>
        </div>
        <div style="background: rgba(255, 215, 0, 0.05); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 8px; padding: 15px; text-align: center;">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">⚖️</div>
            <h4 style="color: #ffd700; margin-bottom: 5px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">ADMIN DUYỆT TIỀN</h4>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Tiền chỉ chuyển đến người bán sau khi admin kiểm tra thông tin hợp lệ.</p>
        </div>
    </div>

    <!-- Filter Bar and Action Button -->
    <div class="card" style="margin-bottom: 30px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
        <form action="{{ route('acc_sales.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; flex: 1;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" class="form-input" placeholder="Tìm theo mô tả..." value="{{ request('search') }}">
            </div>
            
            <div style="min-width: 150px;">
                <select name="price_range" class="form-input" onchange="this.form.submit()">
                    <option value="">-- Tất cả giá --</option>
                    <option value="under_50k" {{ request('price_range') === 'under_50k' ? 'selected' : '' }}>Dưới 50,000đ</option>
                    <option value="50k_200k" {{ request('price_range') === '50k_200k' ? 'selected' : '' }}>50,000đ - 200,000đ</option>
                    <option value="over_200k" {{ request('price_range') === 'over_200k' ? 'selected' : '' }}>Trên 200,000đ</option>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary" style="padding: 0 20px;">LỌC KẾT QUẢ</button>
            @if(request('search') || request('price_range'))
                <a href="{{ route('acc_sales.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; padding: 0 15px;">XÓA LỌC</a>
            @endif
        </form>

        <div>
            <a href="{{ route('acc_sales.sell') }}" class="btn">ĐĂNG BÁN ACC CỦA BẠN</a>
        </div>
    </div>

    <!-- Listings Grid -->
    @if($listings->isEmpty())
        <div class="card" style="text-align: center; padding: 50px 0;">
            <div style="font-size: 3rem; margin-bottom: 15px;">🛡️</div>
            <h3 style="color: #fff; margin-bottom: 10px;">CHƯA CÓ TÀI KHOẢN ĐĂNG BÁN</h3>
            <p style="color: var(--text-muted);">Hiện tại chưa có tài khoản nào phù hợp được đăng bán trên hệ thống.</p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
            @foreach($listings as $item)
                @php
                    $imgs = $item->images;
                    $firstImg = is_array($imgs) && count($imgs) > 0 ? $imgs[0] : '/images/no_image.png';
                @endphp
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 15px; background: rgba(26, 26, 46, 0.4); border: 1px solid rgba(255,107,0,0.1); border-radius: 10px; transition: transform 0.2s, border-color 0.2s; position: relative;" onmouseover="this.style.borderColor='var(--primary)';" onmouseout="this.style.borderColor='rgba(255,107,0,0.1)';">
                    
                    @if($item->sale_price !== null)
                        <div style="position: absolute; top: 15px; right: 15px; background: #ff1744; color: #fff; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: 700; z-index: 5; box-shadow: 0 0 8px #ff1744;">
                            SALE
                        </div>
                    @endif

                    <div>
                        <!-- Thumbnail -->
                        <div style="width: 100%; height: 160px; border-radius: 6px; overflow: hidden; background: #0c0c14; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 15px; position: relative;">
                            <img src="{{ $firstImg }}" alt="Account Image" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>

                        <!-- Info -->
                        <h4 style="color: #fff; font-family: 'Orbitron', sans-serif; margin-bottom: 8px; font-size: 1.1rem;">
                            MÃ ACC: #{{ $item->id }}
                        </h4>
                        
                        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.4; height: 3.8em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-bottom: 15px;">
                            {{ $item->description }}
                        </p>
                    </div>

                    <!-- Price and Button -->
                    <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div>
                            @if($item->sale_price !== null)
                                <div style="color: var(--text-muted); text-decoration: line-through; font-size: 0.8rem; font-family: 'Orbitron', sans-serif;">
                                    {{ number_format($item->price) }}đ
                                </div>
                                <div style="color: var(--secondary); font-family: 'Orbitron', sans-serif; font-weight: 700; font-size: 1.2rem;">
                                    {{ number_format($item->sale_price) }}đ
                                </div>
                            @else
                                <div style="color: var(--primary); font-family: 'Orbitron', sans-serif; font-weight: 700; font-size: 1.2rem;">
                                    {{ number_format($item->price) }}đ
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('acc_sales.show', $item->id) }}" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.85rem;">XEM CHI TIẾT</a>
                    </div>

                </div>
            @endforeach
        </div>

        <div style="margin-top: 35px;">
            {{ $listings->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
