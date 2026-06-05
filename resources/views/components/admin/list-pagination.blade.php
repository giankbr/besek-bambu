@props([
    'paginator',
    'perPageOptions' => [10, 25, 50, 100],
])

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $total = $paginator->total();
    $from = $paginator->firstItem() ?? 0;
    $to = $paginator->lastItem() ?? 0;
    $currentPage = $paginator->currentPage();
    $lastPage = max(1, $paginator->lastPage());
@endphp

@if ($total > 0)
    <footer {{ $attributes->merge(['class' => 'admin-list-pagination mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-700']) }}>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Showing :from–:to of :total results', [
                    'from' => $from,
                    'to' => $to,
                    'total' => $total,
                ]) }}
            </p>

            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                <div class="inline-flex items-stretch overflow-hidden rounded-lg border border-zinc-300 bg-white text-sm dark:border-zinc-600 dark:bg-zinc-900">
                    <span class="flex items-center border-r border-zinc-300 px-3 py-2 text-zinc-500 dark:border-zinc-600 dark:text-zinc-400">
                        {{ __('Per page') }}
                    </span>
                    <select
                        wire:model.live="perPage"
                        class="min-w-[3.5rem] cursor-pointer border-0 bg-transparent px-3 py-2 text-zinc-800 outline-none focus:ring-0 dark:text-zinc-100"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <nav
                    role="navigation"
                    aria-label="{{ __('Pagination Navigation') }}"
                    class="inline-flex items-stretch overflow-hidden rounded-lg border border-zinc-300 bg-white text-sm dark:border-zinc-600 dark:bg-zinc-900"
                >
                    @if ($paginator->onFirstPage())
                        <span
                            aria-disabled="true"
                            class="inline-flex size-9 cursor-not-allowed items-center justify-center text-zinc-300 dark:text-zinc-600"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @else
                        <a
                            href="{{ $paginator->previousPageUrl() }}"
                            rel="prev"
                            wire:navigate
                            class="inline-flex size-9 items-center justify-center text-zinc-600 transition hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                            aria-label="{{ __('pagination.previous') }}"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    <span class="flex min-w-[3.5rem] items-center justify-center border-x border-zinc-300 px-3 py-2 font-medium tabular-nums text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">
                        {{ $currentPage }} / {{ $lastPage }}
                    </span>

                    @if ($paginator->hasMorePages())
                        <a
                            href="{{ $paginator->nextPageUrl() }}"
                            rel="next"
                            wire:navigate
                            class="inline-flex size-9 items-center justify-center text-zinc-600 transition hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                            aria-label="{{ __('pagination.next') }}"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span
                            aria-disabled="true"
                            class="inline-flex size-9 cursor-not-allowed items-center justify-center text-zinc-300 dark:text-zinc-600"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </footer>
@endif
