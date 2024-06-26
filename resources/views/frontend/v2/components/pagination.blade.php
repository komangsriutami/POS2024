@if ($paginator->hasPages())
    <nav>
        <ul class="pagination justify-content-center">
            {{-- First page --}}
            <li class="page-item {{ $paginator->currentPage() == 1 ? 'disabled' : '' }}" aria-disabled="true">
                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>1]) }}">First</a>
            </li>

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$paginator->currentPage()-1]) }}" rel="prev"><i class="fas fa-chevron-left text-primary"></i></a>
                </li>
            @endif

            @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                @php
                    $half_total_links = floor(7 / 2);
                    $from = $paginator->currentPage() - $half_total_links;
                    $to = $paginator->currentPage() + $half_total_links;
                    if ($paginator->currentPage() < $half_total_links) {
                        $to += $half_total_links - $paginator->currentPage();
                    }
                    if ($paginator->lastPage() - $paginator->currentPage() < $half_total_links) {
                        $from -= $half_total_links - ($paginator->lastPage() - $paginator->currentPage()) - 1;
                    }
                @endphp
                @if ($from < $i && $i < $to)
                    <li class="page-item {{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$i]) }}">{{ $i }}</a>
                    </li>
                @endif
            @endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$paginator->currentPage()+1]) }}" rel="next"><i class="fas fa-chevron-right text-primary"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                </li>
            @endif

            {{-- Last page --}}
            <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() ? 'disabled' : '' }}" aria-disabled="true">
                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page'=>$paginator->lastPage()]) }}">Last</a>
            </li>
        </ul>
    </nav>
@endif
<style>
    .page-item.disabled .page-link{
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
        padding: 12px 20px;
        font-weight: 600;
        color: #2E3842;
        margin-right: 10px;
        border-radius: inherit !important;
    }
</style>