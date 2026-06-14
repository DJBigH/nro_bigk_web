@extends('layouts.app')

@section('title', 'Bảng Xếp Hạng - NRO BigK')

@section('content')
<div class="card" style="margin-bottom: 40px; text-align: center;">
    <h1 style="color: var(--primary); margin-bottom: 10px;">BẢNG XẾP HẠNG ANH HÙNG</h1>
    <p style="color: var(--text-muted);">Đua top vinh danh các chiến binh mạnh mẽ nhất toàn server Ngọc Rồng BigK.</p>
</div>

<!-- Tab Navigation -->
<div class="tab-headers">
    <a href="{{ route('ranking', ['type' => 'power']) }}" class="tab-header {{ $type === 'power' ? 'active' : '' }}">TOP SỨC MẠNH</a>
    <a href="{{ route('ranking', ['type' => 'task']) }}" class="tab-header {{ $type === 'task' ? 'active' : '' }}">TOP NHIỆM VỤ</a>
    <a href="{{ route('ranking', ['type' => 'recharge']) }}" class="tab-header {{ $type === 'recharge' ? 'active' : '' }}">TOP CAO THỦ NẠP</a>
</div>

@if(empty($rankings))
    <div class="card" style="text-align: center; padding: 40px 0;">
        <p style="color: var(--text-muted);">Chưa có dữ liệu xếp hạng cho danh mục này.</p>
    </div>
@else
    <!-- Podium for Top 3 -->
    @php
        $top3 = array_slice($rankings, 0, 3);
        $rest = array_slice($rankings, 3);
        
        // Reorder for visual podium: 2nd place on left, 1st in center, 3rd on right
        $podium = [];
        if (isset($top3[1])) $podium[] = ['rank' => 2, 'data' => $top3[1]];
        if (isset($top3[0])) $podium[] = ['rank' => 1, 'data' => $top3[0]];
        if (isset($top3[2])) $podium[] = ['rank' => 3, 'data' => $top3[2]];
    @endphp

    @if(count($top3) >= 1)
        <div class="top-ranks">
            @foreach($podium as $pos)
                <div class="rank-podium rank-{{ $pos['rank'] }}">
                    <div class="rank-num">#{{ $pos['rank'] }}</div>
                    
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.05); border: 2px solid {{ $pos['rank'] == 1 ? 'var(--secondary)' : ($pos['rank'] == 2 ? '#ccd0d4' : '#cd7f32') }}; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin-bottom: 10px;">
                        @if(($pos['data']->gender ?? 0) == 0) 🧑‍🦰 @elseif(($pos['data']->gender ?? 0) == 1) 👽 @else 🦹 @endif
                    </div>
                    
                    <h3 style="font-size: 1.25rem; color: #fff; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                        {{ $pos['data']->name }}
                    </h3>
                    
                    <div style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: {{ $pos['rank'] == 1 ? 'var(--secondary)' : 'var(--primary)' }};">
                        @if($type === 'power')
                            {{ number_format($pos['data']->value) }} SM
                        @elseif($type === 'task')
                            {{ App\Models\Player::getTaskName($pos['data']->value) }}
                        @elseif($type === 'recharge')
                            {{ number_format($pos['data']->value) }} đ
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Rankings Table for 4th to 20th -->
    @if(count($rest) > 0 || count($top3) < 3)
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Hạng</th>
                        <th>Chiến Binh</th>
                        <th>Hành Tinh</th>
                        <th style="text-align: right;">
                            @if($type === 'power') Sức Mạnh @elseif($type === 'task') Nhiệm Vụ @else Tích Lũy Nạp @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // If podium was displayed, start rest ranks from 4. Otherwise, display all starting from 1.
                        $startRank = count($top3) >= 1 ? 4 : 1;
                        $displayList = count($top3) >= 1 ? $rest : $rankings;
                    @endphp
                    @foreach($displayList as $index => $row)
                        <tr>
                            <td style="font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--primary);">
                                #{{ $startRank + $index }}
                            </td>
                            <td style="color: #fff; font-weight: 700;">
                                <span style="margin-right: 8px;">
                                    @if(($row->gender ?? 0) == 0) 🧑‍🦰 @elseif(($row->gender ?? 0) == 1) 👽 @else 🦹 @endif
                                </span>
                                {{ $row->name }}
                            </td>
                            <td>
                                @if(isset($row->gender))
                                    @if($row->gender == 0) Trái Đất @elseif($row->gender == 1) Namếc @else Xayda @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: right; font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--secondary);">
                                @if($type === 'power')
                                    {{ number_format($row->value) }}
                                @elseif($type === 'task')
                                    {{ App\Models\Player::getTaskName($row->value) }}
                                @elseif($type === 'recharge')
                                    {{ number_format($row->value) }}đ
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif
@endsection
