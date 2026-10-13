@extends('admin.layout')

@section('title', 'Visual Map Editor 2D - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 20px; border-left: 4px solid #00e5ff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #00e5ff; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🗺️ TRÌNH BIÊN TẬP BẢN ĐỒ 2D TRỰC QUAN (VISUAL MAP EDITOR)
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Trực quan hóa vị trí NPC, Quái vật và Cổng dịch chuyển trên không gian 2D. Kéo thả chuột để dời tọa độ và lưu thẳng vào Database!
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-secondary" onclick="reloadMapData()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 TẢI LẠI MAP
        </button>
    </div>
</div>

<!-- Map Selector & Controls Bar -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
        <label style="color: #fff; font-weight: 600; font-size: 0.9rem; white-space: nowrap;">Chọn Bản Đồ:</label>
        <select id="mapSelect" class="form-input" style="margin-bottom: 0; padding: 8px 12px; font-weight: 600;" onchange="loadSelectedMap()">
            @foreach($maps as $m)
                <option value="{{ $m->id }}">Map {{ $m->id }}: {{ $m->name }}</option>
            @endforeach
        </select>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="font-size: 0.85rem; color: #888;">Chuột: <strong>Click & Kéo</strong> để dời vị trí</span>
        <button type="button" class="btn" style="background: #4caf50; color: #fff; font-weight: 700; padding: 10px 24px; box-shadow: 0 0 15px rgba(76,175,80,0.3);" onclick="saveMapData()">
            💾 LƯU TỌA ĐỘ VÀO DATABASE
        </button>
    </div>
</div>

<!-- 2D Canvas Workspace -->
<div class="card" style="margin-bottom: 20px; padding: 15px; background: #070a12; border: 1px solid rgba(0,229,255,0.2); overflow-x: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div style="display: flex; gap: 15px; font-size: 0.82rem;">
            <span style="color: #ffd700;">● 🧙 NPC (Màu Vàng)</span>
            <span style="color: #ff5252;">● 👾 Quái Mob (Màu Đỏ)</span>
            <span style="color: #00e5ff;">● 🌀 Cổng Dịch Chuyển (Màu Xanh)</span>
        </div>
        <div id="hoverCoords" style="font-family: 'Orbitron', sans-serif; font-size: 0.85rem; color: #00e5ff;">
            X: 0 | Y: 0
        </div>
    </div>

    <canvas id="mapCanvas" width="1400" height="480" style="background: linear-gradient(180deg, #0d1322 0%, #151f35 80%, #202d1d 85%, #141c12 100%); border: 1px solid #1a263d; border-radius: 6px; cursor: crosshair; display: block; width: 100%; max-width: 1400px; height: auto;">
    </canvas>
</div>

<!-- Entities Inspector & Manager -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- NPCs Table -->
    <div class="card" style="padding: 15px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 8px;">
            <h3 style="color: #ffd700; font-size: 1rem; margin: 0; display: flex; align-items: center; gap: 6px;">
                🧙 DANH SÁCH NPC TRÊN MAP (<span id="npcCount">0</span>)
            </h3>
            <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.75rem;" onclick="addNpcPrompt()">
                ➕ Thêm NPC
            </button>
        </div>
        <div style="max-height: 250px; overflow-y: auto;">
            <table style="margin-top: 0; width: 100%; font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>NPC ID</th>
                        <th>Tọa Độ X</th>
                        <th>Tọa Độ Y</th>
                        <th style="text-align: right;">Xóa</th>
                    </tr>
                </thead>
                <tbody id="npcTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobs Table -->
    <div class="card" style="padding: 15px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 8px;">
            <h3 style="color: #ff5252; font-size: 1rem; margin: 0; display: flex; align-items: center; gap: 6px;">
                👾 DANH SÁCH QUÁI VẬT (<span id="mobCount">0</span>)
            </h3>
            <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.75rem;" onclick="addMobPrompt()">
                ➕ Thêm Quái
            </button>
        </div>
        <div style="max-height: 250px; overflow-y: auto;">
            <table style="margin-top: 0; width: 100%; font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>Mob ID</th>
                        <th>Cấp / HP</th>
                        <th>Tọa Độ (X, Y)</th>
                        <th style="text-align: right;">Xóa</th>
                    </tr>
                </thead>
                <tbody id="mobTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('mapCanvas');
const ctx = canvas.getContext('2d');

let currentMapData = {
    id: 0,
    npcs: [],
    mobs: [],
    waypoints: []
};

let draggingEntity = null;
let dragType = null; // 'npc' or 'mob'

function loadSelectedMap() {
    const mapId = document.getElementById('mapSelect').value;
    fetch(`/admin/map-editor/data?map_id=${mapId}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                currentMapData = res.map;
                renderCanvas();
                renderTables();
            } else {
                alert(res.message || 'Lỗi tải dữ liệu map!');
            }
        })
        .catch(() => alert('Lỗi kết nối tới Server!'));
}

function reloadMapData() {
    loadSelectedMap();
}

function renderCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Vẽ lưới tọa độ
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
    ctx.lineWidth = 1;
    for (let x = 0; x < canvas.width; x += 100) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
    }
    for (let y = 0; y < canvas.height; y += 50) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
    }

    // Vẽ mặt đất chuẩn (Y = 430)
    ctx.strokeStyle = '#4caf50';
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.moveTo(0, 430);
    ctx.lineTo(canvas.width, 430);
    ctx.stroke();

    ctx.fillStyle = 'rgba(76, 175, 80, 0.15)';
    ctx.fillRect(0, 430, canvas.width, canvas.height - 430);

    // 1. Vẽ Waypoints
    if (currentMapData.waypoints) {
        currentMapData.waypoints.forEach(w => {
            const wx = (w.minX + w.maxX) / 2;
            const wy = (w.minY + w.maxY) / 2;
            ctx.fillStyle = 'rgba(0, 229, 255, 0.2)';
            ctx.strokeStyle = '#00e5ff';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(wx, wy, 24, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#00e5ff';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('🌀 ' + w.name, wx, wy - 30);
            ctx.fillText(`-> Map ${w.goMap}`, wx, wy + 38);
        });
    }

    // 2. Vẽ Mobs
    if (currentMapData.mobs) {
        currentMapData.mobs.forEach((m, idx) => {
            ctx.fillStyle = '#ff5252';
            ctx.beginPath();
            ctx.arc(m.x, m.y - 12, 14, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = '#fff';
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('👾 #' + m.id, m.x, m.y - 30);
            ctx.fillStyle = '#ff8a80';
            ctx.font = '10px monospace';
            ctx.fillText(`(${m.x}, ${m.y})`, m.x, m.y + 16);
        });
    }

    // 3. Vẽ NPCs
    if (currentMapData.npcs) {
        currentMapData.npcs.forEach((n, idx) => {
            const nx = n[1];
            const ny = n[2];
            ctx.fillStyle = '#ffd700';
            ctx.beginPath();
            ctx.arc(nx, ny - 14, 16, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = '#fff';
            ctx.font = 'bold 12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('🧙 NPC ' + n[0], nx, ny - 34);
            ctx.fillStyle = '#ffe57f';
            ctx.font = '10px monospace';
            ctx.fillText(`(${nx}, ${ny})`, nx, ny + 18);
        });
    }
}

function renderTables() {
    // NPCs
    document.getElementById('npcCount').innerText = currentMapData.npcs.length;
    const nBody = document.getElementById('npcTableBody');
    nBody.innerHTML = '';
    currentMapData.npcs.forEach((n, idx) => {
        nBody.innerHTML += `
            <tr>
                <td style="font-weight: 700; color: #ffd700;">#${n[0]}</td>
                <td><input type="number" class="form-input" style="padding: 2px 6px; font-size: 0.8rem; width: 75px; margin-bottom: 0;" value="${n[1]}" onchange="updateNpcCoord(${idx}, 1, this.value)"></td>
                <td><input type="number" class="form-input" style="padding: 2px 6px; font-size: 0.8rem; width: 75px; margin-bottom: 0;" value="${n[2]}" onchange="updateNpcCoord(${idx}, 2, this.value)"></td>
                <td style="text-align: right;"><button class="btn btn-danger" style="padding: 2px 6px; font-size: 0.75rem;" onclick="removeNpc(${idx})">×</button></td>
            </tr>
        `;
    });

    // Mobs
    document.getElementById('mobCount').innerText = currentMapData.mobs.length;
    const mBody = document.getElementById('mobTableBody');
    mBody.innerHTML = '';
    currentMapData.mobs.forEach((m, idx) => {
        mBody.innerHTML += `
            <tr>
                <td style="font-weight: 700; color: #ff5252;">#${m.id}</td>
                <td>Lv${m.level} / ${m.hp}HP</td>
                <td>
                    <input type="number" class="form-input" style="padding: 2px 6px; font-size: 0.8rem; width: 65px; margin-bottom: 0; display: inline;" value="${m.x}" onchange="updateMobCoord(${idx}, 'x', this.value)">
                    <input type="number" class="form-input" style="padding: 2px 6px; font-size: 0.8rem; width: 65px; margin-bottom: 0; display: inline;" value="${m.y}" onchange="updateMobCoord(${idx}, 'y', this.value)">
                </td>
                <td style="text-align: right;"><button class="btn btn-danger" style="padding: 2px 6px; font-size: 0.75rem;" onclick="removeMob(${idx})">×</button></td>
            </tr>
        `;
    });
}

function updateNpcCoord(idx, pos, val) {
    currentMapData.npcs[idx][pos] = parseInt(val) || 0;
    renderCanvas();
}

function updateMobCoord(idx, prop, val) {
    currentMapData.mobs[idx][prop] = parseInt(val) || 0;
    renderCanvas();
}

function removeNpc(idx) {
    currentMapData.npcs.splice(idx, 1);
    renderCanvas();
    renderTables();
}

function removeMob(idx) {
    currentMapData.mobs.splice(idx, 1);
    renderCanvas();
    renderTables();
}

function addNpcPrompt() {
    const id = prompt('Nhập ID mẫu NPC (Template ID):', '1');
    if (!id) return;
    currentMapData.npcs.push([parseInt(id), 500, 432]);
    renderCanvas();
    renderTables();
}

function addMobPrompt() {
    const id = prompt('Nhập ID quái vật (Mob ID):', '0');
    if (!id) return;
    currentMapData.mobs.push({
        id: parseInt(id),
        level: 1,
        hp: 100,
        x: 600,
        y: 432
    });
    renderCanvas();
    renderTables();
}

// Drag & Drop Handling on Canvas
canvas.addEventListener('mousedown', function(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;

    // Check NPC click
    for (let i = 0; i < currentMapData.npcs.length; i++) {
        const n = currentMapData.npcs[i];
        const dist = Math.hypot(n[1] - mouseX, (n[2] - 14) - mouseY);
        if (dist <= 20) {
            draggingEntity = i;
            dragType = 'npc';
            return;
        }
    }

    // Check Mob click
    for (let i = 0; i < currentMapData.mobs.length; i++) {
        const m = currentMapData.mobs[i];
        const dist = Math.hypot(m.x - mouseX, (m.y - 12) - mouseY);
        if (dist <= 20) {
            draggingEntity = i;
            dragType = 'mob';
            return;
        }
    }
});

canvas.addEventListener('mousemove', function(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = Math.round((e.clientX - rect.left) * scaleX);
    const mouseY = Math.round((e.clientY - rect.top) * scaleY);

    document.getElementById('hoverCoords').innerText = `X: ${mouseX} | Y: ${mouseY}`;

    if (draggingEntity !== null) {
        if (dragType === 'npc') {
            currentMapData.npcs[draggingEntity][1] = mouseX;
            currentMapData.npcs[draggingEntity][2] = mouseY;
        } else if (dragType === 'mob') {
            currentMapData.mobs[draggingEntity].x = mouseX;
            currentMapData.mobs[draggingEntity].y = mouseY;
        }
        renderCanvas();
    }
});

canvas.addEventListener('mouseup', function() {
    if (draggingEntity !== null) {
        draggingEntity = null;
        dragType = null;
        renderTables();
    }
});

function saveMapData() {
    if (!confirm(`Bạn có chắc muốn lưu lại tọa độ thực thể Map #${currentMapData.id} vào Database?`)) return;

    fetch("{{ route('admin.map_editor.save') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            map_id: currentMapData.id,
            npcs: currentMapData.npcs,
            mobs: currentMapData.mobs
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message || 'Lưu bản đồ thành công!');
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể lưu!'));
        }
    })
    .catch(() => alert('Lỗi kết nối tới Server!'));
}

// Khởi chạy khi tải trang
document.addEventListener('DOMContentLoaded', loadSelectedMap);
</script>
@endsection
