@if ($paginator->hasPages())
    <nav class="cyber-pagination-container" role="navigation" aria-label="Pagination Navigation" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; margin: 20px 0; width: 100%;">
        <!-- Info text -->
        <div style="font-size: 0.85rem; color: #aaa; font-family: 'Orbitron', sans-serif;">
            Hiển thị <span style="color: #00e676; font-weight: 700;">{{ $paginator->firstItem() }}</span> đến <span style="color: #00e676; font-weight: 700;">{{ $paginator->lastItem() }}</span> trên tổng <span style="color: #ff9800; font-weight: 700;">{{ number_format($paginator->total()) }}</span> kết quả
        </div>

        <!-- Buttons row -->
        <ul class="cyber-pagination" style="display: flex; list-style: none; padding: 0; margin: 0; gap: 6px; flex-wrap: wrap; justify-content: center; align-items: center;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li style="display: inline-block;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 7px 14px; font-size: 0.85rem; border-radius: 6px; background: rgba(255, 255, 255, 0.03); color: #555; border: 1px solid rgba(255, 255, 255, 0.06); cursor: not-allowed;">
                        &laquo; Trước
                    </span>
                </li>
            @else
                <li style="display: inline-block;">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; justify-content: center; padding: 7px 14px; font-size: 0.85rem; border-radius: 6px; background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='#fff'">
                        &laquo; Trước
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li style="display: inline-block;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 7px 12px; font-size: 0.85rem; color: #777;">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li style="display: inline-block;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 36px; padding: 0 10px; font-size: 0.9rem; font-weight: 700; border-radius: 6px; background: var(--primary); color: #000; box-shadow: 0 0 12px var(--primary-glow); border: 1px solid var(--primary); font-family: 'Orbitron', sans-serif;">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li style="display: inline-block;">
                                <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 36px; padding: 0 10px; font-size: 0.85rem; border-radius: 6px; background: rgba(255, 255, 255, 0.05); color: #ccc; border: 1px solid rgba(255, 255, 255, 0.1); text-decoration: none; font-family: 'Orbitron', sans-serif; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'; this.style.background='rgba(255,107,0,0.1)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='#ccc'; this.style.background='rgba(255,255,255,0.05)'">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li style="display: inline-block;">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; justify-content: center; padding: 7px 14px; font-size: 0.85rem; border-radius: 6px; background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='#fff'">
                        Tiếp &raquo;
                    </a>
                </li>
            @else
                <li style="display: inline-block;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 7px 14px; font-size: 0.85rem; border-radius: 6px; background: rgba(255, 255, 255, 0.03); color: #555; border: 1px solid rgba(255, 255, 255, 0.06); cursor: not-allowed;">
                        Tiếp &raquo;
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
