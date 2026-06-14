@extends('admin.layout')

@section('title', 'Lịch Sử Nạp - Admin NRO BigK')

@section('admin_content')
<div class="card" style="margin-bottom: 30px;">
    <h2 style="color: var(--primary);">LỊCH SỬ NẠP TOÀN SERVER</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Danh sách ghi nhận các giao dịch nạp tiền tích lũy của người chơi.</p>
</div>

<!-- Recharges Log Table -->
<div class="card" style="padding: 0;">
    @if($transactions->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa có lịch sử giao dịch nạp tiền nào.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tài Khoản</th>
                    <th>Cổng Nạp</th>
                    <th>Mã Giao Dịch</th>
                    <th>Số Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Thời Gian</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $tx->id }}</td>
                        <td style="font-weight: 700; color: #fff;">
                            {{ $tx->account->username ?? 'Không xác định' }}
                        </td>
                        <td style="text-transform: uppercase; font-family: 'Orbitron', sans-serif; font-size: 0.9rem;">
                            {{ $tx->gateway }}
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">
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
                                <span class="badge badge-secondary">Chờ duyệt</span>
                            @endif
                        </td>
                        <td style="font-size: 0.95rem;">
                            {{ $tx->payment_time ? $tx->payment_time->format('d/m/Y H:i:s') : ($tx->created_at ? $tx->created_at->format('d/m/Y H:i:s') : '') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
