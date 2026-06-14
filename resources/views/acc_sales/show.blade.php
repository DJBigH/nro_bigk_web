@extends('layouts.app')

@section('title', 'Chi Tiết Acc #' . $listing->id . ' - NRO BigK')

@section('content')
<div style="margin-bottom: 25px;">
    <a href="{{ route('acc_sales.index') }}" style="color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
        ← Quay lại chợ Acc
    </a>
</div>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
    
    <!-- Left Column: Media Slider & Details -->
    <div>
        <!-- Image Slider -->
        <div class="card" style="padding: 15px; margin-bottom: 30px; background: rgba(18, 18, 30, 0.4);">
            @php
                $imgs = $listing->images;
                $hasImgs = is_array($imgs) && count($imgs) > 0;
            @endphp
            
            <div style="position: relative; width: 100%; height: 380px; border-radius: 8px; overflow: hidden; background: #08080f; border: 1px solid rgba(255,255,255,0.05);">
                @if($hasImgs)
                    <div id="slider-container" style="width: 100%; height: 100%; display: flex; transition: transform 0.4s ease-in-out;">
                        @foreach($imgs as $img)
                            <div style="min-width: 100%; height: 100%; flex-shrink: 0;">
                                <img src="{{ $img }}" alt="Account Image" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                        @endforeach
                    </div>
                    
                    @if(count($imgs) > 1)
                        <!-- Navigation Buttons -->
                        <button onclick="slidePrev()" style="position: absolute; top: 50%; left: 15px; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); color: #fff; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; font-weight: 700; z-index: 10;">‹</button>
                        <button onclick="slideNext()" style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); color: #fff; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; font-weight: 700; z-index: 10;">›</button>
                        
                        <!-- Indicator Dots -->
                        <div style="position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
                            @foreach($imgs as $idx => $img)
                                <span class="slider-dot" onclick="slideTo({{ $idx }})" style="width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.3); cursor: pointer; transition: background 0.2s;"></span>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">
                        Không có hình ảnh minh họa
                    </div>
                @endif
            </div>
        </div>

        <!-- Character DB Details Card -->
        <div class="card" style="margin-bottom: 30px;">
            <h3 style="color: var(--primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
                THÔNG TIN NHÂN VẬT THỰC TẾ TRONG DATABASE
            </h3>
            
            @if($players->isEmpty())
                <p style="color: var(--text-muted); font-size: 0.9rem;">Không tìm thấy nhân vật nào liên kết với tài khoản này.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($players as $player)
                        <div style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,107,0,0.1); border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: rgba(255,107,0,0.1); border: 1px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                                @if($player->gender == 0) 🧑‍🦰 @elseif($player->gender == 1) 👽 @else 🦹 @endif
                            </div>
                            <div style="flex: 1;">
                                <strong style="color: #fff; font-size: 1rem; font-family: 'Orbitron', sans-serif;">{{ $player->name }}</strong>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 0.8rem; margin-top: 5px; color: var(--text-muted);">
                                    <div>Hành tinh: <span style="color: #fff;">@if($player->gender == 0) Trái Đất @elseif($player->gender == 1) Namếc @else Xayda @endif</span></div>
                                    <div>Sức mạnh: <span style="color: var(--secondary);">{{ number_format($player->power) }}</span></div>
                                    <div>Nhiệm vụ: <span style="color: #fff;">{{ App\Models\Player::getTaskName($player->capTT) }}</span></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Description Card -->
        <div class="card">
            <h3 style="color: var(--primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
                MÔ TẢ TỪ NGƯỜI BÁN
            </h3>
            <p style="color: #fff; font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap; font-family: 'Rajdhani', sans-serif; font-weight: 500;">{{ $listing->description }}</p>
        </div>
    </div>

    <!-- Right Column: Purchase Widget & Warranty Info -->
    <div>
        
        <!-- Purchase Widget -->
        <div class="card" style="margin-bottom: 30px; border: 1px solid var(--primary);">
            <div style="margin-bottom: 15px;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-family: 'Orbitron', sans-serif;">MÃ SỐ ACC:</span>
                <strong style="font-size: 1.2rem; color: #fff; font-family: 'Orbitron', sans-serif; display: block;">#{{ $listing->id }}</strong>
            </div>

            <div style="border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 15px 0; margin-bottom: 20px;">
                <span style="font-size: 0.85rem; color: var(--text-muted);">GIÁ BÁN THANH TOÁN:</span>
                
                @if($listing->sale_price !== null)
                    <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 5px;">
                        <span style="font-size: 1.8rem; color: var(--secondary); font-family: 'Orbitron', sans-serif; font-weight: 700;">
                            {{ number_format($listing->sale_price) }}đ
                        </span>
                        <span style="font-size: 1rem; color: var(--text-muted); text-decoration: line-through; font-family: 'Orbitron', sans-serif;">
                            {{ number_format($listing->price) }}đ
                        </span>
                    </div>
                @else
                    <div style="font-size: 1.8rem; color: var(--primary); font-family: 'Orbitron', sans-serif; font-weight: 700; margin-top: 5px;">
                        {{ number_format($listing->price) }}đ
                    </div>
                @endif
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem; margin-bottom: 20px; color: var(--text-muted);">
                <div style="display: flex; justify-content: space-between;">
                    <span>Trạng thái bán:</span>
                    @if($listing->status === 'selling')
                        <span style="color: #00e676; font-weight: 700;">ĐANG BÁN</span>
                    @elseif($listing->status === 'sold')
                        <span style="color: var(--text-muted); font-weight: 700;">ĐÃ BÁN</span>
                    @else
                        <span style="color: #ff1744; font-weight: 700;">ĐÃ HỦY BÁN</span>
                    @endif
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <span>Ngày đăng bán:</span>
                    <span style="color: #fff;">{{ $listing->created_at->format('H:i d/m/Y') }}</span>
                </div>
            </div>

            @if($listing->status === 'selling')
                @if(Auth::id() == $listing->seller_id)
                    <form id="cancel-sale-form">
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <button type="submit" id="cancel-sale-btn" class="btn btn-secondary" style="width: 100%; border: 1px solid #ff1744; color: #ff1744;">
                            HỦY ĐĂNG BÁN ACC
                        </button>
                    </form>
                @else
                    <form id="buyacc-form">
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <button type="submit" id="buyacc-btn" class="btn" style="width: 100%; font-family: 'Orbitron', sans-serif; letter-spacing: 1px;">
                            MUA NGAY (TRỪ VND WEB)
                        </button>
                    </form>
                @endif
            @else
                <button disabled class="btn btn-secondary" style="width: 100%; opacity: 0.5;">
                    TÀI KHOẢN KHÔNG KHẢ DỤNG
                </button>
            @endif
        </div>

        <!-- Warranty Policy Info Card -->
        <div class="card" style="background: rgba(18, 18, 30, 0.2); border: 1px solid rgba(255,255,255,0.05);">
            <h4 style="color: #fff; margin-bottom: 12px; font-family: 'Orbitron', sans-serif; font-size: 0.9rem;">
                CHÍNH SÁCH BẢO HÀNH GIAO DỊCH
            </h4>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.8rem; line-height: 1.4;">
                <div>
                    <strong style="color: #00e676;">🛡️ BẢO HÀNH ĐĂNG NHẬP 1 TIẾNG</strong>
                    <p style="color: var(--text-muted); margin: 3px 0 0 0;">Hoàn trả 100% tiền mua acc nếu khi mua xong bị sai tài khoản, mật khẩu hoặc lỗi đăng nhập trong 1 giờ đầu tiên.</p>
                </div>
                <div>
                    <strong style="color: var(--primary);">📸 BAO KHỚP HÌNH ẢNH</strong>
                    <p style="color: var(--text-muted); margin: 3px 0 0 0;">Nhà bán cam kết các vật phẩm, trang bị và hình ảnh mô tả đúng 100% thực tế.</p>
                </div>
                <div>
                    <strong style="color: #ffd700;">📈 BIẾN ĐỘNG BẢNG XẾP HẠNG</strong>
                    <p style="color: var(--text-muted); margin: 3px 0 0 0;">Người mua chấp nhận các chỉ số thứ hạng nhiệm vụ hoặc sức mạnh có thể chênh lệch nhỏ do biến động tự động của máy chủ game.</p>
                </div>
                <div>
                    <strong style="color: #ff5252;">⚠️ CHÍNH SÁCH KHÔNG HOÀN LẠI</strong>
                    <p style="color: var(--text-muted); margin: 3px 0 0 0;">Các trường hợp mua nhầm, không thích chơi nữa hoặc phá hỏng vật phẩm sau khi đăng nhập thành công sẽ không được hoàn tiền.</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    // Slider logic
    let currentSlide = 0;
    const container = document.getElementById('slider-container');
    const dots = document.querySelectorAll('.slider-dot');
    const slidesCount = dots.length;

    function updateSlider() {
        if (!container) return;
        container.style.transform = `translateX(-${currentSlide * 100}%)`;
        dots.forEach((dot, idx) => {
            dot.style.background = idx === currentSlide ? 'var(--primary)' : 'rgba(255,255,255,0.3)';
        });
    }

    function slideNext() {
        currentSlide = (currentSlide + 1) % slidesCount;
        updateSlider();
    }

    function slidePrev() {
        currentSlide = (currentSlide - 1 + slidesCount) % slidesCount;
        updateSlider();
    }

    function slideTo(idx) {
        currentSlide = idx;
        updateSlider();
    }

    // Initialize dots color
    updateSlider();

    document.addEventListener('DOMContentLoaded', () => {
        // Buy Handler
        const buyForm = document.getElementById('buyacc-form');
        const buyBtn = document.getElementById('buyacc-btn');
        if (buyForm) {
            buyForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!true) {
                    return;
                }

                buyBtn.disabled = true;
                buyBtn.innerText = 'ĐANG XỬ LÝ GIAO DỊCH...';

                try {
                    const response = await window.csrfFetch('{{ route("acc_sales.buy") }}', {
                        method: 'POST',
                        body: JSON.stringify({ listing_id: '{{ $listing->id }}' })
                    });
                    
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 2000);
                    } else {
                        window.showAlert(result.message, 'error');
                        buyBtn.disabled = false;
                        buyBtn.innerText = 'MUA NGAY (TRỪ VND WEB)';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối hệ thống. Vui lòng thử lại.', 'error');
                    buyBtn.disabled = false;
                    buyBtn.innerText = 'MUA NGAY (TRỪ VND WEB)';
                }
            });
        }

        // Cancel Handler (Seller)
        const cancelForm = document.getElementById('cancel-sale-form');
        const cancelBtn = document.getElementById('cancel-sale-btn');
        if (cancelForm) {
            cancelForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!true) {
                    return;
                }

                cancelBtn.disabled = true;
                cancelBtn.innerText = 'ĐANG HỦY BÁN...';

                try {
                    const response = await window.csrfFetch('{{ route("acc_sales.cancel") }}', {
                        method: 'POST',
                        body: JSON.stringify({ listing_id: '{{ $listing->id }}' })
                    });
                    
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        window.showAlert(result.message, 'error');
                        cancelBtn.disabled = false;
                        cancelBtn.innerText = 'HỦY ĐĂNG BÁN ACC';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối hệ thống.', 'error');
                    cancelBtn.disabled = false;
                    cancelBtn.innerText = 'HỦY ĐĂNG BÁN ACC';
                }
            });
        }
    });
</script>
@endsection
