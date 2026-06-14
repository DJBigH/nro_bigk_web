@extends('admin.layout')

@section('title', 'Quản Trị Thống Kê - NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 30px;">
    <h2 style="color: var(--primary);">THỐNG KÊ HỆ THỐNG</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Tổng quan dữ liệu và hoạt động trên máy chủ Ngọc Rồng BigK.</p>
</div>

<!-- Stats Grid -->
<div class="summary-grid">
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Thành Viên</div>
        <div class="summary-val">{{ number_format($stats['total_users']) }}</div>
    </div>
    
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Tổng Doanh Thu</div>
        <div class="summary-val" style="color: var(--secondary);">{{ number_format($stats['total_revenue']) }}đ</div>
    </div>
    
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Lượt Mua Shop</div>
        <div class="summary-val" style="color: #00bcd4;">{{ number_format($stats['total_purchases']) }}</div>
    </div>
    
    <div class="card summary-card">
        <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Đồ Đang Bán Shop</div>
        <div class="summary-val" style="color: #4caf50;">{{ number_format($stats['active_shop_items']) }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    
    <!-- Code Stats -->
    <div class="card">
        <h3 style="color: #fff; margin-bottom: 15px; border-bottom: 1px solid rgba(255,107,0,0.1); padding-bottom: 8px;">GIFT CODES</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <span style="color: var(--text-muted);">Số lượng mã quà tặng:</span>
            <strong style="color: #fff; font-family: 'Orbitron', sans-serif;">{{ $stats['total_giftcodes'] }}</strong>
        </div>
        <a href="{{ route('admin.giftcodes') }}" class="btn" style="width: 100%; font-size: 0.9rem; padding: 10px;">QUẢN LÝ CODES</a>
    </div>

    <!-- Spin stats -->
    <div class="card">
        <h3 style="color: #fff; margin-bottom: 15px; border-bottom: 1px solid rgba(255,107,0,0.1); padding-bottom: 8px;">VÒNG QUAY MAY MẮN</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <span style="color: var(--text-muted);">Số ô giải thưởng:</span>
            <strong style="color: #fff; font-family: 'Orbitron', sans-serif;">{{ $stats['spin_wheel_items'] }} ô</strong>
        </div>
        <a href="{{ route('admin.spin') }}" class="btn btn-secondary" style="width: 100%; font-size: 0.9rem; padding: 10px;">QUẢN LÝ VÒNG QUAY</a>
    </div>

</div>
@endsection
