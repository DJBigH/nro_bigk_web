@extends('admin.layout')

@section('title', 'Quản Lý Bản Tải Game - Admin NRO BigK')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: var(--primary);">QUẢN LÝ BẢN TẢI GAME</h2>
    <button class="btn" onclick="openAddModal()">THÊM FILE TẢI GAME</button>
</div>

<!-- Downloads List -->
<div class="card" style="padding: 0;">
    @if($downloads->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa cấu hình bản tải game nào.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Bản Tải</th>
                    <th>Phiên Bản</th>
                    <th>Đường Dẫn URL</th>
                    <th>Dung Lượng</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($downloads as $file)
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $file->id }}</td>
                        <td style="font-weight: 700; color: #fff;">{{ $file->name }}</td>
                        <td>{{ $file->version ?? '-' }}</td>
                        <td style="font-size: 0.9rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <a href="{{ $file->url }}" target="_blank" style="color: var(--primary);">{{ $file->url }}</a>
                        </td>
                        <td>{{ $file->size ?? '-' }}</td>
                        <td>
                            @if($file->is_active == 1)
                                <span class="badge badge-success">ACTIVE</span>
                            @else
                                <span class="badge badge-danger">TẮT</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; margin-right: 5px;" onclick="openEditModal({{ $file }})">
                                Sửa
                            </button>
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deleteDownload({{ $file->id }})">
                                Xóa
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 15px 20px;">
            {{ $downloads->links() }}
        </div>
    @endif
</div>

<!-- Modal Add/Edit Download -->
<div id="download-modal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3 id="modal-title" style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            THÊM BẢN TẢI GAME MỚI
        </h3>
        
        <form id="download-form">
            <input type="hidden" id="form-download-id" name="id">
            
            <!-- Name -->
            <div class="form-group">
                <label class="form-label">Tên bản tải (VD: Client PC, Bản Android APK...)</label>
                <input type="text" id="form-name" name="name" class="form-input" placeholder="Nhập tên bản tải..." required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <!-- Version -->
                <div class="form-group">
                    <label class="form-label">Phiên bản (VD: 1.0.0)</label>
                    <input type="text" id="form-version" name="version" class="form-input" placeholder="Nhập phiên bản...">
                </div>

                <!-- Size -->
                <div class="form-group">
                    <label class="form-label">Dung lượng (VD: 25 MB, 102 MB)</label>
                    <input type="text" id="form-size" name="size" class="form-input" placeholder="Nhập dung lượng file...">
                </div>
            </div>

            <!-- URL -->
            <div class="form-group">
                <label class="form-label">Đường dẫn tải về (URL)</label>
                <input type="url" id="form-url" name="url" class="form-input" placeholder="Nhập liên kết tải file (HTTP/HTTPS)..." required>
            </div>

            <!-- Active -->
            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select id="form-active" name="is_active" class="form-input" required>
                    <option value="1">Kích hoạt</option>
                    <option value="0">Tắt</option>
                </select>
            </div>

            <button type="submit" id="modal-submit-btn" class="btn" style="width: 100%;">
                XÁC NHẬN
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let mode = 'add'; // 'add' or 'edit'

    // Modal controls
    function openAddModal() {
        mode = 'add';
        document.getElementById('modal-title').innerText = 'THÊM BẢN TẢI GAME MỚI';
        document.getElementById('form-download-id').value = '';
        document.getElementById('download-form').reset();
        document.getElementById('download-modal').classList.add('show');
    }

    function openEditModal(file) {
        mode = 'edit';
        document.getElementById('modal-title').innerText = 'CẬP NHẬT BẢN TẢI GAME';
        document.getElementById('form-download-id').value = file.id;
        document.getElementById('form-name').value = file.name;
        document.getElementById('form-version').value = file.version || '';
        document.getElementById('form-size').value = file.size || '';
        document.getElementById('form-url').value = file.url;
        document.getElementById('form-active').value = file.is_active;
        document.getElementById('download-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('download-modal').classList.remove('show');
    }

    // Submit download link Form
    document.getElementById('download-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        const downloadId = document.getElementById('form-download-id').value;
        const formData = new FormData(document.getElementById('download-form'));
        const data = Object.fromEntries(formData.entries());

        let url = '{{ route("admin.downloads.store") }}';
        let method = 'POST';

        if (mode === 'edit') {
            url = `{{ url('admin/downloads') }}/${downloadId}`;
            method = 'PUT';
        }

        try {
            const response = await window.csrfFetch(url, {
                method: method,
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                closeModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                window.showAlert(result.message, 'error');
                btn.disabled = false;
                btn.innerText = 'XÁC NHẬN';
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
            btn.disabled = false;
            btn.innerText = 'XÁC NHẬN';
        }
    });

    // Delete download
    async function deleteDownload(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa bản tải game này?')) {
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ url('admin/downloads') }}/${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                window.showAlert(result.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                window.showAlert(result.message, 'error');
            }
        } catch (error) {
            window.showAlert('Lỗi kết nối.', 'error');
        }
    }
</script>
@endsection
