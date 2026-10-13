@extends('admin.layout')

@section('title', 'Quản Lý Hệ Thống Bot AI Thông Minh - Admin NRO BigK')

@section('admin_content')
<!-- Header Card -->
<div class="card" style="margin-bottom: 25px; border-left: 4px solid #00e5ff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #00e5ff; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            🤖 QUẢN LÝ 5 LOẠI BOT AI THÔNG MINH REALTIME
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Hệ thống Bot AI thông minh: 🌾 Farm cày cuốc, ⚔️ PK Cờ Đen đồ sát, 🎯 Săn Boss & Mabư, 🥋 ĐH Võ Thuật và ⭐ Đại chiến Ngọc Rồng Sao Đen!
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <button class="btn btn-secondary" onclick="location.reload()" style="font-size: 0.85rem; padding: 8px 15px;">
            🔄 LÀM MỚI
        </button>
    </div>
</div>

<!-- Stats Counter Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div class="card" style="padding: 15px; text-align: center; border-top: 3px solid #00e5ff;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #00e5ff;">{{ $botCount ?? count($allBots ?? $bots) }}</div>
        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">Tổng Bot Online</div>
    </div>
    <div class="card" style="padding: 15px; text-align: center; border-top: 3px solid #4caf50;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #4caf50;">{{ $botCountFarm ?? 0 }}</div>
        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">🌾 Bot Farm (Cày/Up)</div>
    </div>
    <div class="card" style="padding: 15px; text-align: center; border-top: 3px solid #f44336;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #f44336;">{{ $botCountPk ?? 0 }}</div>
        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">⚔️ Bot PK (Cờ Đen)</div>
    </div>
    <div class="card" style="padding: 15px; text-align: center; border-top: 3px solid #ff9800;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #ff9800;">{{ $botCountBoss ?? 0 }}</div>
        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">🎯 Bot Săn Boss & Bư</div>
    </div>
    <div class="card" style="padding: 15px; text-align: center; border-top: 3px solid #9c27b0;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #9c27b0;">{{ $botCountDhvt ?? 0 }}</div>
        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">🥋 Bot ĐH Võ Thuật</div>
    </div>
    <div class="card" style="padding: 15px; text-align: center; border-top: 3px solid #ffeb3b;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #ffeb3b;">{{ $botCountNrsd ?? 0 }}</div>
        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">⭐ Bot Sao Đen (Bang)</div>
    </div>
</div>

<!-- Boss Kill Rate Slider & Auto Chat -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <!-- Card Cấu hình Tỉ lệ Bot ăn Boss -->
    <div class="card" style="border: 1px solid rgba(255, 152, 0, 0.3); background: rgba(13, 20, 36, 0.7);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="font-size: 1.8rem;">🎯</div>
            <div>
                <h3 style="color: #ff9800; font-size: 1.05rem; margin: 0 0 4px 0;">Cài Đặt Tỉ Lệ Bot Kết Liễu Boss (Last Hit)</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem; margin: 0;">
                    Bot gây dame thật vào Boss. Khi Boss dưới 5% HP, Bot chỉ có tỉ lệ này để kết liễu nhặt đồ, còn lại nhường người chơi thật!
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 15px; margin-top: 15px; flex-wrap: wrap;">
            <input type="range" id="bossKillRateSlider" min="5" max="10" step="1" value="{{ $bossKillRate ?? 8 }}" oninput="document.getElementById('bossKillRateVal').innerText = this.value + '%'" style="flex: 1; accent-color: #ff9800; cursor: pointer;">
            <span id="bossKillRateVal" style="font-size: 1.2rem; font-weight: 700; color: #ff9800; min-width: 45px; text-align: center;">{{ $bossKillRate ?? 8 }}%</span>
            <button class="btn" style="background: #ff9800; color: #000; font-weight: 700; padding: 7px 16px;" onclick="saveBossKillRate()">
                💾 Lưu Cấu Hình
            </button>
        </div>
        <div style="display: flex; justify-content: space-between; color: #888; font-size: 0.75rem; margin-top: 5px;">
            <span>Tối thiểu: 5%</span>
            <span>Mặc định: 8%</span>
            <span>Tối đa: 10%</span>
        </div>
    </div>

    <!-- Auto Chat Status Card -->
    <div class="card" style="border: 1px solid rgba(0, 229, 255, 0.2); background: rgba(13, 20, 36, 0.6);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="font-size: 1.8rem;">💬</div>
            <div>
                <h3 style="color: #fff; font-size: 1.05rem; margin: 0 0 4px 0;">Bot Tự Động Chat Giả Lập Người Chơi</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem; margin: 0;">
                    Bot tự động gửi tin nhắn chat vui nhộn, hỏi đáp game, gạ kèo PK và chiến báo Boss để tạo không khí đông đúc.
                </p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
            <div>
                @if($chatEnabled)
                    <span class="badge" style="background: rgba(0, 230, 118, 0.15); color: #00e676; border: 1px solid #00e676; font-size: 0.85rem; padding: 6px 14px; font-weight: 700;">
                        ● ĐANG BẬT TỰ ĐỘNG CHAT
                    </span>
                @else
                    <span class="badge" style="background: rgba(255, 23, 68, 0.15); color: #ff1744; border: 1px solid #ff1744; font-size: 0.85rem; padding: 6px 14px; font-weight: 700;">
                        ○ ĐÃ TẮT TỰ ĐỘNG CHAT
                    </span>
                @endif
            </div>

            <div>
                @if($chatEnabled)
                    <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff5252; padding: 6px 14px; font-size: 0.85rem;" onclick="toggleChat(false)">
                        Tắt Auto Chat
                    </button>
                @else
                    <button class="btn" style="background: #00e676; padding: 6px 14px; font-size: 0.85rem; font-weight: 700;" onclick="toggleChat(true)">
                        Bật Auto Chat
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 5 Archetypes Spawn Control Panel -->
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(0, 229, 255, 0.3);">
    <h3 style="color: #00e5ff; font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
        ⚡ THẢ BOT THEO 5 LOẠI ARCHETYPE THÔNG MINH
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
        <!-- 1. Bot Farm -->
        <div style="background: rgba(76, 175, 80, 0.08); border: 1px solid rgba(76, 175, 80, 0.3); border-radius: 8px; padding: 15px;">
            <div style="font-weight: 700; color: #4caf50; font-size: 0.95rem; margin-bottom: 5px;">🌾 1. Bot Farm (Cày Cuốc & Up Đệ)</div>
            <div style="font-size: 0.8rem; color: #aaa; margin-bottom: 10px;">Dắt đệ tử đi up; 50% nhường bãi khi người thật vào, 50% đứng lì tranh bãi quái.</div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button class="btn" style="background: #4caf50; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(0, 1)">+1</button>
                <button class="btn" style="background: #4caf50; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(0, 3)">+3</button>
                <button class="btn" style="background: #4caf50; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(0, 5)">+5</button>
                <button class="btn" style="background: #388e3c; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(0, 10)">+10</button>
            </div>
        </div>

        <!-- 2. Bot PK -->
        <div style="background: rgba(244, 67, 54, 0.08); border: 1px solid rgba(244, 67, 54, 0.3); border-radius: 8px; padding: 15px;">
            <div style="font-weight: 700; color: #f44336; font-size: 0.95rem; margin-bottom: 5px;">⚔️ 2. Bot PK (Cờ Đen / Chiến Thần)</div>
            <div style="font-size: 0.8rem; color: #aaa; margin-bottom: 10px;">Mở Cờ Đen; combo đấm đá liên hoàn; chết 50% hs tại chỗ, 50% về làng bay lại trả thù; kéo hội.</div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button class="btn" style="background: #f44336; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(1, 1)">+1</button>
                <button class="btn" style="background: #f44336; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(1, 3)">+3</button>
                <button class="btn" style="background: #f44336; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(1, 5)">+5</button>
                <button class="btn" style="background: #d32f2f; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(1, 10)">+10</button>
            </div>
        </div>

        <!-- 3. Bot Săn Boss -->
        <div style="background: rgba(255, 152, 0, 0.08); border: 1px solid rgba(255, 152, 0, 0.3); border-radius: 8px; padding: 15px;">
            <div style="font-weight: 700; color: #ff9800; font-size: 0.95rem; margin-bottom: 5px;">🎯 3. Bot Săn Boss & Ma Bư</div>
            <div style="font-size: 0.8rem; color: #aaa; margin-bottom: 10px;">Tự bay tìm boss dã ngoại & Mabư (12h-13h30); dame thật; last hit 5-10% cấu hình, nhặt đồ rơi.</div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button class="btn" style="background: #ff9800; color: #000; padding: 5px 10px; font-size: 0.8rem; font-weight: 700;" onclick="spawnBotType(2, 1)">+1</button>
                <button class="btn" style="background: #ff9800; color: #000; padding: 5px 10px; font-size: 0.8rem; font-weight: 700;" onclick="spawnBotType(2, 3)">+3</button>
                <button class="btn" style="background: #ff9800; color: #000; padding: 5px 10px; font-size: 0.8rem; font-weight: 700;" onclick="spawnBotType(2, 5)">+5</button>
                <button class="btn" style="background: #f57c00; color: #fff; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(2, 10)">+10</button>
            </div>
        </div>

        <!-- 4. Bot ĐHVT -->
        <div style="background: rgba(156, 39, 176, 0.08); border: 1px solid rgba(156, 39, 176, 0.3); border-radius: 8px; padding: 15px;">
            <div style="font-weight: 700; color: #ba68c8; font-size: 0.95rem; margin-bottom: 5px;">🥋 4. Bot Đại Hội Võ Thuật</div>
            <div style="font-size: 0.8rem; color: #aaa; margin-bottom: 10px;">Chỉ tham gia giải Nhi Đồng & Siêu Cấp 1, 2, 3; tự bay về Map 52 đăng ký và lên đài thi đấu.</div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button class="btn" style="background: #9c27b0; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(3, 1)">+1</button>
                <button class="btn" style="background: #9c27b0; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(3, 3)">+3</button>
                <button class="btn" style="background: #9c27b0; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(3, 5)">+5</button>
                <button class="btn" style="background: #7b1fa2; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(3, 10)">+10</button>
            </div>
        </div>

        <!-- 5. Bot NRSD -->
        <div style="background: rgba(255, 235, 59, 0.08); border: 1px solid rgba(255, 235, 59, 0.3); border-radius: 8px; padding: 15px;">
            <div style="font-weight: 700; color: #fff59d; font-size: 0.95rem; margin-bottom: 5px;">⭐ 5. Bot Ngọc Rồng Sao Đen</div>
            <div style="font-size: 0.8rem; color: #aaa; margin-bottom: 10px;">Gán 4 Bang ảo [CHIẾN THẦN], [SAIYAN VIP]...; vào map 85-91 lúc 20:15 - 21:30, nhặt/cướp ngọc.</div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button class="btn" style="background: #fbc02d; color: #000; padding: 5px 10px; font-size: 0.8rem; font-weight: 700;" onclick="spawnBotType(4, 1)">+1</button>
                <button class="btn" style="background: #fbc02d; color: #000; padding: 5px 10px; font-size: 0.8rem; font-weight: 700;" onclick="spawnBotType(4, 3)">+3</button>
                <button class="btn" style="background: #fbc02d; color: #000; padding: 5px 10px; font-size: 0.8rem; font-weight: 700;" onclick="spawnBotType(4, 5)">+5</button>
                <button class="btn" style="background: #f57f17; color: #fff; padding: 5px 10px; font-size: 0.8rem;" onclick="spawnBotType(4, 10)">+10</button>
            </div>
        </div>
    </div>

    <!-- Hàng Dừng Nhanh Bot -->
    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); margin-top: 20px; padding-top: 15px;">
        <span style="color: #ff8a80; font-size: 0.9rem; font-weight: 600;">Dừng Nhanh Số Lượng:</span>
        <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 6px 12px; font-size: 0.85rem;" onclick="stopCountBots(1)">-1 Bot</button>
        <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 6px 12px; font-size: 0.85rem;" onclick="stopCountBots(3)">-3 Bot</button>
        <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 6px 12px; font-size: 0.85rem;" onclick="stopCountBots(5)">-5 Bot</button>
        <button class="btn btn-secondary" style="border-color: #ff5252; color: #ff8a80; padding: 6px 12px; font-size: 0.85rem;" onclick="stopCountBots(10)">-10 Bot</button>
        
        <div style="margin-left: auto;">
            <button class="btn btn-danger" style="background: #d32f2f; padding: 7px 18px; font-weight: 700; box-shadow: 0 0 10px rgba(211,47,47,0.4);" onclick="stopAllBots()">
                🛑 DỌN DẸP / DỪNG TOÀN BỘ BOT
            </button>
        </div>
    </div>
</div>

<!-- Active Bots List Table -->
<div class="card" style="padding: 0;">
    <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <h3 style="color: #fff; font-size: 1.1rem; margin: 0;">DANH SÁCH BOT ĐANG HOẠT ĐỘNG TRONG GAME</h3>
            <button id="btnStopSelected" class="btn btn-danger" style="display: none; padding: 4px 12px; font-size: 0.8rem; background: #e53935; font-weight: 700; box-shadow: 0 0 10px rgba(229,57,53,0.5);" onclick="stopSelectedBots()">
                🛑 DỪNG <span id="selectedCount">0</span> BOT ĐÃ CHỌN
            </button>
        </div>
        <span style="color: #00e5ff; font-size: 0.9rem; font-weight: 700;">{{ count($allBots ?? $bots) }} Bot đang online</span>
    </div>

    @if(empty($bots) || $bots->isEmpty())
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 10px;">🤖</div>
            <p>Hiện tại không có Bot AI nào đang chạy hoặc Game Server chưa khởi động.</p>
            <button class="btn" style="background: #00bcd4; margin-top: 10px;" onclick="spawnBotType(0, 5)">Thả ngay 5 Bot Farm đầu tiên</button>
        </div>
    @else
        <div style="width: 100%; overflow-x: auto;">
            <table style="margin-top: 0; width: 100%; table-layout: fixed; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center; padding: 10px 6px;">
                            <input type="checkbox" id="selectAllBots" onclick="toggleSelectAllBots(this)" style="cursor: pointer; width: 15px; height: 15px;" title="Chọn tất cả bot trên trang">
                        </th>
                        <th style="width: 50px; padding: 10px 6px; font-size: 0.8rem;">ID</th>
                        <th style="width: 16%; padding: 10px 8px; font-size: 0.8rem;">Tên Bot</th>
                        <th style="width: 14%; padding: 10px 8px; font-size: 0.8rem;">Phân Loại</th>
                        <th style="width: 18%; padding: 10px 8px; font-size: 0.8rem;">Bản Đồ</th>
                        <th style="width: 10%; padding: 10px 8px; font-size: 0.8rem;">Khu Vực</th>
                        <th style="width: 13%; padding: 10px 8px; font-size: 0.8rem;">Sức Mạnh</th>
                        <th style="width: 13%; padding: 10px 8px; font-size: 0.8rem;">Máu (HP)</th>
                        <th style="width: 12%; text-align: right; padding: 10px 8px; font-size: 0.8rem;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bots as $bot)
                        @php
                            $safeBotName = addslashes($bot['name'] ?? 'Bot AI');
                            $type = $bot['type'] ?? 0;
                            $badgeColor = '#4caf50';
                            $badgeBg = 'rgba(76, 175, 80, 0.15)';
                            $badgeName = $bot['typeName'] ?? 'Farm';
                            if ($type == 1) {
                                $badgeColor = '#f44336';
                                $badgeBg = 'rgba(244, 67, 54, 0.15)';
                            } elseif ($type == 2) {
                                $badgeColor = '#ff9800';
                                $badgeBg = 'rgba(255, 152, 0, 0.15)';
                            } elseif ($type == 3) {
                                $badgeColor = '#ba68c8';
                                $badgeBg = 'rgba(186, 104, 200, 0.15)';
                            } elseif ($type == 4) {
                                $badgeColor = '#fff59d';
                                $badgeBg = 'rgba(255, 235, 59, 0.15)';
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center; padding: 10px 6px;">
                                <input type="checkbox" class="bot-checkbox" value="{{ $bot['id'] }}" onchange="onBotCheckChange()" style="cursor: pointer; width: 15px; height: 15px;">
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #888; font-size: 0.8rem; padding: 10px 6px;">{{ $bot['id'] ?? 'BOT' }}</td>
                            <td style="font-weight: 700; color: #00e5ff; font-size: 0.85rem; padding: 10px 8px; word-break: break-word;">
                                🤖 {{ $bot['name'] ?? 'Bot AI' }}
                                @if(!empty($bot['clan']))
                                    <div style="font-size: 0.72rem; color: #ffeb3b; font-weight: normal;">[{{ $bot['clan'] }}]</div>
                                @endif
                            </td>
                            <td style="padding: 10px 8px;">
                                <span class="badge" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeColor }}; font-size: 0.75rem; padding: 3px 8px; font-weight: 600;">
                                    {{ $badgeName }}
                                </span>
                            </td>
                            <td style="color: #fff; font-size: 0.82rem; padding: 10px 8px; word-break: break-word;">
                                <strong style="color: #00e5ff;">{{ $bot['mapName'] ?? 'Chưa vào Map' }}</strong> 
                                @if(isset($bot['mapId']) && $bot['mapId'] >= 0)
                                    <span style="color: #888; font-size: 0.75rem;">(Map {{ $bot['mapId'] }})</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; padding: 10px 8px;">
                                @if(isset($bot['zoneId']) && $bot['zoneId'] >= 0)
                                    <span style="color: #ff9800; font-weight: 700; font-size: 0.82rem;">Khu {{ $bot['zoneId'] }}</span>
                                @else
                                    <span style="color: #666;">-</span>
                                @endif
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #e91e63; font-weight: 600; font-size: 0.8rem; padding: 10px 8px;">
                                {{ number_format($bot['power'] ?? 0) }}
                            </td>
                            <td style="font-family: 'Orbitron', sans-serif; color: #4caf50; font-size: 0.8rem; padding: 10px 8px;">
                                {{ number_format($bot['hp'] ?? 0) }}
                            </td>
                            <td style="text-align: right; white-space: nowrap; padding: 10px 8px;">
                                <button class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; background: #d32f2f; white-space: nowrap; font-weight: 700;" onclick="stopSingleBot({{ $bot['id'] }}, '{{ $safeBotName }}')">
                                    🛑 Dừng
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 15px 20px;">
            {{ $bots->links() }}
        </div>
    @endif
</div>

<script>
// Thả Bot theo Loại (Archetype)
async function spawnBotType(type, count) {
    const typeNames = ['🌾 Bot Farm', '⚔️ Bot PK (Cờ Đen)', '🎯 Bot Săn Boss & Mabư', '🥋 Bot ĐH Võ Thuật', '⭐ Bot Sao Đen (Bang)'];
    const typeName = typeNames[type] || 'Bot AI';

    const confirmed = await window.showConfirm({
        title: 'THẢ BOT AI',
        message: `Bạn có đồng ý thả thêm <strong>${count} ${typeName}</strong> vào máy chủ không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;
    
    fetch("{{ route('admin.bots.spawn') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ count: count, type: type })
    })
    .then(r => r.json())
    .then(async data => {
        if (data.success || data.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', data.message || `Đã thả thành công ${count} ${typeName}!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', data.message || 'Không thể kết nối Game Server!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Lưu tỉ lệ Bot kết liễu Boss
async function saveBossKillRate() {
    const rate = parseInt(document.getElementById('bossKillRateSlider').value);
    fetch("{{ route('admin.bots.config') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ boss_kill_rate: rate })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã cập nhật tỉ lệ Bot kết liễu Boss thành ${rate}%!`, 'success');
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể lưu cấu hình!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng 1 con bot cụ thể theo ID
async function stopSingleBot(botId, botName) {
    const confirmed = await window.showConfirm({
        title: 'DỪNG BOT AI',
        message: `Bạn có đồng ý dừng <strong>Bot "${botName}"</strong> (ID: ${botId}) không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ bot_id: botId })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã dừng Bot ${botName} thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể dừng bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng N con bot nhanh
async function stopCountBots(count) {
    const confirmed = await window.showConfirm({
        title: 'DỪNG BOT AI',
        message: `Bạn có đồng ý dừng <strong>${count} Bot AI</strong> đang hoạt động không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ count: count })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã dừng ${count} bot thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể dừng bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng nhiều bot được tích chọn bằng checkbox
function toggleSelectAllBots(master) {
    const boxes = document.querySelectorAll('.bot-checkbox');
    boxes.forEach(b => b.checked = master.checked);
    onBotCheckChange();
}

function onBotCheckChange() {
    const checked = document.querySelectorAll('.bot-checkbox:checked');
    const btn = document.getElementById('btnStopSelected');
    const cnt = document.getElementById('selectedCount');
    if (checked.length > 0) {
        cnt.innerText = checked.length;
        btn.style.display = 'inline-flex';
    } else {
        btn.style.display = 'none';
    }
}

async function stopSelectedBots() {
    const checked = document.querySelectorAll('.bot-checkbox:checked');
    if (checked.length === 0) return;

    const ids = Array.from(checked).map(c => parseInt(c.value));
    const confirmed = await window.showConfirm({
        title: 'DỪNG NHIỀU BOT ĐÃ CHỌN',
        message: `Bạn có đồng ý dừng <strong>${ids.length} Bot AI</strong> đã chọn khỏi game không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ bot_ids: ids })
    })
    .then(r => r.json())
    .then(async res => {
        if (res.success || res.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', res.message || `Đã dừng ${ids.length} Bot đã chọn thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', res.message || 'Không thể dừng bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

// Dừng tất cả bot
async function stopAllBots() {
    const confirmed = await window.showConfirm({
        title: 'CẢNH BÁO: DỪNG TOÀN BỘ BOT',
        message: 'Bạn có chắc chắn muốn <strong>DỪNG & XÓA TOÀN BỘ BOT AI</strong> đang chạy trên máy chủ không?',
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: true
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.stop') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(r => r.json())
    .then(async data => {
        if (data.success || data.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', data.message || 'Đã dọn dẹp toàn bộ Bot thành công!', 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', data.message || 'Không thể dừng Bot!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}

async function toggleChat(enable) {
    const actionText = enable ? 'BẬT' : 'TẮT';
    const confirmed = await window.showConfirm({
        title: `${actionText} BOT AUTO CHAT`,
        message: `Bạn có đồng ý <strong>${actionText}</strong> tính năng Bot Tự Động Chat thế giới không?`,
        okText: 'Đồng ý',
        cancelText: 'Từ chối',
        isDanger: false
    });
    if (!confirmed) return;

    fetch("{{ route('admin.bots.toggle-chat') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ enabled: enable })
    })
    .then(r => r.json())
    .then(async data => {
        if (data.success || data.status === 'success') {
            await window.showModalAlert('THÀNH CÔNG', data.message || `Đã ${actionText} Bot Auto Chat thành công!`, 'success');
            location.reload();
        } else {
            await window.showModalAlert('LỖI', data.message || 'Không thể cập nhật!', 'error');
        }
    })
    .catch(() => window.showModalAlert('LỖI', 'Lỗi kết nối tới Server!', 'error'));
}
</script>
@endsection
