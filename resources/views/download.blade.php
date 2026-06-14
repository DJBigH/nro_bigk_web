@extends('layouts.app')

@section('title', 'Tải Game - NRO BigK')

@section('content')
<div class="card" style="margin-bottom: 40px; text-align: center;">
    <h1 style="color: var(--primary); margin-bottom: 10px;">TẢI CLIENT TRÒ CHƠI</h1>
    <p style="color: var(--text-muted);">Hãy chọn phiên bản tương ứng với thiết bị của bạn để tham gia thế giới Ngọc Rồng BigK.</p>
</div>

@if($downloads->isEmpty())
    <!-- Default Mock Downloads if admin hasn't created any yet -->
    <div class="item-grid">
        <div class="card" style="text-align: center; border-color: rgba(255,107,0,0.15);">
            <div style="font-size: 3rem; margin-bottom: 15px;">💻</div>
            <h3 style="margin-bottom: 8px;">PHIÊN BẢN PC WINDOWS</h3>
            <span class="badge badge-primary" style="margin-bottom: 15px;">Phiên Bản 1.0.0</span>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 20px;">Dung lượng: ~25 MB. Yêu cầu Java JRE 17 trở lên cài đặt.</p>
            <a href="#" class="btn" style="width: 100%;">TẢI BẢN PC (.ZIP)</a>
        </div>

        <div class="card" style="text-align: center; border-color: rgba(255,107,0,0.15);">
            <div style="font-size: 3rem; margin-bottom: 15px;">🤖</div>
            <h3 style="margin-bottom: 8px;">BẢN ANDROID (APK)</h3>
            <span class="badge badge-primary" style="margin-bottom: 15px;">Phiên Bản 1.0.0</span>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 20px;">Dung lượng: ~20 MB. Tải trực tiếp file APK cài đặt lên điện thoại.</p>
            <a href="#" class="btn btn-secondary" style="width: 100%;">TẢI FILE APK</a>
        </div>
    </div>
@else
    <div class="item-grid">
        @foreach($downloads as $file)
            <div class="card" style="text-align: center; border-color: rgba(255,107,0,0.25);">
                <div style="font-size: 3rem; margin-bottom: 15px;">
                    @if(Str::contains(strtolower($file->name), ['pc', 'win']))
                        💻
                    @elseif(Str::contains(strtolower($file->name), ['android', 'apk']))
                        🤖
                    @elseif(Str::contains(strtolower($file->name), ['ios', 'iphone', 'mac']))
                        🍎
                    @else
                        🎮
                    @endif
                </div>
                
                <h3 style="margin-bottom: 8px; text-transform: uppercase;">{{ $file->name }}</h3>
                
                @if($file->version)
                    <span class="badge badge-primary" style="margin-bottom: 15px;">Version {{ $file->version }}</span>
                @endif
                
                <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 20px;">
                    Dung lượng: {{ $file->size ?? 'N/A' }}<br>
                    Tải về nhanh chóng từ máy chủ phân phối BigK.
                </p>
                
                <a href="{{ $file->url }}" target="_blank" class="btn" style="width: 100%;">
                    TẢI NGAY
                </a>
            </div>
        @endforeach
    </div>
@endif

<div class="card" style="margin-top: 40px;">
    <h3 style="color: var(--secondary); margin-bottom: 15px;">HƯỚNG DẪN CÀI ĐẶT NHANH</h3>
    <ul style="padding-left: 20px; line-height: 1.8; color: var(--text-muted); font-size: 1.05rem;">
        <li><strong>Dành cho máy tính PC:</strong> Hãy tải file .ZIP về, giải nén ra thư mục bất kỳ. Chạy file <code style="color: #fff; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px;">run.bat</code> hoặc mở Java chạy file <code style="color: #fff; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px;">dist/CBRO.jar</code> để chơi.</li>
        <li><strong>Dành cho điện thoại Android:</strong> Cần tải file .APK về máy, đồng ý cài đặt ứng dụng từ nguồn không xác định để tiến hành cài đặt game.</li>
        <li>Nếu gặp bất kỳ khó khăn nào trong quá trình tải và cài đặt game, vui lòng liên hệ Admin để được hỗ trợ kỹ thuật nhanh nhất.</li>
    </ul>
</div>
@endsection
