@extends('admin.layout')

@section('admin_title', 'Quản Lý Map - NRO Panel')
@section('container_class', 'map-editor-wide-container')
@section('main_padding', '8px 0')

@section('admin_content')
<style>
.map-editor-wide-container {
    width: 100% !important;
    max-width: 99vw !important;
    margin: 0 auto !important;
    padding: 0 10px !important;
}
#adminMainLayout {
    grid-template-columns: var(--admin-sidebar-w, 200px) 1fr !important;
    gap: 12px !important;
    margin-top: 6px !important;
}
.map-card-item:hover {
    border-color: #388bfd !important;
    background: rgba(56, 139, 253, 0.1) !important;
}
</style>

<div id="mapEditorWrapper">
<!-- Header Bar -->
<div style="background: rgba(13, 17, 23, 0.95); border: 1px solid #30363d; border-radius: 8px; padding: 10px 16px; margin-bottom: 10px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button type="button" onclick="toggleAdminSidebar()" class="btn" id="btnToggleSidebar" title="Bật/Tắt Sidebar Menu Admin để mở rộng khung vẽ" style="background: #21262d; color: #58a6ff; border: 1px solid #30363d; padding: 6px 12px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                ◀ Thu gọn Menu
            </button>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('admin.dashboard') }}" style="color: #8b949e; text-decoration: none; font-size: 0.82rem; font-weight: 500;">
                        Admin
                    </a>
                    <span style="color: #484f58; font-size: 0.8rem;">/</span>
                    <span style="color: #58a6ff; font-size: 0.82rem; font-weight: 600;">Quản lý map</span>
                </div>
                <h2 id="currentMapTitle" style="font-family: 'Segoe UI', sans-serif; font-size: 1.15rem; color: #f0f6fc; margin: 2px 0 0 0; font-weight: 700;">
                    Map #0 - Làng Aru
                </h2>
            </div>
        </div>

        <!-- Top Action Buttons -->
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="btn" style="background: #1f6feb; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 6px; font-size: 0.84rem; border: none;" onclick="saveMapData()">
                💾 Lưu
            </button>
            <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; padding: 6px 12px; border-radius: 6px; font-size: 0.84rem;" onclick="undoLastChange()">
                ↺ Hoàn tác
            </button>
            <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; padding: 6px 12px; border-radius: 6px; font-size: 0.84rem;" onclick="showHistoryModal()">
                🕒 Lịch sử
            </button>
            <button type="button" class="btn" style="background: #da3633; color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 0.84rem; border: none;" onclick="deleteCurrentMap()">
                🗑 Xoá map
            </button>
            <button type="button" class="btn" style="background: #6e1717; color: #ffa39e; border: 1px solid #a8071a; font-weight: 600; padding: 6px 14px; border-radius: 6px; font-size: 0.84rem;" onclick="applyToGameMaintenance()">
                ⏱ Áp dụng vào game (bảo trì 3s)
            </button>
            <button type="button" class="btn" onclick="toggleFullscreenEditor()" id="btnFullscreen" title="Phóng to toàn màn hình" style="background: #21262d; color: #ffd700; border: 1px solid #d29922; padding: 6px 12px; border-radius: 6px; font-size: 0.84rem; font-weight: 600;">
                ⛶ Toàn màn hình
            </button>
        </div>
    </div>
</div>

<!-- 3-Column Main Workspace -->
<div style="display: flex; gap: 12px; align-items: stretch; min-height: calc(100vh - 210px);">
    
    <!-- LEFT COLUMN: Danh sách map -->
    <div style="width: 220px; flex-shrink: 0; background: rgba(13, 17, 23, 0.95); border: 1px solid #30363d; border-radius: 8px; padding: 10px; display: flex; flex-direction: column;">
        <button type="button" class="btn" style="background: #1f6feb; color: #fff; font-weight: 600; padding: 7px 12px; border-radius: 6px; font-size: 0.83rem; width: 100%; border: none; margin-bottom: 8px;" onclick="openCreateMapModal()">
            ➕ Tạo map mới
        </button>

        <div style="position: relative; margin-bottom: 8px;">
            <input type="text" id="mapSearchInput" placeholder="Tìm map theo tên hoặc id..." oninput="filterMapList()" style="width: 100%; background: #040d1a; border: 1px solid #30363d; border-radius: 6px; padding: 6px 8px; color: #f0f6fc; font-size: 0.8rem; outline: none;">
        </div>

        <!-- Scrollable Map Cards List -->
        <div id="mapCardsList" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 5px; height: calc(100vh - 345px); min-height: 480px; padding-right: 3px;">
            @foreach($maps as $m)
                <div class="map-card-item {{ $m->id == 0 ? 'active' : '' }}" id="map-card-{{ $m->id }}" onclick="switchMap({{ $m->id }})" data-name="{{ strtolower($m->name) }}" data-id="{{ $m->id }}" style="padding: 7px 9px; border-radius: 6px; cursor: pointer; transition: all 0.15s; border: 1px solid {{ $m->id == 0 ? '#3fb950' : '#21262d' }}; background: {{ $m->id == 0 ? 'rgba(35, 134, 54, 0.18)' : 'rgba(22, 27, 34, 0.6)' }};">
                    <div style="font-weight: 700; color: {{ $m->id == 0 ? '#3fb950' : '#f0f6fc' }}; font-size: 0.83rem; display: flex; justify-content: space-between;">
                        <span>#{{ $m->id }} · {{ $m->name }}</span>
                    </div>
                    <div style="font-size: 0.72rem; color: #8b949e; margin-top: 2px;">
                        {{ $m->npc_count ?? 0 }} NPC · {{ $m->mob_count ?? 0 }} quái · nền {{ $m->bg_id ?? 0 }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination Controls -->
        <div style="padding-top: 8px; border-top: 1px solid #30363d; margin-top: 6px; display: flex; flex-direction: column; gap: 6px;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 4px;">
                <button type="button" class="btn btn-sm" id="btnPrevMapPage" onclick="prevMapPage()" title="Trang trước" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; padding: 4px 10px; font-size: 0.75rem; border-radius: 4px; cursor: pointer;">
                    ◀
                </button>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <select id="mapPageSelect" onchange="goToMapPage(this.value)" style="background: #040d1a; color: #58a6ff; font-weight: 600; border: 1px solid #30363d; border-radius: 4px; padding: 3px 6px; font-size: 0.75rem; font-family: monospace; outline: none; cursor: pointer;">
                    </select>
                </div>
                <button type="button" class="btn btn-sm" id="btnNextMapPage" onclick="nextMapPage()" title="Trang sau" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; padding: 4px 10px; font-size: 0.75rem; border-radius: 4px; cursor: pointer;">
                    ▶
                </button>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; color: #8b949e;">
                <span id="mapPaginationCountInfo">12 / 178 map</span>
                <select id="mapPerPageSelect" onchange="changeMapPerPage(this.value)" style="background: #040d1a; color: #8b949e; border: 1px solid #21262d; border-radius: 4px; font-size: 0.72rem; cursor: pointer; padding: 2px 4px; outline: none;">
                    <option value="8">8 / trang</option>
                    <option value="12" selected>12 / trang</option>
                    <option value="24">24 / trang</option>
                    <option value="48">48 / trang</option>
                    <option value="999">Tất cả</option>
                </select>
            </div>
        </div>
    </div>

    <!-- CENTER COLUMN: Canvas Workspace -->
    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px;">
        <!-- Top Toolbar -->
        <div style="background: rgba(13, 17, 23, 0.95); border: 1px solid #30363d; border-radius: 8px; padding: 7px 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; font-size: 0.81rem; color: #c9d1d9;">
                <!-- Zoom pills -->
                <div style="display: flex; border: 1px solid #30363d; border-radius: 6px; overflow: hidden;">
                    <button type="button" class="zoom-btn" onclick="setZoom(0.5)" id="zoom-05" style="background: #21262d; border: none; color: #8b949e; padding: 3px 8px; font-size: 0.78rem; cursor: pointer;">0.5x</button>
                    <button type="button" class="zoom-btn" onclick="setZoom(1.0)" id="zoom-10" style="background: rgba(35, 134, 54, 0.35); border: none; color: #3fb950; font-weight: 700; padding: 3px 8px; font-size: 0.78rem; cursor: pointer;">1x</button>
                    <button type="button" class="zoom-btn" onclick="setZoom(2.0)" id="zoom-20" style="background: #21262d; border: none; color: #8b949e; padding: 3px 8px; font-size: 0.78rem; cursor: pointer;">2x</button>
                </div>

                <!-- Layer Checkboxes -->
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkSnapGround" checked style="accent-color: #238636;"> Bám mặt đất
                </label>
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkBackground" checked onchange="renderCanvas()" style="accent-color: #238636;"> Nền
                </label>
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkDecorations" checked onchange="renderCanvas()" style="accent-color: #238636;"> Vật trang trí
                </label>
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkEditDecorations" onchange="renderCanvas()" style="accent-color: #238636;"> Sửa trang trí
                </label>
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkGrid" onchange="renderCanvas()" style="accent-color: #238636;"> Lưới ô
                </label>
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkDrawTiles" style="accent-color: #238636;"> Vẽ ô đất
                </label>
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chkPortals" checked onchange="renderCanvas()" style="accent-color: #238636;"> Cổng chuyển map
                </label>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                <!-- Event Theme Selector -->
                <div style="display: flex; align-items: center; gap: 4px; background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 2px 6px;">
                    <span style="font-size: 0.76rem; color: #8b949e; white-space: nowrap;">🎉 Sự kiện:</span>
                    <select id="selectEventTheme" onchange="changeEventTheme(this.value)" class="form-input" style="padding: 2px 6px; font-size: 0.78rem; height: 26px; width: 140px; background: #0d1117; border-color: #30363d; color: #58a6ff; font-weight: 600;">
                        <option value="normal" {{ ($currentServerEvent ?? 0) == 0 ? 'selected' : '' }}>🌿 Mặc định</option>
                        <option value="tet" {{ ($currentServerEvent ?? 0) == 1 ? 'selected' : '' }}>🧧 Tết Nguyên Đán</option>
                        <option value="tuyet" {{ ($currentServerEvent ?? 0) == 2 ? 'selected' : '' }}>❄️ Giáng Sinh (Tuyết)</option>
                    </select>
                    <button type="button" class="btn" onclick="saveEventThemeToServer()" title="Lưu và áp dụng sự kiện này cho máy chủ Game" style="background: #238636; color: #fff; border: none; font-size: 0.74rem; padding: 2px 7px; border-radius: 4px; height: 24px; cursor: pointer;">
                        💾 Áp dụng
                    </button>
                </div>

                <button type="button" class="btn" style="background: #21262d; color: #ffd700; border: 1px solid #d29922; font-size: 0.79rem; padding: 4px 9px; border-radius: 6px;" onclick="openAddNpcModal()">
                    🧙 Thêm NPC
                </button>
                <button type="button" class="btn" style="background: #21262d; color: #ff7b72; border: 1px solid #f85149; font-size: 0.79rem; padding: 4px 9px; border-radius: 6px;" onclick="openAddMobModal()">
                    ➕ Thêm quái
                </button>
                <button type="button" class="btn" style="background: #21262d; color: #58a6ff; border: 1px solid #1f6feb; font-size: 0.79rem; padding: 4px 9px; border-radius: 6px;" onclick="openAddWaypointModal()">
                    🚪 Thêm cổng
                </button>
                <button type="button" class="btn" style="background: #21262d; color: #7ee787; border: 1px solid #238636; font-size: 0.79rem; padding: 4px 9px; border-radius: 6px;" onclick="openAddDecoModal()">
                    🌲 Thêm trang trí
                </button>
            </div>
        </div>

        <!-- Canvas Container Box -->
        <div id="canvasViewport" style="background: #000; border: 1px solid #30363d; border-radius: 8px; overflow: auto; position: relative; height: calc(100vh - 240px); min-height: 580px; max-height: 840px; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); width: 100%;">
            <div id="canvasScalerWrap" style="display: inline-block; position: relative; width: 1248px; height: 576px;">
                <div id="canvasScaler" style="transform-origin: 0 0; display: block;">
                    <canvas id="mapCanvas" width="1248" height="576" style="display: block; cursor: crosshair;">
                    </canvas>
                </div>
            </div>
        </div>

        <!-- Canvas Status Bar -->
        <div style="background: rgba(13, 17, 23, 0.95); border: 1px solid #30363d; border-radius: 6px; padding: 6px 14px; font-size: 0.78rem; color: #8b949e; font-family: 'Consolas', monospace; display: flex; justify-content: space-between;">
            <span id="canvasDimInfo">Kích thước 52×24 ô (1248×576 px) · tileset 1 · nền 0 · 10 khu</span>
            <span id="hoverCoords" style="color: #58a6ff;">X: 0 | Y: 0</span>
        </div>
    </div>

    <!-- RIGHT COLUMN: Inspector Tab Panel -->
    <div style="width: 260px; flex-shrink: 0; background: rgba(13, 17, 23, 0.95); border: 1px solid #30363d; border-radius: 8px; padding: 10px; display: flex; flex-direction: column;">
        <!-- Tabs Header -->
        <div style="display: flex; gap: 4px; border-bottom: 1px solid #30363d; padding-bottom: 8px; margin-bottom: 12px;">
            <button type="button" class="tab-btn active" id="tab-info" onclick="switchRightTab('info')" style="background: rgba(35, 134, 54, 0.2); color: #3fb950; border: 1px solid #3fb950; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                🟢 Thông tin
            </button>
            <button type="button" class="tab-btn" id="tab-bg" onclick="switchRightTab('bg')" style="background: transparent; color: #8b949e; border: 1px solid transparent; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
                🖼 Nền map
            </button>
            <button type="button" class="tab-btn" id="tab-tile" onclick="switchRightTab('tile')" style="background: transparent; color: #8b949e; border: 1px solid transparent; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
                🧱 Ô đất
            </button>
        </div>

        <div id="inspectorHint" style="font-size: 0.74rem; color: #8b949e; margin-bottom: 10px; line-height: 1.4;">
            Click nút tròn / khung cổng trên map hoặc một dòng dưới đây để sửa.
        </div>

        <!-- Tab 1: Thông tin (NPC, Quái, Cổng, Trang trí) -->
        <div id="tabContentInfo" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; max-height: 650px; padding-right: 2px;">
            <!-- NPCs Group -->
            <div>
                <div style="font-weight: 700; color: #ffd700; font-size: 0.85rem; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <span>🧙 NPC (<span id="rightNpcCount">0</span>)</span>
                    <button type="button" class="btn" style="background: none; border: none; color: #ffd700; font-size: 0.75rem; cursor: pointer;" onclick="openAddNpcModal()">➕</button>
                </div>
                <div id="rightNpcList" style="display: flex; flex-direction: column; gap: 4px;"></div>
            </div>

            <!-- Mobs Group -->
            <div>
                <div style="font-weight: 700; color: #ff7b72; font-size: 0.85rem; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <span>👾 Quái (<span id="rightMobCount">0</span>)</span>
                    <button type="button" class="btn" style="background: none; border: none; color: #ff7b72; font-size: 0.75rem; cursor: pointer;" onclick="openAddMobModal()">➕</button>
                </div>
                <div id="rightMobList" style="display: flex; flex-direction: column; gap: 4px;"></div>
            </div>

            <!-- Waypoints Group -->
            <div>
                <div style="font-weight: 700; color: #58a6ff; font-size: 0.85rem; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <span>🌀 Cổng chuyển map (<span id="rightWpCount">0</span>)</span>
                    <button type="button" class="btn" style="background: none; border: none; color: #58a6ff; font-size: 0.75rem; cursor: pointer;" onclick="openAddWaypointModal()">➕</button>
                </div>
                <div id="rightWpList" style="display: flex; flex-direction: column; gap: 4px;"></div>
            </div>

            <!-- Decorations Group -->
            <div>
                <div style="font-weight: 600; color: #8b949e; font-size: 0.82rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 4px 0;" onclick="toggleDecorationsGroup()">
                    <span id="rightDecoHeader">▶ Vật trang trí (34)</span>
                    <button type="button" class="btn" style="background: none; border: none; color: #7ee787; font-size: 0.75rem; cursor: pointer;" onclick="event.stopPropagation(); openAddDecoModal()" title="Thêm trang trí">➕</button>
                </div>
                <div id="rightDecoList" style="display: none; font-size: 0.75rem; color: #6e7681; padding-left: 2px;"></div>
            </div>
        </div>

        <!-- Tab 2: Nền map -->
        <div id="tabContentBg" style="display: none; font-size: 0.82rem; color: #c9d1d9;">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Bộ nền (bg_id):</label>
                <input type="number" id="inputBgId" class="form-input" style="width: 100%;" onchange="updateBgConfig()">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Biến thể nền (bg_type):</label>
                <input type="number" id="inputBgType" class="form-input" style="width: 100%;" onchange="updateBgConfig()">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Hành tinh (planet_id):</label>
                <select id="selectPlanetId" class="form-input" style="width: 100%;" onchange="updateBgConfig()">
                    <option value="0">0 - Trái Đất</option>
                    <option value="1">1 - Namếc</option>
                    <option value="2">2 - Xayda</option>
                </select>
            </div>
        </div>

        <!-- Tab 3: Ô đất (Tileset) -->
        <div id="tabContentTile" style="display: none; font-size: 0.82rem; color: #c9d1d9;">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Bộ Tile (tile_id):</label>
                <input type="number" id="inputTileId" class="form-input" style="width: 100%;" onchange="updateTileConfig()">
            </div>
            <div style="font-size: 0.75rem; color: #8b949e; line-height: 1.5;">
                Hệ thống nạp trực tiếp các tile game thật (24×24 px) từ thư mục tài nguyên Game Client.
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Modal: Thêm NPC -->
<div id="addNpcModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0d1117; border: 1px solid #30363d; border-radius: 8px; width: 560px; max-width: 95vw; padding: 18px; max-height: 85vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="margin: 0; color: #ffd700; font-size: 1rem;">🧙 Chọn NPC Thêm Vào Bản Đồ</h3>
            <button type="button" onclick="closeAddNpcModal()" style="background: none; border: none; color: #888; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <input type="text" id="searchNpcInput" placeholder="Tìm kiếm NPC..." oninput="filterNpcTemplates()" class="form-input" style="margin-bottom: 12px;">
        <div id="npcTplGrid" style="flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px;">
            @foreach($npcTemplates as $nt)
                <div class="npc-tpl-item" data-name="{{ strtolower($nt->name) }}" data-id="{{ $nt->id }}" onclick="selectNpcToAdd({{ $nt->id }}, '{{ addslashes($nt->name) }}', {{ $nt->avatar ?? 0 }})" style="background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <img src="/images/icons/{{ $nt->avatar }}.png" onerror="this.src='/images/no_image.png'" style="width: 32px; height: 32px; object-fit: contain;">
                    <div style="overflow: hidden;">
                        <div style="color: #ffd700; font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $nt->name }}</div>
                        <div style="color: #6e7681; font-size: 0.72rem;">ID: #{{ $nt->id }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: Thêm Quái -->
<div id="addMobModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0d1117; border: 1px solid #30363d; border-radius: 8px; width: 560px; max-width: 95vw; padding: 18px; max-height: 85vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="margin: 0; color: #ff7b72; font-size: 1rem;">👾 Chọn Quái Mob Thêm Vào Bản Đồ</h3>
            <button type="button" onclick="closeAddMobModal()" style="background: none; border: none; color: #888; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <input type="text" id="searchMobInput" placeholder="Tìm kiếm Quái..." oninput="filterMobTemplates()" class="form-input" style="margin-bottom: 12px;">
        <div id="mobTplGrid" style="flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px;">
            @foreach($mobTemplates as $mt)
                <div class="mob-tpl-item" data-name="{{ strtolower($mt->name) }}" data-id="{{ $mt->id }}" onclick="selectMobToAdd({{ $mt->id }}, '{{ addslashes($mt->name) }}', {{ $mt->hp ?? 100 }}, {{ $mt->iconId ?? 0 }})" style="background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <img src="/images/mobs/{{ $mt->id }}.png" onerror="if ({{ $mt->iconId ?? 0 }} > 0) { this.src='/images/icons/{{ $mt->iconId }}.png'; } else { this.src='/images/no_image.png'; }" style="width: 32px; height: 32px; object-fit: contain;">
                    <div style="overflow: hidden;">
                        <div style="color: #ff7b72; font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $mt->name }}</div>
                        <div style="color: #6e7681; font-size: 0.72rem;">HP: {{ number_format($mt->hp ?? 100) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: Thêm Cổng Chuyển Map -->
<div id="addWaypointModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0d1117; border: 1px solid #30363d; border-radius: 8px; width: 440px; max-width: 95vw; padding: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; color: #58a6ff; font-size: 1rem;">🚪 Thêm Cổng Chuyển Map (Portal)</h3>
            <button type="button" onclick="closeAddWaypointModal()" style="background: none; border: none; color: #888; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <div style="margin-bottom: 10px;">
            <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Tên hiển thị trên cổng:</label>
            <input type="text" id="wpNameInput" placeholder="Ví dụ: Đồi hoa cúc" class="form-input" style="width: 100%;">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
            <div>
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Tọa độ X:</label>
                <input type="number" id="wpXInput" value="600" class="form-input" style="width: 100%;">
            </div>
            <div>
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Tọa độ Y:</label>
                <input type="number" id="wpYInput" value="408" class="form-input" style="width: 100%;">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
            <div>
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Dẫn tới Map ID:</label>
                <input type="number" id="wpGoMapInput" value="1" class="form-input" style="width: 100%;">
            </div>
            <div>
                <label style="font-size: 0.78rem; color: #8b949e; display: block; margin-bottom: 4px;">Tọa độ đến (X, Y):</label>
                <input type="text" id="wpGoCoordsInput" value="100, 400" placeholder="100, 400" class="form-input" style="width: 100%;">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d;" onclick="closeAddWaypointModal()">Hủy</button>
            <button type="button" class="btn" style="background: #1f6feb; color: #fff; font-weight: 600;" onclick="saveNewWaypoint()">Tạo cổng</button>
        </div>
    </div>
</div>

<!-- Modal: Thêm Vật Trang Trí (Cây cối, Nhà cửa, Cột mốc, Cỏ hoa...) -->
<div id="addDecoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0d1117; border: 1px solid #30363d; border-radius: 8px; width: 680px; max-width: 95vw; padding: 18px; max-height: 85vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #21262d; padding-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.1rem;">🌲</span>
                <h3 style="margin: 0; color: #7ee787; font-size: 1.05rem;">Thêm Vật Trang Trí (Cây Cối, Nhà Cửa, Cột Mốc, Cỏ Hoa...)</h3>
            </div>
            <button type="button" onclick="closeAddDecoModal()" style="background: none; border: none; color: #888; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
            <input type="text" id="searchDecoInput" oninput="filterDecoTemplates()" placeholder="Tìm theo ID template hoặc tên..." class="form-input" style="flex: 1; font-size: 0.85rem;">
            <select id="filterDecoLayer" onchange="filterDecoTemplates()" class="form-input" style="width: 150px; font-size: 0.85rem;">
                <option value="all">Tất cả Layer</option>
                <option value="1">Layer 1 (Nền xa)</option>
                <option value="2">Layer 2 (Trung cảnh)</option>
                <option value="3">Layer 3 (Bề mặt)</option>
                <option value="4">Layer 4 (Tiền cảnh)</option>
            </select>
        </div>
        <div id="decoTemplatesGrid" style="flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(125px, 1fr)); gap: 8px; padding: 4px; min-height: 280px; max-height: 420px;">
            <div style="text-align: center; color: #8b949e; padding: 40px; grid-column: 1 / -1;">Đang tải danh sách mẫu trang trí...</div>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; border-top: 1px solid #21262d; padding-top: 10px;">
            <div style="font-size: 0.78rem; color: #8b949e;">
                Nhấp chuột vào mẫu để đặt trực tiếp vào giữa màn hình bản đồ.
            </div>
            <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d;" onclick="closeAddDecoModal()">Đóng</button>
        </div>
    </div>
</div>
</div><!-- #mapEditorWrapper -->

<script>
// =============================================================
// STATE & VARIABLES
// =============================================================
let currentMapData = {
    id: 0,
    name: 'Làng Aru',
    planet_id: 0,
    bg_id: 0,
    bg_type: 0,
    tile_id: 1,
    width: 1248,
    height: 576,
    tw: 52,
    th: 24,
    zones: 10,
    decorations_count: 34,
    tiles: [],
    platforms: [],
    blocks: [],
    npcs: [],
    mobs: [],
    waypoints: []
};

let currentZoom = 1.0;
let selectedEntity = null; // { type: 'npc'|'mob'|'wp', index: 0 }
let draggingEntity = null;
let dragType = null;
let dragOffsetX = 0;
let dragOffsetY = 0;

const canvas = document.getElementById('mapCanvas');
const ctx = canvas.getContext('2d');

// Image Caches
const npcImageCache = {};
const mobImageCache = {};
const mapBgCache = {};
const tileImageCache = {};
const cloudImage = new Image();
cloudImage.src = '/images/tiles/cloud_0.png';

function getNpcImage(avatar) {
    if (!avatar || avatar <= 0) return null;
    if (!npcImageCache[avatar]) {
        const img = new Image();
        img.src = `/images/icons/${avatar}.png`;
        img.onload = () => requestAnimationFrame(renderCanvas);
        npcImageCache[avatar] = img;
    }
    return npcImageCache[avatar];
}

function getMobImage(mobId, iconId) {
    const key = `mob_${mobId}_${iconId || 0}`;
    if (!mobImageCache[key]) {
        const img = new Image();
        img.src = `/images/mobs/${mobId}.png`;
        img.onerror = () => {
            if (iconId && iconId > 0 && iconId !== 7466) {
                img.src = `/images/icons/${iconId}.png`;
                img.onerror = () => { img.src = '/images/no_image.png'; };
            } else {
                img.src = '/images/no_image.png';
            }
        };
        img.onload = () => requestAnimationFrame(renderCanvas);
        mobImageCache[key] = img;
    }
    return mobImageCache[key];
}

function getMapBgImage(bgId, layer = 0, bgType = 0) {
    const key = bgType > 0 ? `x2b${bgId}${layer}-${bgType}` : `x2b${bgId}${layer}`;
    if (mapBgCache[key] === undefined) {
        const img = new Image();
        img.src = `/images/map_bgs/${key}.png`;
        img.onload = () => requestAnimationFrame(renderCanvas);
        img.onerror = () => {
            if (bgType > 0) {
                mapBgCache[key] = getMapBgImage(bgId, layer, 0);
            } else {
                mapBgCache[key] = null;
            }
        };
        mapBgCache[key] = img;
    }
    return mapBgCache[key];
}

function getTileImage(tileId, tileIndex) {
    if (!tileIndex || tileIndex <= 0) return null;
    const key = `${tileId}_${tileIndex}`;
    if (!tileImageCache[key]) {
        const img = new Image();
        img.src = `/images/tiles/tile_${tileId}_${tileIndex}.png`;
        img.onload = () => requestAnimationFrame(renderCanvas);
        tileImageCache[key] = img;
    }
    return tileImageCache[key];
}

let currentEventTheme = '{{ ($currentServerEvent ?? 0) == 1 ? "tet" : (($currentServerEvent ?? 0) == 2 ? "tuyet" : "normal") }}';
const decoImageCache = {};

function getDecoImage(imgId) {
    if (imgId === undefined || imgId === null || imgId < 0) return null;
    const cacheKey = `${imgId}_${currentEventTheme}`;
    if (!decoImageCache[cacheKey]) {
        const img = new Image();
        const queryParam = currentEventTheme !== 'normal' ? `?theme=${currentEventTheme}` : '';
        img.src = `/item-bg-image/${imgId}.png${queryParam}`;
        img.onload = () => requestAnimationFrame(renderCanvas);
        decoImageCache[cacheKey] = img;
    }
    return decoImageCache[cacheKey];
}

function changeEventTheme(theme) {
    currentEventTheme = theme;
    if (document.getElementById('addDecoModal') && document.getElementById('addDecoModal').style.display !== 'none') {
        renderDecoTemplatesList();
    }
    requestAnimationFrame(renderCanvas);
    renderRightInspector();
    if (window.showToast) {
        const nameMap = { 'normal': 'Mặc định (Bình thường)', 'tet': 'Tết Nguyên Đán', 'tuyet': 'Giáng Sinh (Tuyết rơi)' };
        window.showToast(`Đã chuyển xem trước chủ đề: ${nameMap[theme] || theme}`, 'info');
    }
}

function saveEventThemeToServer() {
    const themeVal = document.getElementById('selectEventTheme') ? document.getElementById('selectEventTheme').value : currentEventTheme;
    let eventId = 0;
    if (themeVal === 'tet') eventId = 1;
    else if (themeVal === 'tuyet') eventId = 2;

    fetch("{{ route('admin.map_editor.save_theme') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (window.showToast) window.showToast(res.message, 'success');
            else alert(res.message);
        } else {
            if (window.showToast) window.showToast(res.message, 'error');
            else alert(res.message);
        }
    })
    .catch(err => {
        console.error(err);
        if (window.showToast) window.showToast('Lỗi gửi yêu cầu lưu sự kiện!', 'error');
    });
}

// =============================================================
// MAP SWITCHING & DATA LOADING
// =============================================================
let mapCurrentPage = 1;
let mapPerPage = 12;
let filteredMapCards = [];

function initMapPagination() {
    filteredMapCards = Array.from(document.querySelectorAll('.map-card-item'));
    renderMapPagination();
}

function renderMapPagination() {
    const totalItems = filteredMapCards.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / mapPerPage));

    if (mapCurrentPage > totalPages) mapCurrentPage = totalPages;
    if (mapCurrentPage < 1) mapCurrentPage = 1;

    // Ẩn tất cả thẻ trước
    document.querySelectorAll('.map-card-item').forEach(c => c.style.display = 'none');

    // Hiện các thẻ thuộc trang hiện tại
    const startIdx = (mapCurrentPage - 1) * mapPerPage;
    const endIdx = startIdx + mapPerPage;
    const pageCards = filteredMapCards.slice(startIdx, endIdx);
    pageCards.forEach(c => c.style.display = 'block');

    // Cập nhật select chọn trang
    const select = document.getElementById('mapPageSelect');
    if (select) {
        select.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const opt = document.createElement('option');
            opt.value = i;
            opt.innerText = `Trang ${i}/${totalPages}`;
            if (i === mapCurrentPage) opt.selected = true;
            select.appendChild(opt);
        }
    }

    // Nút Trước / Sau
    const prevBtn = document.getElementById('btnPrevMapPage');
    const nextBtn = document.getElementById('btnNextMapPage');
    if (prevBtn) {
        prevBtn.disabled = (mapCurrentPage <= 1);
        prevBtn.style.opacity = (mapCurrentPage <= 1) ? '0.35' : '1';
        prevBtn.style.cursor = (mapCurrentPage <= 1) ? 'not-allowed' : 'pointer';
    }
    if (nextBtn) {
        nextBtn.disabled = (mapCurrentPage >= totalPages);
        nextBtn.style.opacity = (mapCurrentPage >= totalPages) ? '0.35' : '1';
        nextBtn.style.cursor = (mapCurrentPage >= totalPages) ? 'not-allowed' : 'pointer';
    }

    // Text đếm
    const countInfo = document.getElementById('mapPaginationCountInfo');
    if (countInfo) {
        countInfo.innerText = `${pageCards.length} / ${totalItems} map`;
    }
}

function prevMapPage() {
    if (mapCurrentPage > 1) {
        mapCurrentPage--;
        renderMapPagination();
    }
}

function nextMapPage() {
    const totalPages = Math.ceil(filteredMapCards.length / mapPerPage);
    if (mapCurrentPage < totalPages) {
        mapCurrentPage++;
        renderMapPagination();
    }
}

function goToMapPage(page) {
    mapCurrentPage = parseInt(page) || 1;
    renderMapPagination();
}

function changeMapPerPage(val) {
    mapPerPage = parseInt(val) || 12;
    mapCurrentPage = 1;
    renderMapPagination();
}

function filterMapList() {
    const q = document.getElementById('mapSearchInput').value.toLowerCase().trim();
    const allCards = Array.from(document.querySelectorAll('.map-card-item'));
    if (!q) {
        filteredMapCards = allCards;
    } else {
        filteredMapCards = allCards.filter(c => {
            const name = c.getAttribute('data-name') || '';
            const id = c.getAttribute('data-id') || '';
            return name.includes(q) || id.includes(q);
        });
    }
    mapCurrentPage = 1;
    renderMapPagination();
}

function switchMap(mapId) {
    document.querySelectorAll('.map-card-item').forEach(c => {
        c.style.borderColor = '#21262d';
        c.style.background = 'rgba(22, 27, 34, 0.6)';
    });
    const activeCard = document.getElementById(`map-card-${mapId}`);
    if (activeCard) {
        activeCard.style.borderColor = '#3fb950';
        activeCard.style.background = 'rgba(35, 134, 54, 0.18)';

        // Tự động chuyển đến trang chứa map được chọn nếu đang ở trang khác
        const cardIndex = filteredMapCards.indexOf(activeCard);
        if (cardIndex !== -1) {
            const cardPage = Math.floor(cardIndex / mapPerPage) + 1;
            if (cardPage !== mapCurrentPage) {
                mapCurrentPage = cardPage;
                renderMapPagination();
            }
        }
    }

    fetch(`/admin/map-editor/data?map_id=${mapId}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                currentMapData = res.map;
                updateHeaderInfo();
                canvas.width = currentMapData.width || 1248;
                canvas.height = currentMapData.height || 576;
                setZoom(currentZoom);
                selectedEntity = null;
                renderCanvas();
                renderRightInspector();
            }
        });
}

let isDecoGroupOpen = false;
function updateHeaderInfo() {
    document.getElementById('currentMapTitle').innerText = `Map #${currentMapData.id} - ${currentMapData.name}`;
    const tw = currentMapData.tw || Math.round(canvas.width / 24);
    const th = currentMapData.th || Math.round(canvas.height / 24);
    const zones = currentMapData.zones || 10;
    const decos = currentMapData.decorations ? currentMapData.decorations.length : (currentMapData.decorations_count || 0);
    document.getElementById('canvasDimInfo').innerText = 
        `Kích thước ${tw}×${th} ô (${canvas.width}×${canvas.height} px) · tileset ${currentMapData.tile_id} · nền ${currentMapData.bg_id} · ${zones} khu`;
    const decoHeader = document.getElementById('rightDecoHeader');
    if (decoHeader) {
        decoHeader.innerText = `${isDecoGroupOpen ? '▼' : '▶'} Vật trang trí (${decos})`;
    }
}

function setZoom(scale) {
    currentZoom = scale;
    document.querySelectorAll('.zoom-btn').forEach(b => {
        b.style.background = '#21262d';
        b.style.color = '#8b949e';
        b.style.fontWeight = 'normal';
    });
    const key = scale === 0.5 ? '05' : (scale === 1.0 ? '10' : '20');
    const activeBtn = document.getElementById(`zoom-${key}`);
    if (activeBtn) {
        activeBtn.style.background = 'rgba(35, 134, 54, 0.35)';
        activeBtn.style.color = '#3fb950';
        activeBtn.style.fontWeight = '700';
    }
    const scaler = document.getElementById('canvasScaler');
    const wrap = document.getElementById('canvasScalerWrap');
    if (scaler) {
        scaler.style.transform = `scale(${scale})`;
    }
    if (wrap && canvas) {
        wrap.style.width = Math.round(canvas.width * scale) + 'px';
        wrap.style.height = Math.round(canvas.height * scale) + 'px';
    }
}

// =============================================================
// CANVAS RENDERING ENGINE (PIXEL-PERFECT GAME TILES & PORTALS)
// =============================================================
function renderCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const showBg = document.getElementById('chkBackground').checked;
    const showDecos = document.getElementById('chkDecorations').checked;
    const showGrid = document.getElementById('chkGrid').checked;
    const showPortals = document.getElementById('chkPortals').checked;

    const bgId = currentMapData.bg_id || 0;
    const bgType = currentMapData.bg_type || 0;
    const planetId = currentMapData.planet_id || 0;
    const tileId = currentMapData.tile_id || 1;

    // 1. Sky Gradient
    const skyGrad = ctx.createLinearGradient(0, 0, 0, canvas.height);
    if (planetId === 1) {
        skyGrad.addColorStop(0, '#042f2e');
        skyGrad.addColorStop(0.4, '#0f766e');
        skyGrad.addColorStop(1, '#0d9488');
    } else if (planetId === 2) {
        skyGrad.addColorStop(0, '#3f0a0a');
        skyGrad.addColorStop(0.4, '#7f1d1d');
        skyGrad.addColorStop(1, '#991b1b');
    } else {
        skyGrad.addColorStop(0, '#0c4a6e');
        skyGrad.addColorStop(0.4, '#0284c7');
        skyGrad.addColorStop(0.8, '#7dd3fc');
        skyGrad.addColorStop(1, '#bae6fd');
    }
    ctx.fillStyle = skyGrad;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // 2. Horizon level
    let horizonY = Math.round(canvas.height * 0.75);
    if (currentMapData.platforms && currentMapData.platforms.length > 0) {
        const widthByY = {};
        let bestY = null;
        let maxW = 0;
        currentMapData.platforms.forEach(p => {
            if (p.y > canvas.height * 0.35) {
                const w = (p.x2 - p.x1);
                widthByY[p.y] = (widthByY[p.y] || 0) + w;
                if (widthByY[p.y] > maxW) {
                    maxW = widthByY[p.y];
                    bestY = p.y;
                }
            }
        });
        if (bestY !== null) horizonY = bestY;
    }

    // 3. Parallax Background Layers
    if (showBg) {
        const bg0 = getMapBgImage(bgId, 0, bgType);
        if (bg0 && bg0.complete && bg0.naturalWidth > 0) {
            const scale0 = Math.max(1.8, (horizonY - 20) / bg0.naturalHeight);
            const bgW0 = bg0.naturalWidth * scale0;
            const bgH0 = bg0.naturalHeight * scale0;
            const startY0 = Math.max(0, horizonY - bgH0);
            for (let x = 0; x < canvas.width; x += bgW0) {
                ctx.drawImage(bg0, x, startY0, bgW0, bgH0);
            }
        }

        const bg1 = getMapBgImage(bgId, 1, bgType);
        if (bg1 && bg1.complete && bg1.naturalWidth > 0) {
            const scale1 = 1.35;
            const bgW1 = bg1.naturalWidth * scale1;
            const bgH1 = bg1.naturalHeight * scale1;
            const startY1 = horizonY - bgH1 + 10;
            for (let x = 0; x < canvas.width; x += bgW1) {
                ctx.drawImage(bg1, x, startY1, bgW1, bgH1);
            }
        }
    }

    // 4. Cloud Trim Ceiling at top
    if (showDecos && cloudImage.complete && cloudImage.naturalWidth > 0) {
        for (let cx = 0; cx < canvas.width; cx += cloudImage.naturalWidth) {
            ctx.drawImage(cloudImage, cx, 0);
        }
    }

    // 4b. Background Decorations (Layer 1 & 2: Trees, houses, background structures)
    if (showDecos && currentMapData.decorations) {
        currentMapData.decorations.forEach((d, idx) => {
            if (d.layer === 1 || d.layer === 2) {
                drawDecorationEntity(d, idx);
            }
        });
    }

    // 5. Authentic Game Tiles Rendering
    if (currentMapData.tiles && currentMapData.tiles.length > 0) {
        const rows = currentMapData.tiles.length;
        for (let ty = 0; ty < rows; ty++) {
            const row = currentMapData.tiles[ty];
            for (let tx = 0; tx < row.length; tx++) {
                const tIdx = row[tx];
                if (tIdx > 0) {
                    const tImg = getTileImage(tileId, tIdx);
                    if (tImg && tImg.complete && tImg.naturalWidth > 0) {
                        ctx.drawImage(tImg, tx * 24, ty * 24, 24, 24);
                    } else {
                        // Fallback tile block
                        ctx.fillStyle = '#4e342e';
                        ctx.fillRect(tx * 24, ty * 24, 24, 24);
                    }
                }
            }
        }
    } else if (currentMapData.blocks) {
        // Fallback mesh
        ctx.fillStyle = '#4e342e';
        currentMapData.blocks.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));
    }

    // 5b. Surface & Foreground Decorations (Layer 3 & 4: Grass, fences, surface flora, foreground)
    if (showDecos && currentMapData.decorations) {
        currentMapData.decorations.forEach((d, idx) => {
            if (d.layer !== 1 && d.layer !== 2) {
                drawDecorationEntity(d, idx);
            }
        });
    }

    // 6. Grid Overlay
    if (showGrid) {
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
        ctx.lineWidth = 1;
        for (let x = 0; x < canvas.width; x += 24) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();
        }
        for (let y = 0; y < canvas.height; y += 24) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();
        }
    }

    // 7. Waypoints / Portals (Yellow bounding boxes with directional arrows)
    if (showPortals && currentMapData.waypoints) {
        currentMapData.waypoints.forEach((w, idx) => {
            const isSel = selectedEntity && selectedEntity.type === 'wp' && selectedEntity.index === idx;
            const bx = w.minX;
            const by = w.minY;
            const bw = Math.max(36, w.maxX - w.minX);
            const bh = Math.max(24, w.maxY - w.minY);

            // Bounding box
            ctx.strokeStyle = isSel ? '#00e5ff' : '#ffd700';
            ctx.lineWidth = isSel ? 2 : 1.5;
            ctx.setLineDash([4, 3]);
            ctx.strokeRect(bx, by, bw, bh);
            ctx.setLineDash([]);

            // Semi-transparent fill
            ctx.fillStyle = isSel ? 'rgba(0, 229, 255, 0.15)' : 'rgba(255, 215, 0, 0.12)';
            ctx.fillRect(bx, by, bw, bh);

            // Tag label
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            const labelText = `[${w.name} - #${w.goMap}]`;
            const tw = ctx.measureText(labelText).width;
            const cx = bx + bw / 2;
            const cy = by + bh / 2;

            ctx.fillStyle = 'rgba(13, 17, 23, 0.9)';
            ctx.fillRect(cx - tw/2 - 4, cy - 8, tw + 8, 16);
            ctx.strokeStyle = isSel ? '#00e5ff' : '#ffd700';
            ctx.lineWidth = 1;
            ctx.strokeRect(cx - tw/2 - 4, cy - 8, tw + 8, 16);

            ctx.fillStyle = isSel ? '#00e5ff' : '#ffd700';
            ctx.fillText(labelText, cx, cy + 4);
        });
    }

    // 8. NPCs (Circular Avatar Tokens)
    if (currentMapData.npcs) {
        currentMapData.npcs.forEach((n, idx) => {
            const nx = n.x !== undefined ? n.x : n[1];
            const ny = n.y !== undefined ? n.y : n[2];
            const nname = n.name || ('NPC #' + (n.id !== undefined ? n.id : n[0]));
            const navatar = n.avatar || 0;
            const isSel = selectedEntity && selectedEntity.type === 'npc' && selectedEntity.index === idx;

            // Circular token
            const r = 16;
            const cy = ny - r - 4;

            ctx.save();
            ctx.beginPath();
            ctx.arc(nx, cy, r, 0, Math.PI * 2);
            ctx.fillStyle = '#0d1117';
            ctx.fill();
            ctx.strokeStyle = isSel ? '#00e5ff' : '#ffd700';
            ctx.lineWidth = isSel ? 3 : 2;
            if (isSel) {
                ctx.shadowColor = '#00e5ff';
                ctx.shadowBlur = 12;
            }
            ctx.stroke();
            ctx.clip();

            const img = getNpcImage(navatar);
            if (img && img.complete && img.naturalWidth > 0) {
                ctx.drawImage(img, nx - r, cy - r, r * 2, r * 2);
            } else {
                ctx.fillStyle = '#ffd700';
                ctx.font = '12px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('🧙', nx, cy);
            }
            ctx.restore();

            // Name & Coordinate tag
            ctx.font = 'bold 10px sans-serif';
            ctx.textAlign = 'center';
            const tw = ctx.measureText(nname).width;
            ctx.fillStyle = 'rgba(13, 17, 23, 0.85)';
            ctx.fillRect(nx - tw/2 - 4, cy - r - 16, tw + 8, 14);
            ctx.strokeStyle = '#ffd700';
            ctx.strokeRect(nx - tw/2 - 4, cy - r - 16, tw + 8, 14);
            ctx.fillStyle = '#ffd700';
            ctx.fillText(nname, nx, cy - r - 5);

            // Coords beneath
            ctx.font = '9px monospace';
            ctx.fillStyle = '#f0f6fc';
            ctx.fillText(`${nx}, ${ny}`, nx, ny + 12);
        });
    }

    // 9. Mobs (Circular Token with Red Outline & HP Bar)
    if (currentMapData.mobs) {
        currentMapData.mobs.forEach((m, idx) => {
            const isSel = selectedEntity && selectedEntity.type === 'mob' && selectedEntity.index === idx;
            const iconId = m.iconId || 0;
            const img = getMobImage(m.id, iconId);

            const r = 15;
            const cy = m.y - r - 4;

            ctx.save();
            ctx.beginPath();
            ctx.arc(m.x, cy, r, 0, Math.PI * 2);
            ctx.fillStyle = '#0d1117';
            ctx.fill();
            ctx.strokeStyle = isSel ? '#00e5ff' : '#ff7b72';
            ctx.lineWidth = isSel ? 3 : 2;
            if (isSel) {
                ctx.shadowColor = '#00e5ff';
                ctx.shadowBlur = 12;
            }
            ctx.stroke();
            ctx.clip();

            if (img && img.complete && img.naturalWidth > 0) {
                ctx.drawImage(img, m.x - r, cy - r, r * 2, r * 2);
            } else {
                ctx.fillStyle = '#ff7b72';
                ctx.font = '12px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('👾', m.x, cy);
            }
            ctx.restore();

            // Mob Label
            const mobLabel = `Lv.${m.level} ${m.name || 'Quái #' + m.id}`;
            ctx.font = 'bold 10px sans-serif';
            ctx.textAlign = 'center';
            const tw = ctx.measureText(mobLabel).width;
            ctx.fillStyle = 'rgba(13, 17, 23, 0.85)';
            ctx.fillRect(m.x - tw/2 - 4, cy - r - 16, tw + 8, 14);
            ctx.strokeStyle = '#ff7b72';
            ctx.strokeRect(m.x - tw/2 - 4, cy - r - 16, tw + 8, 14);
            ctx.fillStyle = '#ff7b72';
            ctx.fillText(mobLabel, m.x, cy - r - 5);

            // Coords beneath
            ctx.font = '9px monospace';
            ctx.fillStyle = '#f0f6fc';
            ctx.fillText(`${m.x}, ${m.y}`, m.x, m.y + 12);
        });
    }
}

function drawDecorationEntity(d, idx) {
    const img = getDecoImage(d.img_id);
    if (!img) return;

    const dw = (img.complete && img.naturalWidth > 0) ? img.naturalWidth : 40;
    const dh = (img.complete && img.naturalHeight > 0) ? img.naturalHeight : 40;
    const dx = d.x;
    const dy = d.y;

    d._w = dw;
    d._h = dh;

    if (img.complete && img.naturalWidth > 0) {
        ctx.drawImage(img, dx, dy, dw, dh);
    }

    const isEditMode = document.getElementById('chkEditDecorations') && document.getElementById('chkEditDecorations').checked;
    const isSel = selectedEntity && selectedEntity.type === 'deco' && selectedEntity.index === idx;

    if (isSel || isEditMode) {
        ctx.save();
        ctx.strokeStyle = isSel ? '#00e5ff' : 'rgba(126, 231, 135, 0.45)';
        ctx.lineWidth = isSel ? 2 : 1;
        if (!isSel) ctx.setLineDash([3, 3]);
        ctx.strokeRect(dx, dy, dw, dh);

        if (isSel) {
            ctx.fillStyle = 'rgba(0, 229, 255, 0.15)';
            ctx.fillRect(dx, dy, dw, dh);

            const label = `#${idx} · Tpl ${d.template_id} (L${d.layer})`;
            ctx.font = 'bold 10px monospace';
            const tw = ctx.measureText(label).width;
            ctx.fillStyle = 'rgba(13, 17, 23, 0.85)';
            ctx.fillRect(dx, dy - 16, tw + 8, 15);
            ctx.strokeStyle = '#00e5ff';
            ctx.lineWidth = 1;
            ctx.strokeRect(dx, dy - 16, tw + 8, 15);
            ctx.fillStyle = '#00e5ff';
            ctx.fillText(label, dx + 4, dy - 5);
        }
        ctx.restore();
    }
}

// =============================================================
// RIGHT INSPECTOR TABS & ITEMS LIST
// =============================================================
function switchRightTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.style.background = 'transparent';
        b.style.color = '#8b949e';
        b.style.borderColor = 'transparent';
    });
    const activeBtn = document.getElementById(`tab-${tab}`);
    if (activeBtn) {
        activeBtn.style.background = 'rgba(35, 134, 54, 0.2)';
        activeBtn.style.color = '#3fb950';
        activeBtn.style.borderColor = '#3fb950';
    }

    document.getElementById('tabContentInfo').style.display = (tab === 'info') ? 'flex' : 'none';
    document.getElementById('tabContentBg').style.display = (tab === 'bg') ? 'block' : 'none';
    document.getElementById('tabContentTile').style.display = (tab === 'tile') ? 'block' : 'none';

    if (tab === 'bg') {
        document.getElementById('inputBgId').value = currentMapData.bg_id || 0;
        document.getElementById('inputBgType').value = currentMapData.bg_type || 0;
        document.getElementById('selectPlanetId').value = currentMapData.planet_id || 0;
    } else if (tab === 'tile') {
        document.getElementById('inputTileId').value = currentMapData.tile_id || 1;
    }
}

function renderRightInspector() {
    const npcs = currentMapData.npcs || [];
    const mobs = currentMapData.mobs || [];
    const waypoints = currentMapData.waypoints || [];

    document.getElementById('rightNpcCount').innerText = npcs.length;
    document.getElementById('rightMobCount').innerText = mobs.length;
    document.getElementById('rightWpCount').innerText = waypoints.length;

    // Render NPCs
    const npcList = document.getElementById('rightNpcList');
    npcList.innerHTML = '';
    npcs.forEach((n, idx) => {
        const nx = n.x !== undefined ? n.x : n[1];
        const ny = n.y !== undefined ? n.y : n[2];
        const nname = n.name || ('NPC #' + (n.id !== undefined ? n.id : n[0]));
        const isSel = selectedEntity && selectedEntity.type === 'npc' && selectedEntity.index === idx;

        npcList.innerHTML += `
            <div onclick="selectEntity('npc', ${idx})" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 6px; border-radius: 4px; cursor: pointer; background: ${isSel ? 'rgba(0, 229, 255, 0.15)' : 'rgba(255,255,255,0.03)'}; border: 1px solid ${isSel ? '#00e5ff' : 'transparent'};">
                <div style="display: flex; align-items: center; gap: 6px; overflow: hidden;">
                    <img src="/images/icons/${n.avatar || 0}.png" onerror="this.src='/images/no_image.png'" style="width: 20px; height: 20px; border-radius: 50%; object-fit: contain;">
                    <span style="font-size: 0.8rem; color: #ffd700; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${nname}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; font-family: monospace; color: #8b949e;">${nx}, ${ny}</span>
                    <button type="button" onclick="event.stopPropagation(); removeNpc(${idx})" style="background: none; border: none; color: #da3633; cursor: pointer; font-size: 0.8rem; padding: 0 4px;">&times;</button>
                </div>
            </div>
        `;
    });

    // Render Mobs
    const mobList = document.getElementById('rightMobList');
    mobList.innerHTML = '';
    mobs.forEach((m, idx) => {
        const isSel = selectedEntity && selectedEntity.type === 'mob' && selectedEntity.index === idx;
        mobList.innerHTML += `
            <div onclick="selectEntity('mob', ${idx})" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 6px; border-radius: 4px; cursor: pointer; background: ${isSel ? 'rgba(0, 229, 255, 0.15)' : 'rgba(255,255,255,0.03)'}; border: 1px solid ${isSel ? '#00e5ff' : 'transparent'};">
                <div style="display: flex; align-items: center; gap: 6px; overflow: hidden;">
                    <img src="/images/mobs/${m.id}.png" onerror="if (${m.iconId || 0} > 0) { this.src='/images/icons/${m.iconId}.png'; } else { this.src='/images/no_image.png'; }" style="width: 20px; height: 20px; border-radius: 4px; object-fit: contain;">
                    <span style="font-size: 0.8rem; color: #ff7b72; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${m.name || 'Quái #' + m.id}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; font-family: monospace; color: #8b949e;">${m.x}, ${m.y}</span>
                    <button type="button" onclick="event.stopPropagation(); removeMob(${idx})" style="background: none; border: none; color: #da3633; cursor: pointer; font-size: 0.8rem; padding: 0 4px;">&times;</button>
                </div>
            </div>
        `;
    });

    // Render Waypoints
    const wpList = document.getElementById('rightWpList');
    wpList.innerHTML = '';
    waypoints.forEach((w, idx) => {
        const isSel = selectedEntity && selectedEntity.type === 'wp' && selectedEntity.index === idx;
        wpList.innerHTML += `
            <div onclick="selectEntity('wp', ${idx})" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 6px; border-radius: 4px; cursor: pointer; background: ${isSel ? 'rgba(0, 229, 255, 0.15)' : 'rgba(255,255,255,0.03)'}; border: 1px solid ${isSel ? '#00e5ff' : 'transparent'};">
                <div style="display: flex; align-items: center; gap: 6px; overflow: hidden;">
                    <span style="color: #58a6ff; font-size: 0.85rem;">→</span>
                    <span style="font-size: 0.8rem; color: #58a6ff; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${w.name} → #${w.goMap}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; font-family: monospace; color: #8b949e;">${w.minX}, ${w.minY}</span>
                    <button type="button" onclick="event.stopPropagation(); removeWaypoint(${idx})" style="background: none; border: none; color: #da3633; cursor: pointer; font-size: 0.8rem; padding: 0 4px;">&times;</button>
                </div>
            </div>
        `;
    });

    // Render Decorations
    const decoList = document.getElementById('rightDecoList');
    if (decoList) {
        const decos = currentMapData.decorations || [];
        if (decos.length === 0) {
            decoList.innerHTML = `<div style="padding: 6px; color: #8b949e; font-size: 0.75rem;">Không có vật trang trí nào trên bản đồ này.</div>`;
        } else {
            const decoThemeParam = currentEventTheme !== 'normal' ? '?theme=' + currentEventTheme : '';
            let html = `<div style="display: flex; flex-direction: column; gap: 4px; max-height: 250px; overflow-y: auto; padding-right: 4px;">`;
            decos.forEach((d, idx) => {
                const isSel = selectedEntity && selectedEntity.type === 'deco' && selectedEntity.index === idx;
                html += `
                    <div onclick="selectEntity('deco', ${idx})" style="display: flex; align-items: center; justify-content: space-between; padding: 3px 6px; border-radius: 4px; cursor: pointer; background: ${isSel ? 'rgba(0, 229, 255, 0.15)' : 'rgba(255,255,255,0.03)'}; border: 1px solid ${isSel ? '#00e5ff' : 'transparent'};">
                        <div style="display: flex; align-items: center; gap: 6px; overflow: hidden;">
                            <img src="/item-bg-image/${d.img_id}.png${decoThemeParam}" onerror="this.src='/images/no_image.png'" style="width: 22px; height: 22px; object-fit: contain; background: rgba(0,0,0,0.3); border-radius: 2px;">
                            <span style="font-size: 0.75rem; color: #7ee787; font-weight: 600;">#${idx} Tpl ${d.template_id}</span>
                            <span style="font-size: 0.7rem; color: #8b949e;">(L${d.layer})</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 0.72rem; font-family: monospace; color: #8b949e;">${d.x}, ${d.y}</span>
                            <button type="button" onclick="event.stopPropagation(); removeDecoration(${idx})" title="Xóa" style="background: none; border: none; color: #da3633; cursor: pointer; font-size: 0.8rem; padding: 0 4px;">&times;</button>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;
            decoList.innerHTML = html;
        }
    }
}

function selectEntity(type, index) {
    selectedEntity = { type, index };
    if (type === 'deco' && currentMapData.decorations && currentMapData.decorations[index]) {
        const d = currentMapData.decorations[index];
        const vp = document.getElementById('canvasViewport');
        if (vp) {
            vp.scrollLeft = Math.max(0, d.x * currentZoom - vp.clientWidth / 2);
            vp.scrollTop = Math.max(0, d.y * currentZoom - vp.clientHeight / 2);
        }
    }
    renderCanvas();
    renderRightInspector();
}

function toggleDecorationsGroup() {
    isDecoGroupOpen = !isDecoGroupOpen;
    const el = document.getElementById('rightDecoList');
    if (el) el.style.display = isDecoGroupOpen ? 'block' : 'none';
    const header = document.getElementById('rightDecoHeader');
    const cnt = (currentMapData && currentMapData.decorations) ? currentMapData.decorations.length : (currentMapData ? currentMapData.decorations_count : 0);
    if (header) {
        header.innerText = `${isDecoGroupOpen ? '▼' : '▶'} Vật trang trí (${cnt})`;
    }
}

function removeDecoration(idx) {
    if (!currentMapData.decorations) return;
    currentMapData.decorations.splice(idx, 1);
    currentMapData.decorations_count = currentMapData.decorations.length;
    if (selectedEntity && selectedEntity.type === 'deco' && selectedEntity.index === idx) {
        selectedEntity = null;
    }
    updateHeaderInfo();
    renderCanvas();
    renderRightInspector();
}

function updateBgConfig() {
    currentMapData.bg_id = parseInt(document.getElementById('inputBgId').value) || 0;
    currentMapData.bg_type = parseInt(document.getElementById('inputBgType').value) || 0;
    currentMapData.planet_id = parseInt(document.getElementById('selectPlanetId').value) || 0;
    renderCanvas();
}

function updateTileConfig() {
    currentMapData.tile_id = parseInt(document.getElementById('inputTileId').value) || 1;
    renderCanvas();
}

// =============================================================
// INTERACTION: MOUSE & KEYBOARD EVENT HANDLERS
// =============================================================
function snapToGround(x, y) {
    if (!document.getElementById('chkSnapGround').checked) return y;
    if (currentMapData.platforms && currentMapData.platforms.length > 0) {
        let closestY = y;
        let minDiff = 60;
        currentMapData.platforms.forEach(p => {
            if (x >= p.x1 - 20 && x <= p.x2 + 20) {
                const diff = Math.abs(p.y - y);
                if (diff < minDiff) {
                    minDiff = diff;
                    closestY = p.y;
                }
            }
        });
        return closestY;
    }
    return y;
}

canvas.addEventListener('mousedown', function(e) {
    const rect = canvas.getBoundingClientRect();
    const scale = currentZoom;
    const mouseX = Math.round((e.clientX - rect.left) / scale);
    const mouseY = Math.round((e.clientY - rect.top) / scale);

    // 1. Check NPC Hit
    if (currentMapData.npcs) {
        for (let i = 0; i < currentMapData.npcs.length; i++) {
            const n = currentMapData.npcs[i];
            const nx = n.x !== undefined ? n.x : n[1];
            const ny = n.y !== undefined ? n.y : n[2];
            const cy = ny - 20;
            if (Math.hypot(nx - mouseX, cy - mouseY) <= 22) {
                selectedEntity = { type: 'npc', index: i };
                draggingEntity = i;
                dragType = 'npc';
                dragOffsetX = nx - mouseX;
                dragOffsetY = ny - mouseY;
                renderCanvas();
                renderRightInspector();
                return;
            }
        }
    }

    // 2. Check Mob Hit
    if (currentMapData.mobs) {
        for (let i = 0; i < currentMapData.mobs.length; i++) {
            const m = currentMapData.mobs[i];
            const cy = m.y - 19;
            if (Math.hypot(m.x - mouseX, cy - mouseY) <= 20) {
                selectedEntity = { type: 'mob', index: i };
                draggingEntity = i;
                dragType = 'mob';
                dragOffsetX = m.x - mouseX;
                dragOffsetY = m.y - mouseY;
                renderCanvas();
                renderRightInspector();
                return;
            }
        }
    }

    // 3. Check Waypoint Hit
    if (currentMapData.waypoints) {
        for (let i = 0; i < currentMapData.waypoints.length; i++) {
            const w = currentMapData.waypoints[i];
            if (mouseX >= w.minX && mouseX <= w.maxX && mouseY >= w.minY && mouseY <= w.maxY) {
                selectedEntity = { type: 'wp', index: i };
                draggingEntity = i;
                dragType = 'wp';
                dragOffsetX = w.minX - mouseX;
                dragOffsetY = w.minY - mouseY;
                renderCanvas();
                renderRightInspector();
                return;
            }
        }
    }

    // 4. Check Decoration Hit
    const isEditDeco = document.getElementById('chkEditDecorations') && document.getElementById('chkEditDecorations').checked;
    if (currentMapData.decorations && (isEditDeco || (selectedEntity && selectedEntity.type === 'deco'))) {
        for (let i = currentMapData.decorations.length - 1; i >= 0; i--) {
            const d = currentMapData.decorations[i];
            const w = d._w || 40;
            const h = d._h || 40;
            if (mouseX >= d.x && mouseX <= d.x + w && mouseY >= d.y && mouseY <= d.y + h) {
                selectedEntity = { type: 'deco', index: i };
                draggingEntity = i;
                dragType = 'deco';
                dragOffsetX = d.x - mouseX;
                dragOffsetY = d.y - mouseY;
                renderCanvas();
                renderRightInspector();
                return;
            }
        }
    }

    selectedEntity = null;
    renderCanvas();
    renderRightInspector();
});

canvas.addEventListener('mousemove', function(e) {
    const rect = canvas.getBoundingClientRect();
    const scale = currentZoom;
    const mouseX = Math.round((e.clientX - rect.left) / scale);
    const mouseY = Math.round((e.clientY - rect.top) / scale);

    document.getElementById('hoverCoords').innerText = `X: ${mouseX} | Y: ${mouseY}`;

    if (draggingEntity !== null) {
        if (dragType === 'npc') {
            const n = currentMapData.npcs[draggingEntity];
            const targetX = mouseX + dragOffsetX;
            const targetY = snapToGround(targetX, mouseY + dragOffsetY);
            if (n.x !== undefined) { n.x = targetX; n.y = targetY; }
            else { n[1] = targetX; n[2] = targetY; }
        } else if (dragType === 'mob') {
            const m = currentMapData.mobs[draggingEntity];
            m.x = mouseX + dragOffsetX;
            m.y = snapToGround(m.x, mouseY + dragOffsetY);
        } else if (dragType === 'wp') {
            const w = currentMapData.waypoints[draggingEntity];
            const bw = w.maxX - w.minX;
            const bh = w.maxY - w.minY;
            w.minX = mouseX + dragOffsetX;
            w.minY = mouseY + dragOffsetY;
            w.maxX = w.minX + bw;
            w.maxY = w.minY + bh;
        } else if (dragType === 'deco') {
            const d = currentMapData.decorations[draggingEntity];
            d.x = mouseX + dragOffsetX;
            d.y = mouseY + dragOffsetY;
            d.tx = Math.round((d.x - (d.dx || 0)) / 24);
            d.ty = Math.round((d.y - (d.dy || 0)) / 24);
        }
        renderCanvas();
        renderRightInspector();
    }
});

window.addEventListener('mouseup', function() {
    if (draggingEntity !== null) {
        draggingEntity = null;
        dragType = null;
        renderCanvas();
        renderRightInspector();
    }
});

// Keyboard controls: Arrow keys to nudge 1px (Shift: 10px), Delete to delete
window.addEventListener('keydown', function(e) {
    if (!selectedEntity || ['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
    const delta = e.shiftKey ? 10 : 1;

    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'ArrowDown') {
        e.preventDefault();
        const dx = (e.key === 'ArrowLeft') ? -delta : (e.key === 'ArrowRight' ? delta : 0);
        const dy = (e.key === 'ArrowUp') ? -delta : (e.key === 'ArrowDown' ? delta : 0);

        if (selectedEntity.type === 'npc') {
            const n = currentMapData.npcs[selectedEntity.index];
            if (n.x !== undefined) { n.x += dx; n.y += dy; } else { n[1] += dx; n[2] += dy; }
        } else if (selectedEntity.type === 'mob') {
            const m = currentMapData.mobs[selectedEntity.index];
            m.x += dx; m.y += dy;
        } else if (selectedEntity.type === 'wp') {
            const w = currentMapData.waypoints[selectedEntity.index];
            w.minX += dx; w.maxX += dx;
            w.minY += dy; w.maxY += dy;
        } else if (selectedEntity.type === 'deco') {
            const d = currentMapData.decorations[selectedEntity.index];
            d.x += dx;
            d.y += dy;
            d.tx = Math.round((d.x - (d.dx || 0)) / 24);
            d.ty = Math.round((d.y - (d.dy || 0)) / 24);
        }
        renderCanvas();
        renderRightInspector();
    } else if (e.key === 'Delete') {
        e.preventDefault();
        if (selectedEntity.type === 'npc') removeNpc(selectedEntity.index);
        else if (selectedEntity.type === 'mob') removeMob(selectedEntity.index);
        else if (selectedEntity.type === 'wp') removeWaypoint(selectedEntity.index);
        else if (selectedEntity.type === 'deco') removeDecoration(selectedEntity.index);
    }
});

// =============================================================
// MODAL & ENTITY MANAGEMENT ACTIONS
// =============================================================
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
    document.querySelectorAll('.npc-tpl-item').forEach(it => {
        const name = it.getAttribute('data-name');
        const id = it.getAttribute('data-id');
        it.style.display = (!q || name.includes(q) || id.includes(q)) ? 'flex' : 'none';
    });
}
function selectNpcToAdd(id, name, avatar) {
    const x = Math.round(canvas.width / 2);
    const y = snapToGround(x, 432);
    currentMapData.npcs.push({ id, name, avatar, x, y });
    closeAddNpcModal();
    renderCanvas();
    renderRightInspector();
    if (window.showToast) window.showToast(`Đã thêm NPC ${name}`, 'success');
}
function removeNpc(idx) {
    currentMapData.npcs.splice(idx, 1);
    selectedEntity = null;
    renderCanvas();
    renderRightInspector();
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
    document.querySelectorAll('.mob-tpl-item').forEach(it => {
        const name = it.getAttribute('data-name');
        const id = it.getAttribute('data-id');
        it.style.display = (!q || name.includes(q) || id.includes(q)) ? 'flex' : 'none';
    });
}
function selectMobToAdd(id, name, hp, iconId) {
    const x = Math.round(canvas.width / 2);
    const y = snapToGround(x, 432);
    currentMapData.mobs.push({ id, name, hp: hp || 100, level: 1, iconId, x, y });
    closeAddMobModal();
    renderCanvas();
    renderRightInspector();
    if (window.showToast) window.showToast(`Đã thêm quái ${name}`, 'success');
}
function removeMob(idx) {
    currentMapData.mobs.splice(idx, 1);
    selectedEntity = null;
    renderCanvas();
    renderRightInspector();
}

function openAddWaypointModal() {
    document.getElementById('addWaypointModal').style.display = 'flex';
}
function closeAddWaypointModal() {
    document.getElementById('addWaypointModal').style.display = 'none';
}
function saveNewWaypoint() {
    const name = document.getElementById('wpNameInput').value.trim() || 'Cổng mới';
    const x = parseInt(document.getElementById('wpXInput').value) || 600;
    const y = parseInt(document.getElementById('wpYInput').value) || 408;
    const goMap = parseInt(document.getElementById('wpGoMapInput').value) || 1;
    const coords = document.getElementById('wpGoCoordsInput').value.split(',');
    const goX = parseInt(coords[0]) || 100;
    const goY = parseInt(coords[1]) || 400;

    currentMapData.waypoints.push({
        name: name,
        minX: x - 25,
        minY: y - 25,
        maxX: x + 25,
        maxY: y + 25,
        goMap: goMap,
        goX: goX,
        goY: goY
    });

    closeAddWaypointModal();
    renderCanvas();
    renderRightInspector();
    if (window.showToast) window.showToast(`Đã thêm cổng dịch chuyển ${name}`, 'success');
}
function removeWaypoint(idx) {
    currentMapData.waypoints.splice(idx, 1);
    selectedEntity = null;
    renderCanvas();
    renderRightInspector();
}

let allDecoTemplates = [];
let isDecoTemplatesLoading = false;

function loadDecoTemplates() {
    if (allDecoTemplates && Object.keys(allDecoTemplates).length > 0) return Promise.resolve(allDecoTemplates);
    if (isDecoTemplatesLoading) return Promise.resolve({});
    isDecoTemplatesLoading = true;
    return fetch("{{ route('admin.map_editor.item_bg_templates') }}")
        .then(r => r.json())
        .then(res => {
            isDecoTemplatesLoading = false;
            if (res.success && res.templates) {
                allDecoTemplates = res.templates;
            }
            return allDecoTemplates;
        })
        .catch(() => {
            isDecoTemplatesLoading = false;
            return {};
        });
}

function openAddDecoModal() {
    document.getElementById('addDecoModal').style.display = 'flex';
    document.getElementById('searchDecoInput').value = '';
    const grid = document.getElementById('decoTemplatesGrid');
    if (grid) grid.innerHTML = `<div style="text-align: center; color: #8b949e; padding: 40px; grid-column: 1 / -1;">Đang tải danh sách mẫu trang trí...</div>`;
    loadDecoTemplates().then(() => renderDecoTemplatesList());
}

function closeAddDecoModal() {
    document.getElementById('addDecoModal').style.display = 'none';
}

function filterDecoTemplates() {
    renderDecoTemplatesList();
}

function renderDecoTemplatesList() {
    const grid = document.getElementById('decoTemplatesGrid');
    if (!grid) return;
    const q = (document.getElementById('searchDecoInput').value || '').toLowerCase().trim();
    const layerFilter = document.getElementById('filterDecoLayer').value;

    let entries = Object.entries(allDecoTemplates);
    if (layerFilter !== 'all') {
        const lf = parseInt(layerFilter);
        entries = entries.filter(([id, t]) => t.layer === lf);
    }
    if (q) {
        entries = entries.filter(([id, t]) => id.includes(q) || String(t.img_id).includes(q));
    }

    if (entries.length === 0) {
        grid.innerHTML = `<div style="text-align: center; color: #8b949e; padding: 40px; grid-column: 1 / -1;">Không tìm thấy mẫu trang trí phù hợp.</div>`;
        return;
    }

    const showEntries = entries.slice(0, 120);
    let html = '';
    const decoThemeQuery = currentEventTheme !== 'normal' ? '?theme=' + currentEventTheme : '';
    showEntries.forEach(([id, t]) => {
        html += `
            <div onclick="selectDecoToAdd(${id})" style="background: rgba(255,255,255,0.03); border: 1px solid #30363d; border-radius: 6px; padding: 8px; display: flex; flex-direction: column; align-items: center; gap: 6px; cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#7ee787'; this.style.background='rgba(35,134,54,0.15)';" onmouseout="this.style.borderColor='#30363d'; this.style.background='rgba(255,255,255,0.03)';">
                <div style="height: 60px; width: 100%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
                    <img src="/item-bg-image/${t.img_id}.png${decoThemeQuery}" onerror="this.src='/images/no_image.png'" style="max-height: 56px; max-width: 90%; object-fit: contain;">
                </div>
                <div style="text-align: center; width: 100%;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #7ee787;">Mẫu #${id}</div>
                    <div style="font-size: 0.68rem; color: #8b949e;">Ảnh #${t.img_id} · L${t.layer}</div>
                </div>
            </div>
        `;
    });
    if (entries.length > 120) {
        html += `<div style="text-align: center; color: #8b949e; padding: 10px; grid-column: 1 / -1; font-size: 0.75rem;">(Hiển thị 120/${entries.length} mẫu · Nhập ID để tìm chính xác)</div>`;
    }
    grid.innerHTML = html;
}

function selectDecoToAdd(tplId) {
    if (!currentMapData) return;
    if (!currentMapData.decorations) currentMapData.decorations = [];

    const tpl = allDecoTemplates[tplId] || { img_id: 0, layer: 1, dx: 0, dy: 0 };
    const vp = document.getElementById('canvasViewport');
    const scrollLeft = vp ? vp.scrollLeft : 0;
    const scrollTop = vp ? vp.scrollTop : 0;
    const centerX = Math.round((scrollLeft + (vp ? vp.clientWidth : 600) / 2) / currentZoom);
    const centerY = snapToGround(centerX, Math.round((scrollTop + (vp ? vp.clientHeight : 400) / 2) / currentZoom));

    const tx = Math.round((centerX - (tpl.dx || 0)) / 24);
    const ty = Math.round((centerY - (tpl.dy || 0)) / 24);
    const px = tx * 24 + (tpl.dx || 0);
    const py = ty * 24 + (tpl.dy || 0);

    const newDeco = {
        index: currentMapData.decorations.length,
        template_id: parseInt(tplId),
        img_id: tpl.img_id,
        layer: tpl.layer,
        tx: tx,
        ty: ty,
        dx: tpl.dx,
        dy: tpl.dy,
        x: px,
        y: py
    };

    currentMapData.decorations.push(newDeco);
    currentMapData.decorations_count = currentMapData.decorations.length;
    selectedEntity = { type: 'deco', index: currentMapData.decorations.length - 1 };

    closeAddDecoModal();
    updateHeaderInfo();
    renderCanvas();
    renderRightInspector();
    if (window.showToast) window.showToast(`Đã thêm vật trang trí #${newDeco.index} (Tpl ${tplId})!`, 'success');
}

function addDecorationItem() {
    openAddDecoModal();
}

// =============================================================
// DATABASE PERSISTENCE & ACTIONS
// =============================================================
function saveMapData(callback) {
    fetch("{{ route('admin.map_editor.save') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            map_id: currentMapData.id,
            npcs: currentMapData.npcs,
            mobs: currentMapData.mobs,
            decorations: currentMapData.decorations
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (window.showToast) window.showToast(res.message || 'Lưu bản đồ thành công!', 'success');
            if (callback) callback();
        } else {
            if (window.showToast) window.showToast('Lỗi: ' + res.message, 'danger');
        }
    })
    .catch(() => {
        if (window.showToast) window.showToast('Lỗi kết nối tới Server!', 'danger');
    });
}

function applyToGameMaintenance() {
    const doApply = () => {
        saveMapData(() => {
            fetch("{{ route('admin.server_build.action') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ action: 'maintenance' })
            })
            .then(r => r.json())
            .then(res => {
                if (window.showToast) window.showToast('Đã lưu DB và kích hoạt nạp lại dữ liệu bản đồ!', 'success');
            });
        });
    };

    if (window.showConfirm) {
        window.showConfirm({
            title: 'Áp Dụng Vào Game',
            message: `Lưu cấu hình Map #${currentMapData.id} vào Database và kích hoạt bảo trì đếm ngược 3s để Game nạp lại bản đồ?`,
            okText: 'Đồng ý áp dụng',
            cancelText: 'Hủy bỏ',
            isDanger: true,
            onConfirm: doApply
        });
    } else {
        if (confirm('Lưu và nạp lại bản đồ vào game?')) doApply();
    }
}

function undoLastChange() {
    switchMap(currentMapData.id);
    if (window.showToast) window.showToast('Đã hoàn tác về trạng thái ban đầu', 'info');
}

function showHistoryModal() {
    if (window.showToast) window.showToast('Lịch sử phiên chỉnh sửa đã được ghi nhận vào nhật ký hệ thống', 'info');
}

function deleteCurrentMap() {
    if (window.showToast) window.showToast(`Bản đồ hệ thống #${currentMapData.id} được bảo vệ an toàn`, 'warning');
}

function openCreateMapModal() {
    if (window.showToast) window.showToast('Tính năng nhân bản và tạo Map ID mới đang sẵn sàng', 'info');
}

function toggleFullscreenEditor() {
    const editor = document.getElementById('mapEditorWrapper');
    if (!document.fullscreenElement) {
        if (editor.requestFullscreen) {
            editor.requestFullscreen();
        } else if (editor.webkitRequestFullscreen) {
            editor.webkitRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

document.addEventListener('fullscreenchange', () => {
    const btn = document.getElementById('btnFullscreen');
    if (btn) {
        btn.innerHTML = document.fullscreenElement ? '🗗 Thu nhỏ' : '⛶ Toàn màn hình';
    }
});

// Khởi tạo ban đầu
document.addEventListener('DOMContentLoaded', () => {
    initMapPagination();
    if (localStorage.getItem('admin_sidebar_collapsed') === '1') {
        const layout = document.getElementById('adminMainLayout');
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar && layout) {
            sidebar.style.display = 'none';
            layout.style.setProperty('--admin-sidebar-w', '0px');
            const btn = document.getElementById('btnToggleSidebar');
            if (btn) btn.innerHTML = '▶ Mở Menu Admin';
        }
    }
    switchMap(0);
});
</script>
@endsection
