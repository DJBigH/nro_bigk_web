<div class="cyber-pagination-container" role="navigation" aria-label="Pagination Navigation" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin: 20px 0; padding: 12px 18px; background: rgba(10, 12, 18, 0.7); border: 1px solid rgba(255, 107, 0, 0.2); border-radius: 10px; width: 100%; box-sizing: border-box;">
    <!-- Left: Info & Per-Page Selector -->
    <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
        <!-- Result count summary -->
        <div style="font-size: 0.85rem; color: #aaa; font-family: 'Orbitron', sans-serif;">
            @if ($paginator->total() > 0)
                Hiển thị <span style="color: #00e676; font-weight: 700;">{{ $paginator->firstItem() }}</span> - <span style="color: #00e676; font-weight: 700;">{{ $paginator->lastItem() }}</span> trên <span style="color: #ff9800; font-weight: 700;">{{ number_format($paginator->total()) }}</span> dòng
            @else
                <span style="color: #777;">0 kết quả</span>
            @endif
        </div>

        <!-- Per-page selector (5, 10, 15, 20) -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 0.82rem; color: #888; font-family: 'Orbitron', sans-serif;">Hiển thị:</span>
            <select class="cyber-per-page-select" 
                    onchange="cyberChangePerPage(this.value, '{{ $paginator->getPageName() }}')"
                    style="background: #141721; color: #00e5ff; border: 1px solid rgba(0, 229, 255, 0.4); border-radius: 6px; padding: 4px 8px; font-size: 0.85rem; font-weight: 700; font-family: 'Orbitron', sans-serif; cursor: pointer; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#ff9800'" onblur="this.style.borderColor='rgba(0, 229, 255, 0.4)'">
                @foreach([5, 10, 15, 20] as $size)
                    <option value="{{ $size }}" {{ $paginator->perPage() == $size ? 'selected' : '' }}>
                        {{ $size }} / trang
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Right: Page Navigation Buttons -->
    <ul class="cyber-pagination" style="display: flex; list-style: none; padding: 0; margin: 0; gap: 6px; flex-wrap: wrap; justify-content: center; align-items: center;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li style="display: inline-block;">
                <span style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; font-size: 0.82rem; border-radius: 6px; background: rgba(255, 255, 255, 0.03); color: #555; border: 1px solid rgba(255, 255, 255, 0.06); cursor: not-allowed; user-select: none;">
                    &laquo; Trước
                </span>
            </li>
        @else
            <li style="display: inline-block;">
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; font-size: 0.82rem; border-radius: 6px; background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='#fff'">
                    &laquo; Trước
                </a>
            </li>
        @endif

        {{-- Single page fallback --}}
        @if ($paginator->lastPage() <= 1)
            <li style="display: inline-block;">
                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 32px; padding: 0 10px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; background: var(--primary); color: #000; box-shadow: 0 0 10px var(--primary-glow); border: 1px solid var(--primary); font-family: 'Orbitron', sans-serif;">
                    1
                </span>
            </li>
        @else
            {{-- Multiple pages --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li style="display: inline-block;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 10px; font-size: 0.82rem; color: #777;">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li style="display: inline-block;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 32px; padding: 0 10px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; background: var(--primary); color: #000; box-shadow: 0 0 10px var(--primary-glow); border: 1px solid var(--primary); font-family: 'Orbitron', sans-serif;">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li style="display: inline-block;">
                                <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 32px; padding: 0 10px; font-size: 0.82rem; border-radius: 6px; background: rgba(255, 255, 255, 0.05); color: #ccc; border: 1px solid rgba(255, 255, 255, 0.1); text-decoration: none; font-family: 'Orbitron', sans-serif; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'; this.style.background='rgba(255,107,0,0.1)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='#ccc'; this.style.background='rgba(255,255,255,0.05)'">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li style="display: inline-block;">
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; font-size: 0.82rem; border-radius: 6px; background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='#fff'">
                    Tiếp &raquo;
                </a>
            </li>
        @else
            <li style="display: inline-block;">
                <span style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; font-size: 0.82rem; border-radius: 6px; background: rgba(255, 255, 255, 0.03); color: #555; border: 1px solid rgba(255, 255, 255, 0.06); cursor: not-allowed; user-select: none;">
                    Tiếp &raquo;
                </span>
            </li>
        @endif
    </ul>
</div>

<script>
if (typeof window.cyberChangePerPage === 'undefined') {
    window.cyberChangePerPage = function(val, pageName) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set(pageName || 'page', '1');
        window.location.href = url.toString();
    };
}
</script>
