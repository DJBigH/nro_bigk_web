@extends('admin.layout')

@section('title', 'Cấu Hình Tỉ Lệ Đập Đồ & Pha Lê Hóa - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #e040fb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #e040fb; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🔨 CẤU HÌNH TỈ LỆ ĐẬP ĐỒ & PHA LÊ HÓA REALTIME
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Điều chỉnh trực tiếp % thành công khi ép Sao Pha Lê tại Bà Hạt Mít. Áp dụng thẳng vào RAM máy chủ tức thời không cần bảo trì!
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI
        </button>
    </div>
</div>

<!-- Preset Quick Buttons -->
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(224, 64, 251, 0.2);">
    <h3 style="color: #e040fb; font-size: 1.05rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        ⚡ CHỌN NHANH CẤU HÌNH MẪU (PRESETS)
    </h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="applyPreset('default')" style="font-size: 0.85rem; padding: 8px 16px;">
            ⚙️ Mặc Định Gốc (Chuẩn)
        </button>
        <button type="button" class="btn btn-secondary" onclick="applyPreset('easy')" style="font-size: 0.85rem; padding: 8px 16px; border-color: #00e5ff; color: #00e5ff;">
            ✨ Dễ Thở (x2 Tỉ Lệ)
        </button>
        <button type="button" class="btn btn-secondary" onclick="applyPreset('fun')" style="font-size: 0.85rem; padding: 8px 16px; border-color: #ff9800; color: #ff9800;">
            🔥 Cày Cuốc Vui Vẻ (40-100%)
        </button>
        <button type="button" class="btn btn-secondary" onclick="applyPreset('max')" style="font-size: 0.85rem; padding: 8px 16px; border-color: #4caf50; color: #4caf50;">
            💯 Test 100% (Không Thất Bại)
        </button>
    </div>
</div>

<!-- Rates Form -->
<form id="combineRateForm">
    @csrf
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
        @php
            $starLabels = [
                '1 Sao (Pha Lê Hóa 1)',
                '2 Sao (Pha Lê Hóa 2)',
                '3 Sao (Pha Lê Hóa 3)',
                '4 Sao (Pha Lê Hóa 4)',
                '5 Sao (Pha Lê Hóa 5)',
                '6 Sao (Pha Lê Hóa 6)',
                '7 Sao (Pha Lê Hóa 7)',
                '8 Sao (Pha Lê Hóa 8 - Đỉnh Cao)',
            ];
            $starColors = [
                '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722', '#e040fb'
            ];
        @endphp

        @for($i = 0; $i < 8; $i++)
            @php
                $val = isset($rates[$i]) ? (float)$rates[$i] : 50.0;
                $color = $starColors[$i];
            @endphp
            <div class="card" style="border: 1px solid rgba(255,255,255,0.08); background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, rgba(20,24,35,0.95) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.3rem;">⭐</span>
                        <strong style="color: {{ $color }}; font-size: 1rem;">{{ $starLabels[$i] }}</strong>
                    </div>
                    <span id="rateLabel_{{ $i }}" style="font-family: 'Orbitron', sans-serif; font-size: 1.2rem; font-weight: 700; color: {{ $color }};">
                        {{ $val }}%
                    </span>
                </div>

                <div style="margin-bottom: 12px;">
                    <input type="range" 
                           id="range_{{ $i }}" 
                           min="0.1" 
                           max="100" 
                           step="0.1" 
                           value="{{ $val }}" 
                           style="width: 100%; accent-color: {{ $color }}; cursor: pointer;"
                           oninput="syncRate({{ $i }}, this.value)">
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Giá trị %:</span>
                    <input type="number" 
                           name="rates[]" 
                           id="input_{{ $i }}" 
                           class="form-input" 
                           min="0.1" 
                           max="100" 
                           step="0.1" 
                           value="{{ $val }}" 
                           style="margin-bottom: 0; padding: 6px 10px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem; width: 100px; text-align: right;"
                           oninput="syncRateInput({{ $i }}, this.value)">
                    <span style="font-size: 0.85rem; color: #fff;">%</span>
                </div>
            </div>
        @endfor
    </div>

    <!-- Save Action -->
    <div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="color: var(--text-muted); font-size: 0.9rem;">
            💡 <strong style="color: #fff;">Lưu ý:</strong> Sau khi nhấn Lưu, máy chủ Java sẽ cập nhật ngay lập tức vào mảng RAM. Người chơi trong game đập đồ tiếp theo sẽ nhận ngay tỉ lệ mới.
        </div>
        <button type="submit" class="btn" style="background: linear-gradient(135deg, #e040fb 0%, #7b1fa2 100%); font-weight: 700; padding: 12px 30px; font-size: 1rem; box-shadow: 0 0 15px rgba(224,64,251,0.4);">
            💾 LƯU CẤU HÌNH TỈ LỆ REALTIME
        </button>
    </div>
</form>

<script>
const presets = {
    'default': [80.0, 50.0, 20.0, 10.0, 5.0, 2.0, 1.0, 0.5],
    'easy':    [95.0, 80.0, 50.0, 30.0, 15.0, 8.0, 4.0, 2.0],
    'fun':     [100.0, 90.0, 80.0, 70.0, 60.0, 50.0, 40.0, 30.0],
    'max':     [100.0, 100.0, 100.0, 100.0, 100.0, 100.0, 100.0, 100.0]
};

function syncRate(idx, val) {
    document.getElementById('input_' + idx).value = val;
    document.getElementById('rateLabel_' + idx).innerText = val + '%';
}

function syncRateInput(idx, val) {
    if (val < 0.1) val = 0.1;
    if (val > 100) val = 100;
    document.getElementById('range_' + idx).value = val;
    document.getElementById('rateLabel_' + idx).innerText = val + '%';
}

function applyPreset(name) {
    const list = presets[name];
    if (!list) return;
    for (let i = 0; i < 8; i++) {
        const val = list[i];
        document.getElementById('range_' + i).value = val;
        document.getElementById('input_' + i).value = val;
        document.getElementById('rateLabel_' + i).innerText = val + '%';
    }
}

document.getElementById('combineRateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const rates = [];
    for (let i = 0; i < 8; i++) {
        rates.push(parseFloat(document.getElementById('input_' + i).value));
    }

    fetch("{{ route('admin.combine-rate.update') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ rates: rates })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message || 'Cập nhật tỉ lệ đập đồ realtime thành công!');
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể lưu tỉ lệ!'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối tới Game Server!');
    });
});
</script>
@endsection
