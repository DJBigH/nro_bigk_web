@extends('layouts.app')

@section('title', 'Vòng Quay May Mắn - NRO BigK')

@section('content')
<div class="card" style="margin-bottom: 40px; text-align: center;">
    <h1 style="color: var(--primary); margin-bottom: 10px;">VÒNG QUAY MAY MẮN</h1>
    <p style="color: var(--text-muted);">
        Số dư của bạn: 
        <strong style="color: var(--secondary); font-family: 'Orbitron', sans-serif; font-size: 1.25rem;" id="user-balance">
            {{ number_format($account->vnd ?? 0) }} VND
        </strong>
        | Lượt quay: 
        <span style="color: #4caf50; font-family: 'Orbitron', sans-serif; font-weight: 700;">
            {{ number_format($spinPrice) }} đ / Lượt
        </span>
    </p>
</div>

<!-- Warning and Ticker Layout -->
<div class="spin-container">
    
    <!-- Spin Wheel Graphic Card -->
    <div class="card wheel-section" style="padding: 40px 20px;">
        <div style="background: rgba(255, 107, 0, 0.05); border: 1px dashed var(--primary); border-radius: 6px; padding: 15px; margin-bottom: 30px; font-size: 0.95rem; color: #ffb74d; text-align: center; width: 100%;">
            📦 <strong>Rương Đồ Web:</strong> Vật phẩm quay trúng sẽ được gửi vào <a href="{{ route('dashboard') }}" style="color: var(--secondary); text-decoration: underline; font-weight: bold;">Rương Đồ Web</a>. Bạn có thể quay thoải mái khi đang online game, sau đó thoát game để rút đồ vào hành trang!
        </div>

        <!-- Wheel -->
        <div class="wheel-wrapper">
            <div class="wheel-arrow"></div>
            <canvas id="wheel-canvas" width="400" height="400" class="wheel-canvas"></canvas>
            
            <button id="spin-btn" class="wheel-center">
                <span class="wheel-center-text">QUAY</span>
            </button>
        </div>
    </div>

    <!-- Payout Ticker Feed / List of prizes -->
    <div>
        <h2 style="color: #fff; margin-bottom: 20px; border-left: 4px solid var(--secondary); padding-left: 12px;">CHIẾN TÍCH GẦN ĐÂY</h2>
        <div class="card" style="padding: 0; margin-bottom: 30px; max-height: 250px; overflow-y: auto;">
            @if($recentSpins->isEmpty())
                <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa có lượt quay nào.</p>
            @else
                <table style="margin-top: 0;">
                    <tbody>
                        @foreach($recentSpins as $spin)
                            <tr>
                                <td style="font-weight: 700; color: #fff;">{{ $spin->account->username }}</td>
                                <td style="color: var(--text-muted);">đã trúng</td>
                                <td style="color: var(--secondary); font-weight: 700;">
                                    {{ $spin->spinItem->display_name }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <h2 style="color: #fff; margin-bottom: 20px; border-left: 4px solid var(--primary); padding-left: 12px;">DANH SÁCH PHẦN THƯỞNG</h2>
        <div class="card" style="padding: 0; max-height: 250px; overflow-y: auto;">
            <table style="margin-top: 0;">
                <thead>
                    <tr>
                        <th>Phần Thưởng</th>
                        <th>Loại</th>
                        <th style="text-align: right;">Tỉ Lệ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td style="color: #fff; font-weight: 700;">{{ $item->display_name }}</td>
                            <td>
                                @if($item->reward_type === 'item')
                                    <span class="badge badge-primary">VẬT PHẨM</span>
                                @elseif($item->reward_type === 'vnd')
                                    <span class="badge badge-success">VND CỘNG THÊM</span>
                                @else
                                    <span class="badge badge-secondary">XU GAME</span>
                                @endif
                            </td>
                            <td style="text-align: right; color: var(--secondary); font-weight: 700;">{{ $item->ratio }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Result Announcement Modal -->
<div id="result-modal" class="modal">
    <div class="modal-content" style="text-align: center;">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
        <h2 style="color: var(--secondary); margin-bottom: 15px; font-family: 'Orbitron', sans-serif;">CHÚC MỪNG CHIẾN BINH!</h2>
        <p style="font-size: 1.3rem; color: #fff; margin-bottom: 20px;" id="modal-reward-text"></p>
        <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 25px;" id="modal-note-text"></p>
        <button class="btn" onclick="closeModal()">ĐỒNG Ý</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('wheel-canvas');
        const ctx = canvas.getContext('2d');
        const spinBtn = document.getElementById('spin-btn');
        const balanceEl = document.getElementById('user-balance');
        const items = @json($items);
        
        const numSegments = items.length;
        const segmentAngle = (2 * Math.PI) / numSegments;
        let startAngle = 0;
        let isSpinning = false;
        
        // Render wheel segments
        function drawWheel() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius = canvas.width / 2 - 10;
            
            for (let i = 0; i < numSegments; i++) {
                const angle = startAngle + i * segmentAngle;
                
                // Segment block
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, angle, angle + segmentAngle);
                ctx.fillStyle = items[i].color;
                ctx.fill();
                ctx.lineWidth = 1.5;
                ctx.strokeStyle = '#020205';
                ctx.stroke();
                
                // Labels text rendering
                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(angle + segmentAngle / 2);
                
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 12px Orbitron, sans-serif';
                ctx.textAlign = 'right';
                ctx.shadowColor = 'rgba(0,0,0,0.8)';
                ctx.shadowBlur = 4;
                
                // Shorten labels if long
                let text = items[i].display_name;
                if (text.length > 18) {
                    text = text.substring(0, 16) + '..';
                }
                ctx.fillText(text, radius - 25, 5);
                ctx.restore();
            }
        }
        
        drawWheel();

        // Spin execution AJAX trigger
        spinBtn.addEventListener('click', async () => {
            if (isSpinning) return;
            
            spinBtn.disabled = true;
            isSpinning = true;

            try {
                const response = await window.csrfFetch('{{ route("spin.execute") }}', {
                    method: 'POST'
                });
                
                const result = await response.json();

                if (result.success) {
                    // Update header user balance
                    balanceEl.innerText = result.new_balance + ' VND';
                    
                    // Trigger spin animation stopping at winner_index
                    animateWheel(result.winner_index, () => {
                        // Success modal callback
                        showModal(result.message, result.reward_type);
                        isSpinning = false;
                        spinBtn.disabled = false;
                    });
                } else {
                    window.showAlert(result.message, 'error');
                    isSpinning = false;
                    spinBtn.disabled = false;
                }
            } catch (error) {
                window.showAlert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.', 'error');
                isSpinning = false;
                spinBtn.disabled = false;
            }
        });

        // Spin logic deceleration timeline
        function animateWheel(winnerIndex, callback) {
            const spinDuration = 4500; // ms
            const startTime = performance.now();
            
            // Stopping angle math (arrow at top: -Math.PI / 2)
            // Stop segment under arrow -> middle of segment (winnerIndex)
            const targetSegmentAngle = winnerIndex * segmentAngle + segmentAngle / 2;
            const targetRotation = (3 * Math.PI / 2) - targetSegmentAngle;
            
            // Multiple full spins (5-8 rotations)
            const fullSpins = 8 * 2 * Math.PI;
            const finalAngle = fullSpins + targetRotation;
            
            const initialAngle = startAngle % (2 * Math.PI);

            function update(timestamp) {
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / spinDuration, 1);
                
                // Ease out cubic deceleration formula
                const easeOut = 1 - Math.pow(1 - progress, 3);
                
                startAngle = initialAngle + (finalAngle - initialAngle) * easeOut;
                drawWheel();
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    startAngle = startAngle % (2 * Math.PI);
                    drawWheel();
                    callback();
                }
            }
            
            requestAnimationFrame(update);
        }
    });

    function showModal(message, type) {
        const modal = document.getElementById('result-modal');
        const text = document.getElementById('modal-reward-text');
        const note = document.getElementById('modal-note-text');
        
        text.innerText = message;
        if (type === 'item') {
            note.innerText = 'Vật phẩm đã được gửi vào Rương Đồ Web. Vui lòng vào trang Hồ Sơ để rút vật phẩm vào game.';
        } else {
            note.innerText = 'Phần thưởng số dư đã cộng trực tiếp vào tài khoản web của bạn.';
        }
        
        modal.classList.add('show');
    }

    function closeModal() {
        document.getElementById('result-modal').classList.remove('show');
        // Refresh local spins list
        window.location.reload();
    }
</script>
@endsection
