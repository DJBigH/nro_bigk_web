@extends('admin.layout')

@section('title', 'Tra Cứu Vật Phẩm - Admin NRO BigK')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <h2 style="color: var(--primary);">TRA CỨU VẬT PHẨM GAME</h2>
    
    <form action="{{ route('admin.items.lookup') }}" method="GET" style="display: flex; gap: 10px; min-width: 320px;">
        <input type="text" name="search" value="{{ $search }}" class="form-input" style="padding: 10px 15px; font-size: 0.95rem; margin-bottom: 0;" placeholder="Nhập ID hoặc tên vật phẩm...">
        <button type="submit" class="btn" style="padding: 10px 20px;">TÌM KIẾM</button>
        @if($search)
            <a href="{{ route('admin.items.lookup') }}" class="btn btn-secondary" style="padding: 10px 15px; font-size: 0.95rem; text-decoration: none; display: flex; align-items: center; justify-content: center;">HỦY</a>
        @endif
    </form>
</div>

<div class="card" style="padding: 0;">
    @if($items->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Không tìm thấy vật phẩm nào.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th style="width: 110px;">ID Template</th>
                    <th style="width: 90px; text-align: center;">Ảnh/Icon</th>
                    <th>Tên Vật Phẩm</th>
                    <th>Mô Tả</th>
                    <th>Loại</th>
                    <th>Yêu Cầu Sức Mạnh</th>
                    <th>Giới Tính</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary); font-size: 1.05rem;">
                            {{ $item->id }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span style="font-size: 1.4rem;" title="Icon ID: {{ $item->icon_id }}">📦</span>
                        </td>
                        <td style="font-weight: 700; color: #fff;">{{ $item->NAME }}</td>
                        <td style="color: var(--text-muted); font-size: 0.95rem; max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $item->description }}">
                            {{ $item->description ?? '-' }}
                        </td>
                        <td style="font-size: 0.9rem;">
                            @if($item->TYPE == 5)
                                <span class="badge badge-primary">Cải trang</span>
                            @elseif($item->TYPE == 12)
                                <span class="badge badge-success">Thỏi vàng</span>
                            @else
                                <span class="badge badge-secondary">Loại {{ $item->TYPE }}</span>
                            @endif
                        </td>
                        <td style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem; color: var(--primary); font-weight: bold;">
                            {{ number_format($item->power_require) }}
                        </td>
                        <td style="font-size: 0.9rem; color: #fff;">
                            @if($item->gender == 0)
                                Trái Đất
                            @elseif($item->gender == 1)
                                Namec
                            @elseif($item->gender == 2)
                                Xayda
                            @else
                                Không giới hạn
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $items->appends(['search' => $search])->links() }}
        </div>
    @endif
</div>
@endsection
