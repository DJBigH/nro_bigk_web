@extends('admin.layout')

@section('admin_title', 'Server & Build - NRO Panel')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-family: 'Orbitron', sans-serif; font-size: 1.35rem; color: #fff; margin: 0; font-weight: 800; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px;">
            🖥️ Server & Build
        </h2>
    </div>
    <div style="font-size: 0.8rem; color: #718096; font-family: monospace;">
        systemd · allowlisted ops help
    </div>
</div>

<!-- 1. Top System Services Badges -->
<div class="card" style="margin-bottom: 18px; padding: 12px 18px; background: rgba(13, 17, 23, 0.95); border: 1px solid rgba(48, 54, 61, 0.8); border-radius: 8px;">
    <div style="display: flex; gap: 18px; align-items: center; flex-wrap: wrap; font-size: 0.86rem; font-family: 'Consolas', monospace;">
        <span style="display: flex; align-items: center; gap: 6px;">
            <span id="dot-nro-game" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $isGameActive ? '#238636' : '#da3633' }}; box-shadow: 0 0 8px {{ $isGameActive ? '#238636' : '#da3633' }};"></span>
            <span style="color: #c9d1d9;">nro-game · <strong id="status-nro-game" style="color: {{ $isGameActive ? '#3fb950' : '#f85149' }};">{{ $isGameActive ? 'active' : 'inactive' }}</strong></span>
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span id="dot-mysql" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $isMysqlActive ? '#238636' : '#da3633' }}; box-shadow: 0 0 8px {{ $isMysqlActive ? '#238636' : '#da3633' }};"></span>
            <span style="color: #c9d1d9;">mysql · <strong id="status-mysql" style="color: {{ $isMysqlActive ? '#3fb950' : '#f85149' }};">{{ $isMysqlActive ? 'active' : 'inactive' }}</strong></span>
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #238636; box-shadow: 0 0 8px #238636;"></span>
            <span style="color: #c9d1d9;">nginx · <strong style="color: #3fb950;">active</strong></span>
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #238636; box-shadow: 0 0 8px #238636;"></span>
            <span style="color: #c9d1d9;">nro-panel · <strong style="color: #3fb950;">active</strong></span>
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <a href="{{ route('admin.events') }}" style="color: #ffd700; text-decoration: none; display: flex; align-items: center; gap: 5px;" title="Xem và điều khiển 11 sự kiện Realtime">
                <span>🎉</span>
                <span style="color: #c9d1d9;">event · <strong style="color: #58a6ff;">Quản Lý Sự Kiện →</strong></span>
            </a>
        </span>
    </div>
</div>

<!-- 2. Game Service Card -->
<div class="card" style="margin-bottom: 20px; padding: 18px 20px; background: rgba(13, 17, 23, 0.95); border: 1px solid rgba(48, 54, 61, 0.8); border-radius: 8px;">
    <h3 style="color: #f0f6fc; font-size: 0.95rem; margin-top: 0; margin-bottom: 14px; font-weight: 700; font-family: 'Segoe UI', sans-serif;">
        Game service
    </h3>

    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
        <button type="button" class="btn" style="background: #1f6feb; color: #fff; font-weight: 600; padding: 6px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; font-size: 0.85rem;" onclick="runServiceAction('start')">
            Start
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; font-weight: 600; padding: 6px 16px; border: 1px solid #30363d; border-radius: 6px; font-size: 0.85rem;" onclick="runServiceAction('stop')">
            Stop
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; font-weight: 600; padding: 6px 16px; border: 1px solid #30363d; border-radius: 6px; font-size: 0.85rem;" onclick="runServiceAction('restart')">
            Restart
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; font-weight: 600; padding: 6px 16px; border: 1px solid #30363d; border-radius: 6px; font-size: 0.85rem;" onclick="runServiceAction('backup')">
            Backup
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; font-weight: 600; padding: 6px 16px; border: 1px solid #30363d; border-radius: 6px; font-size: 0.85rem;" onclick="runServiceAction('build')">
            Build
        </button>
        <button type="button" class="btn" style="background: #da3633; color: #fff; font-weight: 600; padding: 6px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; font-size: 0.85rem;" onclick="confirmAction('maintenance', 'Bảo trì máy chủ Game', 'Bắt đầu đếm ngược 30s để kick và lưu dữ liệu toàn bộ người chơi?', true)">
            Bảo trì
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; font-weight: 600; padding: 6px 16px; border: 1px solid #30363d; border-radius: 6px; font-size: 0.85rem;" onclick="confirmAction('clean_build', 'Clean Build', 'Xóa sạch cache classes và biên dịch lại toàn bộ mã nguồn jar?', false)">
            Clean build
        </button>
        <button type="button" class="btn" style="background: #6e1717; color: #ffa39e; font-weight: 600; padding: 6px 16px; border: 1px solid #a8071a; border-radius: 6px; font-size: 0.85rem;" onclick="confirmAction('reset_server', 'Reset Dữ Liệu Server', 'CẢNH BÁO: Hành động này sẽ dừng game và xóa nhân vật thường (giữ lại tài khoản Admin). Bạn có chắc chắn không?', true)">
            Reset server
        </button>
    </div>

    <div style="font-size: 0.78rem; color: #8b949e; line-height: 1.6; border-top: 1px solid rgba(48,54,61,0.5); padding-top: 10px;">
        <div><strong>Bảo trì</strong> — countdown rồi kick/save player.</div>
        <div><strong>Clean build</strong> — mvn clean package (hoặc dọn dẹp classes và đóng gói lại soicon.jar).</div>
        <div><strong>Reset server</strong> — dừng game, xóa nhân vật, account (giữ admin), lịch sử nhập code, giao dịch, buff admin, clan.</div>
    </div>
</div>

<!-- 3. IP Dev Allowlist Card -->
<div class="card" style="margin-bottom: 20px; padding: 18px 20px; background: rgba(13, 17, 23, 0.95); border: 1px solid rgba(48, 54, 61, 0.8); border-radius: 8px;">
    <h3 style="color: #f0f6fc; font-size: 0.95rem; margin-top: 0; margin-bottom: 8px; font-weight: 700; font-family: 'Segoe UI', sans-serif;">
        IP cho tài khoản dev (nrodev)
    </h3>
    <div style="font-size: 0.78rem; color: #8b949e; margin-bottom: 12px; line-height: 1.5;">
        <div>IP panel thấy: <code style="color: #58a6ff; font-family: monospace;">{{ $detectedIp }}</code> (x-real-ip (nginx))</div>
        <div>Vào panel qua Tailscale thì IP này là 100.x (đã được phép sẵn). Game login từ Internet dùng IP public.</div>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; align-items: center;">
        <input type="text" id="devIpInput" value="{{ $detectedIp }}" class="form-input" style="max-width: 380px; margin-bottom: 0; background: #0d1117; border: 1px solid #30363d; color: #f0f6fc; font-family: 'Consolas', monospace; font-size: 0.88rem; padding: 6px 12px; border-radius: 6px;">
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; font-size: 0.82rem; padding: 6px 12px; border-radius: 6px;" onclick="useDetectedIp()">
            Dùng IP panel thấy
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; font-size: 0.82rem; padding: 6px 12px; border-radius: 6px;" onclick="fetchPublicIp()">
            Lấy IP public của tôi
        </button>
        <button type="button" class="btn" style="background: #1f6feb; color: #fff; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); font-size: 0.82rem; padding: 6px 14px; border-radius: 6px;" onclick="addDevIp()">
            Thêm IP của tôi cho nrodev
        </button>
        <button type="button" class="btn" style="background: #21262d; color: #c9d1d9; border: 1px solid #30363d; font-size: 0.82rem; padding: 6px 12px; border-radius: 6px;" onclick="reloadAllowlist()">
            Áp dụng lại (reload)
        </button>
    </div>

    <div style="font-size: 0.8rem; color: #8b949e; margin-bottom: 8px;">
        Allowlist trong server.properties:
    </div>
    <div id="allowlistBadges" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
        @foreach($allowlist as $ipItem)
            <span style="font-family: monospace; font-size: 0.78rem; background: rgba(35, 134, 54, 0.15); color: #3fb950; border: 1px solid rgba(63, 185, 80, 0.3); padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px;">
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #238636;"></span>
                {{ $ipItem }}
            </span>
        @endforeach
    </div>
    <div style="font-size: 0.75rem; color: #6e7681;">
        Bấm "Áp dụng lại (reload)" để xem danh sách game đang dùng.
    </div>
</div>

<!-- 4. Job Output Live Console Terminal -->
<div class="card" style="margin-bottom: 20px; padding: 18px 20px; background: rgba(13, 17, 23, 0.95); border: 1px solid rgba(48, 54, 61, 0.8); border-radius: 8px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h3 style="color: #f0f6fc; font-size: 0.95rem; margin: 0; font-weight: 700; font-family: 'Segoe UI', sans-serif;">
            Job output
        </h3>
        <button type="button" class="btn" style="background: transparent; color: #8b949e; border: none; font-size: 0.78rem; cursor: pointer; padding: 2px 6px;" onclick="clearJobOutput()">
            Clear terminal
        </button>
    </div>
    <div id="jobOutputConsole" style="background: #040d1a; border: 1px solid #1e293b; border-radius: 6px; padding: 14px 16px; font-family: 'Consolas', 'Courier New', monospace; font-size: 0.85rem; color: #a5d6ff; min-height: 120px; max-height: 350px; overflow-y: auto; white-space: pre-wrap; line-height: 1.5; box-shadow: inset 0 0 10px rgba(0,0,0,0.6);">Chưa chạy job.</div>
</div>

<script>
const JOB_LOG_STORAGE_KEY = 'nro_server_build_job_output';
const JOB_LOG_MAX_AGE_MS = 15 * 60 * 1000; // 15 phút (900.000 ms)

function saveJobOutputToStorage(text) {
    try {
        localStorage.setItem(JOB_LOG_STORAGE_KEY, JSON.stringify({
            timestamp: Date.now(),
            content: text
        }));
    } catch (e) {}
}

function loadJobOutputFromStorage() {
    try {
        const raw = localStorage.getItem(JOB_LOG_STORAGE_KEY);
        if (!raw) return;
        const data = JSON.parse(raw);
        const age = Date.now() - (data.timestamp || 0);
        if (age < JOB_LOG_MAX_AGE_MS && data.content && data.content.trim() !== 'Chưa chạy job.') {
            const box = document.getElementById('jobOutputConsole');
            if (box) {
                box.innerText = data.content;
                box.scrollTop = box.scrollHeight;
            }
        } else {
            localStorage.removeItem(JOB_LOG_STORAGE_KEY);
        }
    } catch (e) {}
}

function appendJobLog(text) {
    const box = document.getElementById('jobOutputConsole');
    if (!box) return;
    if (box.innerText.trim() === 'Chưa chạy job.') {
        box.innerText = '';
    }
    const timestamp = new Date().toLocaleTimeString();
    box.innerText += `[${timestamp}] ${text}\n`;
    box.scrollTop = box.scrollHeight;
    saveJobOutputToStorage(box.innerText);
}

function clearJobOutput() {
    const box = document.getElementById('jobOutputConsole');
    if (box) box.innerText = 'Chưa chạy job.';
    localStorage.removeItem(JOB_LOG_STORAGE_KEY);
}

document.addEventListener('DOMContentLoaded', () => {
    loadJobOutputFromStorage();
});

function runServiceAction(action) {
    appendJobLog(`Đang gửi lệnh '${action}' tới hệ thống...`);
    fetch("{{ route('admin.server_build.action') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ action: action })
    })
    .then(r => r.json())
    .then(res => {
        if (res.output) {
            appendJobLog(res.output);
        } else {
            appendJobLog(res.message || 'Thực hiện thành công!');
        }
        checkServerStatus();
        if (window.showToast) window.showToast(res.message || 'Thao tác hoàn tất', res.success ? 'success' : 'danger');
    })
    .catch(err => {
        appendJobLog(`[LỖI KẾT NỐI] ${err.message}`);
        if (window.showToast) window.showToast('Lỗi kết nối tới Server', 'danger');
    });
}

function confirmAction(action, title, message, isDanger) {
    const doIt = () => runServiceAction(action);
    if (window.showConfirm) {
        window.showConfirm({
            title: title,
            message: message,
            okText: 'Xác nhận thực hiện',
            cancelText: 'Hủy bỏ',
            isDanger: isDanger,
            onConfirm: doIt
        });
    } else {
        if (confirm(`${title}\n\n${message}`)) doIt();
    }
}

function useDetectedIp() {
    document.getElementById('devIpInput').value = "{{ $detectedIp }}";
}

function fetchPublicIp() {
    appendJobLog("Đang tra cứu IP public hiện tại...");
    fetch('https://api.ipify.org?format=json')
        .then(r => r.json())
        .then(d => {
            if (d.ip) {
                document.getElementById('devIpInput').value = d.ip;
                appendJobLog(`Đã lấy IP public: ${d.ip}`);
            }
        })
        .catch(() => {
            appendJobLog("[LỖI] Không thể tự động lấy IP public từ internet.");
        });
}

function addDevIp() {
    const ip = document.getElementById('devIpInput').value.trim();
    if (!ip) return;
    appendJobLog(`Đang thêm IP '${ip}' vào danh sách dev allowlist...`);
    fetch("{{ route('admin.server_build.ip') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ action: 'add', ip: ip })
    })
    .then(r => r.json())
    .then(res => {
        appendJobLog(res.message);
        renderAllowlist(res.allowlist);
        if (window.showToast) window.showToast(res.message, 'success');
    })
    .catch(err => {
        appendJobLog(`[LỖI] ${err.message}`);
    });
}

function reloadAllowlist() {
    appendJobLog("Đang tải lại danh sách allowlist...");
    fetch("{{ route('admin.server_build.ip') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ action: 'reload' })
    })
    .then(r => r.json())
    .then(res => {
        appendJobLog(res.message);
        renderAllowlist(res.allowlist);
        if (window.showToast) window.showToast(res.message, 'info');
    });
}

function renderAllowlist(list) {
    if (!list) return;
    const container = document.getElementById('allowlistBadges');
    container.innerHTML = '';
    list.forEach(ip => {
        container.innerHTML += `
            <span style="font-family: monospace; font-size: 0.78rem; background: rgba(35, 134, 54, 0.15); color: #3fb950; border: 1px solid rgba(63, 185, 80, 0.3); padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px;">
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #238636;"></span>
                ${ip}
            </span>
        `;
    });
}

function checkServerStatus() {
    fetch("{{ route('admin.server_build.status') }}")
        .then(r => r.json())
        .then(data => {
            const gameActive = data.nro_game;
            document.getElementById('dot-nro-game').style.background = gameActive ? '#238636' : '#da3633';
            document.getElementById('dot-nro-game').style.boxShadow = `0 0 8px ${gameActive ? '#238636' : '#da3633'}`;
            document.getElementById('status-nro-game').innerText = gameActive ? 'active' : 'inactive';
            document.getElementById('status-nro-game').style.color = gameActive ? '#3fb950' : '#f85149';
        })
        .catch(() => {});
}

// Poll server status every 6 seconds
setInterval(checkServerStatus, 6000);
</script>
@endsection
