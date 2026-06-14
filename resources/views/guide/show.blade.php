@extends('layouts.app')

@section('title', $post->title . ' - Hướng Dẫn NRO BigK')
@section('description', strip_tags(Str::limit($post->description, 150)))

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    
    <!-- Back to List button -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('guide') }}" style="color: var(--primary); text-decoration: none; font-family: 'Orbitron', sans-serif; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
            🡸 QUAY LẠI DANH SÁCH
        </a>
    </div>

    <!-- Article Card -->
    <div class="card" style="padding: 40px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
            @if($post->up_top == 1)
                <span class="badge badge-primary">GHIM</span>
            @endif
            <span class="badge badge-secondary" style="text-transform: uppercase;">{{ $post->category ?? 'HƯỚNG DẪN' }}</span>
            <span style="font-size: 0.95rem; color: var(--text-muted);">
                Đăng lúc: {{ $post->time_post ? $post->time_post->format('d/m/Y H:i') : '' }}
            </span>
        </div>

        <h1 style="color: #fff; font-size: 2.2rem; line-height: 1.3; margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
            {{ $post->title }}
        </h1>

        <!-- Article Description (Formatted text/HTML) -->
        <div class="article-content" style="color: var(--text-main); font-size: 1.2rem; line-height: 1.8;">
            {!! nl2br($post->description) !!}
        </div>
    </div>

</div>
@endsection

@section('styles')
<style>
    .article-content p {
        margin-bottom: 15px;
    }
    .article-content strong {
        color: #fff;
    }
    .article-content ul, .article-content ol {
        padding-left: 20px;
        margin-bottom: 15px;
    }
    .article-content li {
        margin-bottom: 5px;
    }
</style>
@endsection
