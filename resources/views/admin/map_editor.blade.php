@extends('admin.layout')

@section('title', 'Visual Map Editor 2D - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 20px; border-left: 4px solid #00e5ff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #00e5ff; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; margin: 0 0 5px 0;">
            🗺️ TRÌNH BIÊN TẬP BẢN ĐỒ 2D TRỰC QUAN (VISUAL MAP EDITOR)
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
            Hiển thị trực quan Sprite Avatar NPC thật trong game, Quái vật có Thanh Máu (HP Bar), Cổng dịch chuyển và Mặt đất 2D Platformer. Kéo thả chuột để điều chỉnh tọa độ và lưu trực tiếp vào Database!
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
    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 340px; flex-wrap: wrap;">
        <label style="color: #fff; font-weight: 700; font-size: 0.9rem; white-space: nowrap; color: #00e5ff;">🗺️ Bản Đồ:</label>
        <div style="position: relative; width: 220px;">
            <input type="text" id="mapSearchInput" class="form-input" placeholder="🔍 Tìm ID hoặc tên map..." oninput="filterMapOptions()" onkeydown="if(event.key==='Enter'){event.preventDefault();filterMapOptions();loadSelectedMap();}" style="margin-bottom: 0; padding: 7px 12px; font-size: 0.85rem; width: 100%;">
        </div>
        <select id="mapSelect" class="form-input" style="margin-bottom: 0; padding: 7px 12px; font-weight: 600; max-width: 320px; flex: 1;" onchange="loadSelectedMap()">
            @foreach($maps as $m)
                <option value="{{ $m->id }}">Map {{ $m->id }}: {{ $m->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-secondary" style="padding: 7px 12px; font-size: 0.82rem;" onclick="reloadMapData()" title="Tải lại bản đồ">
            🔄
        </button>
    </div>

    <div style="display: flex; gap: 12px; align-items: center;">
        <span style="font-size: 0.85rem; color: #aaa;">💡 <strong>Kéo & thả chuột</strong> lên NPC/Quái để dời vị trí</span>
        <button type="button" class="btn" style="background: #00c853; color: #fff; font-weight: 700; padding: 10px 24px; box-shadow: 0 0 15px rgba(0,200,83,0.35); border: none;" onclick="saveMapData()">
            💾 LƯU TỌA ĐỘ VÀO DATABASE
        </button>
    </div>
</div>

<!-- 2D Canvas Workspace -->
<div class="card" style="margin-bottom: 20px; padding: 15px; background: #070c18; border: 1px solid rgba(0,229,255,0.25);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; gap: 18px; font-size: 0.85rem; align-items: center;">
            <span style="color: #ffd700; font-weight: 600;">🧙 NPC (Avatar Sprite Game)</span>
            <span style="color: #ff5252; font-weight: 600;">👾 Quái Mob (Thanh HP Bar)</span>
            <span style="color: #00e5ff; font-weight: 600;">🌀 Cổng Portal (Dịch Chuyển)</span>
            <span style="color: #69f0ae; font-weight: 600;">🟩 Mặt Đất Chuẩn (Y = 430)</span>
        </div>
        <div id="hoverCoords" style="font-family: 'Orbitron', monospace; font-size: 0.9rem; color: #00e5ff; background: rgba(0,229,255,0.1); padding: 4px 12px; border-radius: 4px; border: 1px solid rgba(0,229,255,0.2);">
            X: 0 | Y: 0
        </div>
    </div>

    <div style="position: relative; overflow-x: auto; background: #000; border-radius: 8px; border: 2px solid #1a2744; box-shadow: inset 0 0 20px rgba(0,0,0,0.8);">
        <canvas id="mapCanvas" width="1400" height="520" style="display: block; width: 1400px; height: 520px; cursor: crosshair;">
        </canvas>
    </div>
    <div style="font-size: 0.8rem; color: #888; margin-top: 8px; display: flex; justify-content: space-between;">
        <span>* Kéo thanh cuộn ngang nếu bản đồ dài hơn màn hình. Độ phân giải chuẩn: 1400 x 520 px.</span>
        <span>Mặt đất nền NRO mặc định ở cao độ Y: 430 - 435.</span>
    </div>
</div>

<!-- Entities Inspector & Manager -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px;">
    <!-- NPCs Table -->
    <div class="card" style="padding: 14px 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 8px; flex-wrap: wrap; gap: 8px;">
            <h3 style="color: #ffd700; font-size: 0.95rem; margin: 0; display: flex; align-items: center; gap: 6px;">
                🧙 DANH SÁCH NPC (<span id="npcCount">0</span>)
            </h3>
            <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.78rem; background: rgba(255, 215, 0, 0.2); color: #ffd700; border: 1px solid #ffd700; white-space: nowrap;" onclick="openAddNpcModal()">
                ➕ Thêm NPC
            </button>
        </div>
        <div style="max-height: 320px; overflow-y: auto;">
            <table style="margin-top: 0; width: 100%; font-size: 0.82rem; table-layout: fixed; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 36px; padding: 8px 4px; text-align: center;">Icon</th>
                        <th style="padding: 8px 4px;">NPC</th>
                        <th style="width: 55px; text-align: center; padding: 8px 4px;">X</th>
                        <th style="width: 55px; text-align: center; padding: 8px 4px;">Y</th>
                        <th style="text-align: right; width: 35px; padding: 8px 4px;">Xóa</th>
                    </tr>
                </thead>
                <tbody id="npcTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobs Table -->
    <div class="card" style="padding: 14px 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 8px; flex-wrap: wrap; gap: 8px;">
            <h3 style="color: #ff5252; font-size: 0.95rem; margin: 0; display: flex; align-items: center; gap: 6px;">
                👾 DANH SÁCH QUÁI VẬT (<span id="mobCount">0</span>)
            </h3>
            <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.78rem; background: rgba(255, 82, 82, 0.2); color: #ff5252; border: 1px solid #ff5252; white-space: nowrap;" onclick="openAddMobModal()">
                ➕ Thêm Quái
            </button>
        </div>
        <div style="max-height: 320px; overflow-y: auto;">
            <table style="margin-top: 0; width: 100%; font-size: 0.82rem; table-layout: fixed; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 36px; padding: 8px 4px; text-align: center;">Icon</th>
                        <th style="padding: 8px 4px;">Tên Quái</th>
                        <th style="width: 90px; padding: 8px 4px;">Cấp / Máu</th>
                        <th style="width: 55px; text-align: center; padding: 8px 4px;">X</th>
                        <th style="width: 55px; text-align: center; padding: 8px 4px;">Y</th>
                        <th style="text-align: right; width: 35px; padding: 8px 4px;">Xóa</th>
                    </tr>
                </thead>
                <tbody id="mobTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Thêm NPC -->
<div id="addNpcModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div class="card" style="width: 500px; max-width: 95%; max-height: 80vh; display: flex; flex-direction: column; border: 1px solid #ffd700; box-shadow: 0 0 25px rgba(255,215,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #ffd700; margin: 0; font-size: 1.1rem;">➕ Chọn NPC Cần Đặt Lên Bản Đồ</h3>
            <button onclick="closeAddNpcModal()" style="background: none; border: none; color: #aaa; font-size: 1.3rem; cursor: pointer;">&times;</button>
        </div>
        <input type="text" id="searchNpcInput" class="form-input" placeholder="🔍 Gõ tìm NPC theo tên hoặc ID..." oninput="filterNpcTemplates()" style="margin-bottom: 12px;">
        <div id="npcTemplateList" style="flex: 1; overflow-y: auto; max-height: 350px; display: grid; grid-template-columns: 1fr; gap: 8px;">
            @foreach($npcTemplates as $nt)
                <div class="npc-tpl-item" data-id="{{ $nt->id }}" data-name="{{ strtolower($nt->name) }}" data-avatar="{{ $nt->avatar }}" onclick="selectNpcToAdd({{ $nt->id }}, '{{ addslashes($nt->name) }}', {{ $nt->avatar ?? 0 }})" style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: rgba(255,255,255,0.04); border-radius: 6px; cursor: pointer; border: 1px solid transparent; transition: all 0.2s;">
                    <img src="/images/icons/{{ $nt->avatar ?? 0 }}.png" onerror="this.src='/images/no_image.png'" style="width: 32px; height: 32px; object-fit: contain; background: rgba(0,0,0,0.3); border-radius: 4px; padding: 2px;">
                    <div style="flex: 1;">
                        <div style="color: #ffd700; font-weight: 600; font-size: 0.9rem;">{{ $nt->name }}</div>
                        <div style="color: #888; font-size: 0.75rem;">ID: #{{ $nt->id }} | Avatar: {{ $nt->avatar ?? 0 }}</div>
                    </div>
                    <span class="badge" style="background: rgba(255,215,0,0.15); color: #ffd700; font-size: 0.75rem;">+ Thêm</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: Thêm Quái -->
<div id="addMobModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div class="card" style="width: 500px; max-width: 95%; max-height: 80vh; display: flex; flex-direction: column; border: 1px solid #ff5252; box-shadow: 0 0 25px rgba(255,82,82,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #ff5252; margin: 0; font-size: 1.1rem;">➕ Chọn Quái Vật Đặt Lên Bản Đồ</h3>
            <button onclick="closeAddMobModal()" style="background: none; border: none; color: #aaa; font-size: 1.3rem; cursor: pointer;">&times;</button>
        </div>
        <input type="text" id="searchMobInput" class="form-input" placeholder="🔍 Gõ tìm Quái theo tên hoặc ID..." oninput="filterMobTemplates()" style="margin-bottom: 12px;">
        <div id="mobTemplateList" style="flex: 1; overflow-y: auto; max-height: 350px; display: grid; grid-template-columns: 1fr; gap: 8px;">
            @foreach($mobTemplates as $mt)
                <div class="mob-tpl-item" data-id="{{ $mt->id }}" data-name="{{ strtolower($mt->name) }}" data-hp="{{ $mt->hp }}" onclick="selectMobToAdd({{ $mt->id }}, '{{ addslashes($mt->name) }}', {{ $mt->hp }}, {{ $mt->iconId ?? 0 }})" style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: rgba(255,255,255,0.04); border-radius: 6px; cursor: pointer; border: 1px solid transparent; transition: all 0.2s;">
                    <img src="/images/icons/{{ $mt->iconId ?? 7466 }}.png" onerror="this.src='/images/no_image.png'" style="width: 32px; height: 32px; object-fit: contain; background: rgba(0,0,0,0.3); border-radius: 4px; padding: 2px;">
                    <div style="flex: 1;">
                        <div style="color: #ff5252; font-weight: 600; font-size: 0.9rem;">{{ $mt->name }}</div>
                        <div style="color: #888; font-size: 0.75rem;">ID: #{{ $mt->id }} | Máu chuẩn: {{ number_format($mt->hp) }} HP</div>
                    </div>
                    <span class="badge" style="background: rgba(255,82,82,0.15); color: #ff5252; font-size: 0.75rem;">+ Thêm</span>
                </div>
            @endforeach
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

// Lọc danh sách bản đồ theo ID hoặc Tên
function filterMapOptions() {
    const q = document.getElementById('mapSearchInput').value.toLowerCase().trim();
    const select = document.getElementById('mapSelect');
    const options = select.options;
    let firstVisibleIndex = -1;

    for (let i = 0; i < options.length; i++) {
        const opt = options[i];
        const text = opt.text.toLowerCase();
        const val = opt.value.toString();
        const matches = !q || text.includes(q) || val.includes(q);
        opt.hidden = !matches;
        opt.style.display = matches ? '' : 'none';
        if (matches && firstVisibleIndex === -1) {
            firstVisibleIndex = i;
        }
    }

    if (q && firstVisibleIndex !== -1 && options[select.selectedIndex].hidden) {
        select.selectedIndex = firstVisibleIndex;
        loadSelectedMap();
    }
}

let draggingEntity = null;
let dragType = null; // 'npc' or 'mob'

// Cache hình ảnh Avatar NPC & Quái
const npcImageCache = {};
const mobImageCache = {};
const mapBgCache = {};

function getNpcImage(avatar) {
    if (!avatar || avatar <= 0) return null;
    if (!npcImageCache[avatar]) {
        const img = new Image();
        img.src = `/images/icons/${avatar}.png`;
        img.onload = () => renderCanvas();
        npcImageCache[avatar] = img;
    }
    return npcImageCache[avatar];
}

function getMobImage(iconId) {
    const id = iconId && iconId > 0 ? iconId : 7466;
    if (!mobImageCache[id]) {
        const img = new Image();
        img.src = `/images/icons/${id}.png`;
        img.onload = () => renderCanvas();
        mobImageCache[id] = img;
    }
    return mobImageCache[id];
}

function getMapBgImage(bgId, layer = 0) {
    const key = `x2b${bgId}${layer}`;
    if (mapBgCache[key] === undefined) {
        const img = new Image();
        img.src = `/images/map_bgs/${key}.png`;
        img.onload = () => renderCanvas();
        img.onerror = () => { mapBgCache[key] = null; };
        mapBgCache[key] = img;
    }
    return mapBgCache[key];
}

function loadSelectedMap() {
    const mapId = document.getElementById('mapSelect').value;
    fetch(`/admin/map-editor/data?map_id=${mapId}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                currentMapData = res.map;
                const bgId = currentMapData.bg_id !== undefined ? currentMapData.bg_id : 0;
                // Preload các layer background thật từ game NRO
                for (let l = 0; l <= 4; l++) {
                    getMapBgImage(bgId, l);
                }
                // Preload tất cả NPC avatars & Mob icons
                if (currentMapData.npcs) {
                    currentMapData.npcs.forEach(n => {
                        const av = n.avatar || (n.id ? n.avatar : 0);
                        if (av) getNpcImage(av);
                    });
                }
                if (currentMapData.mobs) {
                    currentMapData.mobs.forEach(m => {
                        if (m.iconId) getMobImage(m.iconId);
                    });
                }
                renderCanvas();
                renderTables();
            } else {
                if (window.showModalAlert) {
                    window.showModalAlert('Lỗi', res.message || 'Lỗi tải dữ liệu map!', 'danger');
                } else {
                    alert(res.message || 'Lỗi tải dữ liệu map!');
                }
            }
        })
        .catch(() => {
            if (window.showModalAlert) {
                window.showModalAlert('Lỗi kết nối', 'Không thể kết nối tới Server!', 'danger');
            } else {
                alert('Lỗi kết nối tới Server!');
            }
        });
}

function reloadMapData() {
    loadSelectedMap();
}

// -------------------------------------------------------------
// VẼ CANVAS 2D CHUẨN THẾ GIỚI GAME NRO
// -------------------------------------------------------------
function renderCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const groundY = 430;
    const bgId = currentMapData.bg_id !== undefined ? currentMapData.bg_id : 0;
    const planetId = currentMapData.planet_id !== undefined ? currentMapData.planet_id : 0;

    // 1. Vẽ bầu trời nền theo hành tinh (Sky Gradient)
    const skyGrad = ctx.createLinearGradient(0, 0, 0, groundY);
    if (planetId === 1) {
        // Namếc (Xanh ngọc / lục lam)
        skyGrad.addColorStop(0, '#042f2e');
        skyGrad.addColorStop(0.5, '#0f766e');
        skyGrad.addColorStop(1, '#14b8a6');
    } else if (planetId === 2) {
        // Xayda (Hoàng hôn đỏ quạch / cằn cỗi)
        skyGrad.addColorStop(0, '#450a0a');
        skyGrad.addColorStop(0.5, '#7f1d1d');
        skyGrad.addColorStop(1, '#b91c1c');
    } else {
        // Trái Đất (Xanh lam tươi sáng)
        skyGrad.addColorStop(0, '#0c4a6e');
        skyGrad.addColorStop(0.5, '#0284c7');
        skyGrad.addColorStop(1, '#7dd3fc');
    }
    ctx.fillStyle = skyGrad;
    ctx.fillRect(0, 0, canvas.width, groundY);

    // 2. Vẽ các layer Background NRO thật từ game (x2b{bgId}0, x2b{bgId}1, x2b{bgId}2, x2b{bgId}3)
    // Layer 0: Bầu trời xa / Dãy núi xa (tile lặp ngang)
    const bg0 = getMapBgImage(bgId, 0);
    if (bg0 && bg0.complete && bg0.naturalWidth > 0) {
        const bgH = Math.min(280, groundY - 40);
        const bgW = bg0.naturalWidth * (bgH / bg0.naturalHeight);
        for (let x = 0; x < canvas.width; x += bgW) {
            ctx.drawImage(bg0, x, groundY - bgH, bgW, bgH);
        }
    }

    // Layer 1: Đồi núi / Nhà cửa trung cảnh (nếu có)
    const bg1 = getMapBgImage(bgId, 1);
    if (bg1 && bg1.complete && bg1.naturalWidth > 0) {
        const scale = 1.25;
        const bgW = bg1.naturalWidth * scale;
        const bgH = bg1.naturalHeight * scale;
        for (let x = 0; x < canvas.width; x += bgW) {
            ctx.drawImage(bg1, x, groundY - bgH + 15, bgW, bgH);
        }
    }

    // Layer 2: Cây cối / Khung cảnh làng mạc cận cảnh (nếu có)
    const bg2 = getMapBgImage(bgId, 2);
    if (bg2 && bg2.complete && bg2.naturalWidth > 0) {
        const scale = 1.1;
        const bgW = bg2.naturalWidth * scale;
        const bgH = bg2.naturalHeight * scale;
        for (let x = 0; x < canvas.width; x += bgW) {
            ctx.drawImage(bg2, x, groundY - bgH + 8, bgW, bgH);
        }
    }

    // Layer 3: Chi tiết phụ
    const bg3 = getMapBgImage(bgId, 3);
    if (bg3 && bg3.complete && bg3.naturalWidth > 0) {
        const scale = 1.0;
        const bgW = bg3.naturalWidth * scale;
        const bgH = bg3.naturalHeight * scale;
        for (let x = 0; x < canvas.width; x += bgW) {
            ctx.drawImage(bg3, x, groundY - bgH, bgW, bgH);
        }
    }

    // 3. Lưới tọa độ nhẹ trên nền
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
    ctx.lineWidth = 1;
    for (let x = 0; x < canvas.width; x += 100) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();

        ctx.fillStyle = 'rgba(255, 255, 255, 0.35)';
        ctx.font = '9px monospace';
        ctx.fillText(x, x + 3, 14);
    }
    for (let y = 0; y < canvas.height; y += 50) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();

        ctx.fillStyle = 'rgba(255, 255, 255, 0.35)';
        ctx.font = '9px monospace';
        ctx.fillText(y, 4, y - 2);
    }

    // 4. Vẽ Mặt Đất 2D Platformer (Y = 430) theo Hành Tinh
    let grassColor = '#4caf50';
    let grassDark = '#2e7d32';
    let soilStart = '#3e2723';
    let soilEnd = '#150d0b';

    if (planetId === 1) {
        // Namếc
        grassColor = '#10b981';
        grassDark = '#047857';
        soilStart = '#134e4a';
        soilEnd = '#022c22';
    } else if (planetId === 2) {
        // Xayda
        grassColor = '#b45309';
        grassDark = '#78350f';
        soilStart = '#450a0a';
        soilEnd = '#1c0505';
    }

    // Lòng đất
    const earthGrad = ctx.createLinearGradient(0, groundY, 0, canvas.height);
    earthGrad.addColorStop(0, soilStart);
    earthGrad.addColorStop(1, soilEnd);
    ctx.fillStyle = earthGrad;
    ctx.fillRect(0, groundY, canvas.width, canvas.height - groundY);

    // Dải đá sọc trong lòng đất
    ctx.fillStyle = 'rgba(0,0,0,0.3)';
    for (let x = 0; x < canvas.width; x += 60) {
        ctx.fillRect(x + (x % 40), groundY + 25, 30, 8);
        ctx.fillRect(x + 15, groundY + 55, 20, 6);
    }

    // Dải cỏ bề mặt (Grass strip)
    ctx.fillStyle = grassColor;
    ctx.fillRect(0, groundY, canvas.width, 8);

    ctx.fillStyle = grassDark;
    ctx.fillRect(0, groundY + 8, canvas.width, 6);

    // Ngọn cỏ nhô lên
    ctx.fillStyle = grassColor;
    for (let x = 0; x < canvas.width; x += 12) {
        ctx.beginPath();
        ctx.moveTo(x, groundY);
        ctx.lineTo(x + 4, groundY - 6);
        ctx.lineTo(x + 8, groundY);
        ctx.fill();
    }

    // Đường ranh giới mặt đất Y=430 phát sáng
    ctx.strokeStyle = grassColor;
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(0, groundY);
    ctx.lineTo(canvas.width, groundY);
    ctx.stroke();

    // 4. Vẽ Cổng Dịch Chuyển (Waypoints)
    if (currentMapData.waypoints) {
        currentMapData.waypoints.forEach(w => {
            const wx = (w.minX + w.maxX) / 2;
            const wy = (w.minY + w.maxY) / 2;

            // Hiệu ứng Portal phát sáng
            const pGrad = ctx.createRadialGradient(wx, wy, 5, wx, wy, 28);
            pGrad.addColorStop(0, 'rgba(0, 229, 255, 0.8)');
            pGrad.addColorStop(0.6, 'rgba(0, 150, 255, 0.3)');
            pGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = pGrad;
            ctx.beginPath();
            ctx.arc(wx, wy, 32, 0, Math.PI * 2);
            ctx.fill();

            // Vòng tròn Portal
            ctx.strokeStyle = '#00e5ff';
            ctx.lineWidth = 2.5;
            ctx.beginPath();
            ctx.arc(wx, wy, 22, 0, Math.PI * 2);
            ctx.stroke();

            // Lõi xoay
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.arc(wx, wy, 6, 0, Math.PI * 2);
            ctx.fill();

            // Nhãn Portal
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            const pText = '🌀 ' + w.name;
            const tw = ctx.measureText(pText).width;
            ctx.fillStyle = 'rgba(7, 12, 24, 0.85)';
            ctx.fillRect(wx - tw/2 - 6, wy - 42, tw + 12, 18);
            ctx.strokeStyle = '#00e5ff';
            ctx.strokeRect(wx - tw/2 - 6, wy - 42, tw + 12, 18);

            ctx.fillStyle = '#00e5ff';
            ctx.fillText(pText, wx, wy - 29);

            ctx.fillStyle = '#80d8ff';
            ctx.font = '10px monospace';
            ctx.fillText(`→ Map ${w.goMap} (${w.goX},${w.goY})`, wx, wy + 38);
        });
    }

    // 5. Vẽ Quái Mob (Với Sprite Icon thật từ Game radar/item_template)
    if (currentMapData.mobs) {
        currentMapData.mobs.forEach((m, idx) => {
            const isSelected = (draggingEntity === idx && dragType === 'mob');
            const iconId = m.iconId || 7466;
            const img = getMobImage(iconId);

            // Đổ bóng dưới chân quái
            ctx.fillStyle = 'rgba(0,0,0,0.55)';
            ctx.beginPath();
            ctx.ellipse(m.x, m.y, 16, 5, 0, 0, Math.PI * 2);
            ctx.fill();

            const spriteHeight = 44;
            const spriteY = m.y - spriteHeight;

            if (img && img.complete && img.naturalWidth > 0) {
                const aspect = img.naturalWidth / img.naturalHeight;
                const spriteWidth = Math.min(60, Math.max(28, spriteHeight * aspect));
                const spriteX = m.x - spriteWidth / 2;

                if (isSelected) {
                    ctx.save();
                    ctx.shadowColor = '#ff5252';
                    ctx.shadowBlur = 15;
                    ctx.drawImage(img, spriteX, spriteY, spriteWidth, spriteHeight);
                    ctx.restore();
                } else {
                    ctx.drawImage(img, spriteX, spriteY, spriteWidth, spriteHeight);
                }
            } else {
                // Biểu tượng Token Quái fallback nếu ảnh chưa nạp xong
                ctx.fillStyle = isSelected ? '#ff1744' : '#d50000';
                ctx.beginPath();
                ctx.arc(m.x, m.y - 18, 16, 0, Math.PI * 2);
                ctx.fill();

                ctx.strokeStyle = isSelected ? '#ffff00' : '#ff8a80';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.font = '16px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('👾', m.x, m.y - 17);
            }

            // Thanh Máu (HP Bar)
            const barW = 50;
            const barH = 5;
            const barX = m.x - barW / 2;
            const barY = spriteY - 14;

            ctx.fillStyle = 'rgba(0,0,0,0.7)';
            ctx.fillRect(barX - 1, barY - 1, barW + 2, barH + 2);
            ctx.fillStyle = '#00e676';
            ctx.fillRect(barX, barY, barW, barH);

            // Tên Quái & Cấp
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'alphabetic';
            const mobLabel = `Lv.${m.level} ${m.name || 'Quái #' + m.id}`;
            const mtw = ctx.measureText(mobLabel).width;

            ctx.fillStyle = 'rgba(15, 20, 30, 0.85)';
            ctx.fillRect(m.x - mtw/2 - 5, barY - 18, mtw + 10, 16);
            ctx.strokeStyle = '#ff5252';
            ctx.lineWidth = 1;
            ctx.strokeRect(m.x - mtw/2 - 5, barY - 18, mtw + 10, 16);

            ctx.fillStyle = '#ff8a80';
            ctx.fillText(mobLabel, m.x, barY - 6);

            // Tọa độ nhỏ bên dưới
            ctx.font = '9px monospace';
            ctx.fillStyle = '#ffd54f';
            ctx.fillText(`(${m.x}, ${m.y})`, m.x, m.y + 16);
        });
    }

    // 6. Vẽ NPC (Với Sprite Avatar thật trong Game!)
    if (currentMapData.npcs) {
        currentMapData.npcs.forEach((n, idx) => {
            const nx = n.x !== undefined ? n.x : n[1];
            const ny = n.y !== undefined ? n.y : n[2];
            const nid = n.id !== undefined ? n.id : n[0];
            const nname = n.name || ('NPC #' + nid);
            const navatar = n.avatar || 0;
            const isSelected = (draggingEntity === idx && dragType === 'npc');

            // Đổ bóng dưới chân NPC
            ctx.fillStyle = 'rgba(0,0,0,0.6)';
            ctx.beginPath();
            ctx.ellipse(nx, ny, 18, 6, 0, 0, Math.PI * 2);
            ctx.fill();

            const img = getNpcImage(navatar);
            const spriteHeight = 48;
            const spriteY = ny - spriteHeight;

            if (img && img.complete && img.naturalWidth > 0) {
                // Tỉ lệ scale sprite
                const aspect = img.naturalWidth / img.naturalHeight;
                const spriteWidth = spriteHeight * aspect;
                const spriteX = nx - spriteWidth / 2;

                if (isSelected) {
                    ctx.save();
                    ctx.shadowColor = '#ffd700';
                    ctx.shadowBlur = 15;
                    ctx.drawImage(img, spriteX, spriteY, spriteWidth, spriteHeight);
                    ctx.restore();
                } else {
                    ctx.drawImage(img, spriteX, spriteY, spriteWidth, spriteHeight);
                }
            } else {
                // Fallback nếu ảnh chưa tải xong hoặc không có avatar
                ctx.fillStyle = isSelected ? '#ffea00' : '#ffd700';
                ctx.beginPath();
                ctx.arc(nx, ny - 24, 18, 0, Math.PI * 2);
                ctx.fill();

                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.font = '18px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('🧙', nx, ny - 24);
            }

            // Banner Tên NPC (Màu vàng rực rỡ)
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            const nameLabel = `🧙 ${nname}`;
            const ntw = ctx.measureText(nameLabel).width;

            const nameBoxY = spriteY - 20;
            ctx.fillStyle = 'rgba(20, 20, 10, 0.9)';
            ctx.fillRect(nx - ntw/2 - 6, nameBoxY, ntw + 12, 17);
            ctx.strokeStyle = '#ffd700';
            ctx.lineWidth = 1;
            ctx.strokeRect(nx - ntw/2 - 6, nameBoxY, ntw + 12, 17);

            ctx.fillStyle = '#ffd700';
            ctx.fillText(nameLabel, nx, nameBoxY + 12);

            // Tọa độ nhỏ bên dưới
            ctx.font = '9px monospace';
            ctx.fillStyle = '#fff9c4';
            ctx.fillText(`(${nx}, ${ny})`, nx, ny + 16);
        });
    }
}

// -------------------------------------------------------------
// RENDER BẢNG DANH SÁCH NPC & QUÁI VẬT
// -------------------------------------------------------------
function renderTables() {
    // 1. NPC Table
    const npcs = currentMapData.npcs || [];
    document.getElementById('npcCount').innerText = npcs.length;
    const nBody = document.getElementById('npcTableBody');
    nBody.innerHTML = '';

    npcs.forEach((n, idx) => {
        const nx = n.x !== undefined ? n.x : n[1];
        const ny = n.y !== undefined ? n.y : n[2];
        const nid = n.id !== undefined ? n.id : n[0];
        const nname = n.name || ('NPC #' + nid);
        const navatar = n.avatar || 0;

        nBody.innerHTML += `
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="text-align: center;">
                    <img src="/images/icons/${navatar}.png" onerror="this.src='/images/no_image.png'" style="width: 28px; height: 28px; object-fit: contain; background: rgba(0,0,0,0.4); border-radius: 4px; padding: 2px; vertical-align: middle;">
                </td>
                <td>
                    <div style="font-weight: 700; color: #ffd700; font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${nname}</div>
                    <div style="color: #777; font-size: 0.72rem;">ID: #${nid}</div>
                </td>
                <td style="text-align: center;">
                    <input type="number" class="form-input" style="padding: 3px 4px; font-size: 0.8rem; width: 50px; margin-bottom: 0; text-align: center;" value="${nx}" onchange="updateNpcCoord(${idx}, 'x', this.value)">
                </td>
                <td style="text-align: center;">
                    <input type="number" class="form-input" style="padding: 3px 4px; font-size: 0.8rem; width: 50px; margin-bottom: 0; text-align: center;" value="${ny}" onchange="updateNpcCoord(${idx}, 'y', this.value)">
                </td>
                <td style="text-align: right;">
                    <button class="btn btn-danger" style="padding: 3px 8px; font-size: 0.75rem;" onclick="removeNpc(${idx})">×</button>
                </td>
            </tr>
        `;
    });

    // 2. Mob Table
    const mobs = currentMapData.mobs || [];
    document.getElementById('mobCount').innerText = mobs.length;
    const mBody = document.getElementById('mobTableBody');
    mBody.innerHTML = '';

    mobs.forEach((m, idx) => {
        const mobIcon = m.iconId || 7466;
        mBody.innerHTML += `
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="text-align: center;">
                    <img src="/images/icons/${mobIcon}.png" onerror="this.src='/images/no_image.png'" style="width: 28px; height: 28px; object-fit: contain; background: rgba(0,0,0,0.4); border-radius: 4px; padding: 2px; vertical-align: middle;">
                </td>
                <td>
                    <div style="font-weight: 700; color: #ff5252; font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${m.name || 'Quái #' + m.id}</div>
                    <div style="color: #777; font-size: 0.72rem;">ID: #${m.id}</div>
                </td>
                <td>
                    <span style="color: #ffb74d; font-weight: 700;">Lv.${m.level}</span>
                    <div style="color: #00e676; font-size: 0.72rem;">${Number(m.hp).toLocaleString()} HP</div>
                </td>
                <td style="text-align: center;">
                    <input type="number" class="form-input" style="padding: 3px 4px; font-size: 0.8rem; width: 50px; margin-bottom: 0; text-align: center;" value="${m.x}" onchange="updateMobCoord(${idx}, 'x', this.value)">
                </td>
                <td style="text-align: center;">
                    <input type="number" class="form-input" style="padding: 3px 4px; font-size: 0.8rem; width: 50px; margin-bottom: 0; text-align: center;" value="${m.y}" onchange="updateMobCoord(${idx}, 'y', this.value)">
                </td>
                <td style="text-align: right;">
                    <button class="btn btn-danger" style="padding: 3px 8px; font-size: 0.75rem;" onclick="removeMob(${idx})">×</button>
                </td>
            </tr>
        `;
    });
}

function updateNpcCoord(idx, coord, val) {
    const v = parseInt(val) || 0;
    if (coord === 'x') {
        if (currentMapData.npcs[idx].x !== undefined) currentMapData.npcs[idx].x = v;
        else currentMapData.npcs[idx][1] = v;
    } else {
        if (currentMapData.npcs[idx].y !== undefined) currentMapData.npcs[idx].y = v;
        else currentMapData.npcs[idx][2] = v;
    }
    renderCanvas();
}

function updateMobCoord(idx, coord, val) {
    const v = parseInt(val) || 0;
    currentMapData.mobs[idx][coord] = v;
    renderCanvas();
}

function removeNpc(idx) {
    const npc = currentMapData.npcs[idx];
    const name = npc.name || ('NPC #' + (npc.id !== undefined ? npc.id : npc[0]));
    const doRemove = () => {
        currentMapData.npcs.splice(idx, 1);
        renderCanvas();
        renderTables();
        if (window.showToast) window.showToast(`Đã xóa NPC ${name}`, 'info');
    };

    if (window.showConfirm) {
        window.showConfirm({
            title: 'Xóa NPC Khỏi Bản Đồ',
            message: `Bạn có chắc muốn xóa NPC <b style="color: #ffd700;">${name}</b> khỏi bản đồ?`,
            okText: 'Xác nhận xóa',
            cancelText: 'Từ chối',
            isDanger: true,
            onConfirm: doRemove
        });
    } else {
        if (confirm(`Xác nhận xóa NPC ${name} khỏi bản đồ?`)) doRemove();
    }
}

function removeMob(idx) {
    const mob = currentMapData.mobs[idx];
    const name = mob.name || ('Quái #' + mob.id);
    const doRemove = () => {
        currentMapData.mobs.splice(idx, 1);
        renderCanvas();
        renderTables();
        if (window.showToast) window.showToast(`Đã xóa quái ${name}`, 'info');
    };

    if (window.showConfirm) {
        window.showConfirm({
            title: 'Xóa Quái Khỏi Bản Đồ',
            message: `Bạn có chắc muốn xóa quái <b style="color: #ff5252;">${name}</b> (Lv.${mob.level}) khỏi bản đồ?`,
            okText: 'Xác nhận xóa',
            cancelText: 'Từ chối',
            isDanger: true,
            onConfirm: doRemove
        });
    } else {
        if (confirm(`Xác nhận xóa quái ${name} khỏi bản đồ?`)) doRemove();
    }
}

// -------------------------------------------------------------
// MODALS THÊM NPC & QUÁI VẬT
// -------------------------------------------------------------
function openAddNpcModal() {
    document.getElementById('addNpcModal').style.display = 'flex';
    document.getElementById('searchNpcInput').value = '';
    filterNpcTemplates();
}

function closeAddNpcModal() {
    document.getElementById('addNpcModal').style.display = 'none';
}

function filterNpcTemplates() {
    const q = document.getElementById('searchNpcInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.npc-tpl-item');
    items.forEach(it => {
        const name = it.getAttribute('data-name');
        const id = it.getAttribute('data-id');
        if (name.includes(q) || id.includes(q)) {
            it.style.display = 'flex';
        } else {
            it.style.display = 'none';
        }
    });
}

function selectNpcToAdd(id, name, avatar) {
    currentMapData.npcs.push({
        id: id,
        name: name,
        avatar: avatar,
        x: 600,
        y: 432
    });
    getNpcImage(avatar);
    closeAddNpcModal();
    renderCanvas();
    renderTables();
    if (window.showToast) window.showToast(`Đã thêm NPC ${name}`, 'success');
}

function openAddMobModal() {
    document.getElementById('addMobModal').style.display = 'flex';
    document.getElementById('searchMobInput').value = '';
    filterMobTemplates();
}

function closeAddMobModal() {
    document.getElementById('addMobModal').style.display = 'none';
}

function filterMobTemplates() {
    const q = document.getElementById('searchMobInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.mob-tpl-item');
    items.forEach(it => {
        const name = it.getAttribute('data-name');
        const id = it.getAttribute('data-id');
        if (name.includes(q) || id.includes(q)) {
            it.style.display = 'flex';
        } else {
            it.style.display = 'none';
        }
    });
}

function selectMobToAdd(id, name, hp, iconId) {
    const mobIcon = iconId && iconId > 0 ? iconId : 7466;
    currentMapData.mobs.push({
        id: id,
        name: name,
        level: 1,
        hp: hp || 100,
        iconId: mobIcon,
        x: 700,
        y: 432
    });
    getMobImage(mobIcon);
    closeAddMobModal();
    renderCanvas();
    renderTables();
    if (window.showToast) window.showToast(`Đã thêm quái ${name}`, 'success');
}

// -------------------------------------------------------------
// KÉO THẢ CHUỘT TRÊN CANVAS (DRAG & DROP)
// -------------------------------------------------------------
canvas.addEventListener('mousedown', function(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;

    // Check click NPC
    if (currentMapData.npcs) {
        for (let i = 0; i < currentMapData.npcs.length; i++) {
            const n = currentMapData.npcs[i];
            const nx = n.x !== undefined ? n.x : n[1];
            const ny = n.y !== undefined ? n.y : n[2];
            const dist = Math.hypot(nx - mouseX, (ny - 24) - mouseY);
            if (dist <= 28) {
                draggingEntity = i;
                dragType = 'npc';
                renderCanvas();
                return;
            }
        }
    }

    // Check click Mob
    if (currentMapData.mobs) {
        for (let i = 0; i < currentMapData.mobs.length; i++) {
            const m = currentMapData.mobs[i];
            const dist = Math.hypot(m.x - mouseX, (m.y - 18) - mouseY);
            if (dist <= 26) {
                draggingEntity = i;
                dragType = 'mob';
                renderCanvas();
                return;
            }
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
            const n = currentMapData.npcs[draggingEntity];
            if (n.x !== undefined) {
                n.x = mouseX;
                n.y = mouseY;
            } else {
                n[1] = mouseX;
                n[2] = mouseY;
            }
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
        renderCanvas();
        renderTables();
    }
});

// -------------------------------------------------------------
// LƯU DỮ LIỆU BẢN ĐỒ VÀO DATABASE
// -------------------------------------------------------------
function saveMapData() {
    const doSave = () => {
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
                if (window.showModalAlert) {
                    window.showModalAlert('Thành công', res.message || 'Lưu cấu hình bản đồ thành công!', 'success');
                } else {
                    alert(res.message || 'Lưu bản đồ thành công!');
                }
            } else {
                if (window.showModalAlert) {
                    window.showModalAlert('Lỗi lưu bản đồ', res.message || 'Không thể lưu bản đồ!', 'danger');
                } else {
                    alert('Lỗi: ' + (res.message || 'Không thể lưu!'));
                }
            }
        })
        .catch(() => {
            if (window.showModalAlert) {
                window.showModalAlert('Lỗi kết nối', 'Không thể kết nối tới Server!', 'danger');
            } else {
                alert('Lỗi kết nối tới Server!');
            }
        });
    };

    if (window.showConfirm) {
        window.showConfirm({
            title: 'Lưu Dữ Liệu Bản Đồ',
            message: `Bạn có chắc muốn lưu lại tọa độ và danh sách NPC/Quái của Map <b style="color: #00e5ff;">#${currentMapData.id} - ${currentMapData.name || ''}</b> vào Database?`,
            okText: 'Đồng ý lưu',
            cancelText: 'Từ chối',
            isDanger: false,
            onConfirm: doSave
        });
    } else {
        if (confirm(`Bạn có chắc muốn lưu lại tọa độ thực thể Map #${currentMapData.id} vào Database?`)) doSave();
    }
}

// Khởi chạy khi load trang
document.addEventListener('DOMContentLoaded', loadSelectedMap);
</script>
@endsection
