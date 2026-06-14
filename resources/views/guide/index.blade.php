@extends('layouts.app')

@section('title', 'Hướng Dẫn Trò Chơi - NRO BigK')

@section('content')
<div class="card" style="margin-bottom: 40px; text-align: center;">
    <h1 style="color: var(--primary); margin-bottom: 10px;">HƯỚNG DẪN & CẨM NANG</h1>
    <p style="color: var(--text-muted);">Tổng hợp các bài viết hướng dẫn chơi game, sự kiện hoạt động diễn ra tại Ngọc Rồng BigK.</p>
</div>

<div style="display: flex; flex-direction: column; gap: 20px;">
    @if($posts->isEmpty())
        <div class="card" style="text-align: center; padding: 40px 0;">
            <p style="color: var(--text-muted);">Hiện chưa có bài viết hướng dẫn nào được đăng tải.</p>
        </div>
    @else
        @foreach($posts as $post)
            <div class="card" style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        @if($post->up_top == 1)
                            <span class="badge badge-primary">GHIM</span>
                        @endif
                        <span class="badge badge-secondary" style="text-transform: uppercase;">{{ $post->category ?? 'HƯỚNG DẪN' }}</span>
                        <span style="font-size: 0.9rem; color: var(--text-muted);">
                            {{ $post->time_post ? $post->time_post->format('d/m/Y H:i') : '' }}
                        </span>
                    </div>
                    
                    <h2 style="font-size: 1.4rem; color: #fff; margin-bottom: 10px;">
                        <a href="{{ route('guide.show', $post->id_post) }}" style="color: #fff; text-decoration: none;">
                            {{ $post->title }}
                        </a>
                    </h2>
                    
                    <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.5; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                        {{ strip_tags($post->description) }}
                    </p>
                </div>

                <div>
                    <a href="{{ route('guide.show', $post->id_post) }}" class="btn" style="padding: 10px 20px; font-size: 0.95rem;">CHI TIẾT</a>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
