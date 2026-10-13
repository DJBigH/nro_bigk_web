@extends('admin.layout')

@section('title', 'Quản Lý Hướng Dẫn - Admin NRO BigK')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: var(--primary);">QUẢN LÝ BÀI VIẾT HƯỚNG DẪN</h2>
    <button class="btn" onclick="openAddModal()">THÊM BÀI VIẾT</button>
</div>

<!-- Posts List -->
<div class="card" style="padding: 0;">
    @if($posts->isEmpty())
        <p style="padding: 20px; text-align: center; color: var(--text-muted);">Chưa đăng tải bài viết hướng dẫn nào.</p>
    @else
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th>ID Post</th>
                    <th>Tiêu Đề</th>
                    <th>Ngày Đăng</th>
                    <th>Ghim Lên Đầu</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td style="font-family: 'Orbitron', sans-serif;">{{ $post->id_post }}</td>
                        <td style="font-weight: 700; color: #fff;">{{ $post->title }}</td>
                        <td style="font-size: 0.95rem;">
                            {{ $post->time_post ? $post->time_post->format('d/m/Y H:i') : '' }}
                        </td>
                        <td>
                            @if($post->up_top == 1)
                                <span class="badge badge-primary">CÓ GHIM</span>
                            @else
                                <span class="badge badge-secondary">K.GHIM</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="openEditModal({{ $post }})">
                                    Sửa
                                </button>
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="deletePost({{ $post->id_post }})">
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="padding: 20px;">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<!-- Modal Add/Edit Post -->
<div id="post-modal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3 id="modal-title" style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            TẠO BÀI VIẾT MỚI
        </h3>
        
        <form id="post-form">
            <input type="hidden" id="form-post-id" name="id">

            <!-- Title -->
            <div class="form-group">
                <label class="form-label">Tiêu đề bài viết</label>
                <input type="text" id="form-title" name="title" class="form-input" placeholder="Nhập tiêu đề..." required max="150">
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">Nội dung bài viết (Chấp nhận HTML/Text)</label>
                <textarea id="form-desc" name="description" class="form-input" rows="10" placeholder="Nhập nội dung hướng dẫn chi tiết..." required></textarea>
            </div>

            <!-- Up top flag -->
            <div class="form-group">
                <label class="form-label">Ghim lên đầu trang</label>
                <select id="form-uptop" name="up_top" class="form-input" required>
                    <option value="0">Không ghim</option>
                    <option value="1">Có ghim lên đầu</option>
                </select>
            </div>

            <button type="submit" id="modal-submit-btn" class="btn" style="width: 100%;">
                XÁC NHẬN ĐĂNG BÀI
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let mode = 'add';

    // Modal control helper
    function openAddModal() {
        mode = 'add';
        document.getElementById('modal-title').innerText = 'TẠO BÀI VIẾT HƯỚNG DẪN MỚI';
        document.getElementById('form-post-id').value = '';
        document.getElementById('post-form').reset();
        document.getElementById('post-modal').classList.add('show');
    }

    function openEditModal(post) {
        mode = 'edit';
        document.getElementById('modal-title').innerText = 'CẬP NHẬT BÀI VIẾT';
        document.getElementById('form-post-id').value = post.id_post;
        document.getElementById('form-title').value = post.title;
        document.getElementById('form-desc').value = post.description;
        document.getElementById('form-uptop').value = post.up_top;
        document.getElementById('post-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('post-modal').classList.remove('show');
    }

    // Submit post AJAX Form
    document.getElementById('post-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
        btn.innerText = 'ĐANG XỬ LÝ...';

        const postId = document.getElementById('form-post-id').value;
        const formData = new FormData(document.getElementById('post-form'));
        const data = Object.fromEntries(formData.entries());

        let url = '{{ route("admin.posts.store") }}';
        let method = 'POST';

        if (mode === 'edit') {
            url = `{{ url('admin/posts') }}/${postId}`;
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

    // Delete post
    async function deletePost(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa bài viết hướng dẫn này?')) {
            return;
        }

        try {
            const response = await window.csrfFetch(`{{ url('admin/posts') }}/${id}`, {
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
