@props([
    'product',
    'badge' => null,
    'priority' => false,
])

@php
    $rating = (float) $product->average_rating;
    $reviewCount = (int) $product->review_count;
    $isDiscounted = (bool) $product->sale_price;

    $media = match (true) {
        (bool) $product->thumbnail && $product->thumbnail_is_video => 'thumb-video',
        (bool) $product->thumbnail => 'thumb-image',
        (bool) $product->preview_image && $product->preview_is_video => 'preview-video',
        (bool) $product->preview_image => 'preview-image',
        default => 'placeholder',
    };

    $mediaUrl = match ($media) {
        'thumb-video', 'thumb-image' => $product->thumbnail_url,
        'preview-video', 'preview-image' => $product->preview_image_url,
        default => null,
    };

    $url = route('products.show', $product);
    $authorName = $product->author?->name;
@endphp

<article
    {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-gray-300 hover:shadow-xl hover:shadow-gray-900/5']) }}
>
    {{-- ── Media ─────────────────────────────────────────────────────────────── --}}
    <a href="{{ $url }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden bg-gray-100"
       aria-label="View {{ $product->title }}">
        @switch($media)
            @case('thumb-video')
            @case('preview-video')
                <video
                    src="{{ $mediaUrl }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                    muted loop playsinline preload="metadata"
                    @if($priority) fetchpriority="high" @endif
                    x-data
                    @mouseenter="$el.play()"
                    @mouseleave="$el.pause(); $el.currentTime = 0"
                ></video>
                @break

            @case('thumb-image')
            @case('preview-image')
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $product->title }}{{ $product->category ? ' — ' . $product->category->name . ' template' : '' }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                    @if($priority) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                    decoding="async"
                >
                @break

            @default
                <div class="absolute inset-0 flex items-center justify-center opacity-25 grayscale">
                    <img src="/templatr-logo.svg" class="h-auto w-20" alt="" loading="lazy" decoding="async">
                </div>
        @endswitch

        {{-- Solid scrim keeps badges legible over any artwork --}}
        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/55 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></span>

        <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
            @if($isDiscounted)
                <span class="rounded-lg bg-red-600 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-white shadow-sm">Sale</span>
            @endif
            @if($badge === 'new')
                <span class="rounded-lg bg-[#FFC300] px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-black shadow-sm">New</span>
            @endif
        </div>

        <span class="absolute right-3 top-3 rounded-lg bg-black/55 px-2 py-0.5 text-[11px] font-medium text-white backdrop-blur-sm">
            {{ ucfirst($product->file_type ?? 'file') }}
        </span>

        {{-- Revealed on hover: the detail affordance Envato-style listings use --}}
        <span class="pointer-events-none absolute inset-x-3 bottom-3 flex translate-y-2 items-center justify-center rounded-xl bg-white/95 py-2 text-xs font-semibold text-gray-900 opacity-0 shadow-sm transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
            View details
        </span>
    </a>

    {{-- ── Body ──────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-1 flex-col p-4">
        <div class="mb-2 flex items-center justify-between gap-2">
            @if($product->category)
                <a href="{{ route('category.show', $product->category) }}" wire:navigate
                   class="truncate text-xs font-medium text-gray-600 transition-colors hover:text-[var(--color-primary-ink)]">
                    {{ $product->category->name }}
                </a>
            @else
                <span class="text-xs font-medium text-gray-600">Uncategorised</span>
            @endif

            @if($reviewCount > 0)
                <span class="flex flex-shrink-0 items-center gap-1 text-xs font-medium text-gray-700">
                    <svg class="h-3.5 w-3.5 text-[#F5A623]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>{{ number_format($rating, 1) }}</span>
                    <span class="sr-only">out of 5 from {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                </span>
            @else
                <span class="flex-shrink-0 text-xs font-medium text-gray-500">No reviews yet</span>
            @endif
        </div>

        <h3 class="mb-3 text-sm font-semibold leading-snug text-gray-900">
            <a href="{{ $url }}" wire:navigate class="line-clamp-2 transition-colors hover:text-[var(--color-primary-ink)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FFC300] focus-visible:ring-offset-2">
                {{ $product->title }}
            </a>
        </h3>

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-gray-100 pt-3">
            @if($authorName)
                <span class="truncate text-xs text-gray-600">by {{ $authorName }}</span>
            @else
                <span></span>
            @endif

            <span class="flex flex-shrink-0 items-baseline gap-1.5">
                @if($isDiscounted)
                    <s class="text-xs text-gray-500">{{ $product->formatted_original_price }}</s>
                @endif
                <span class="text-base font-bold text-gray-900">{{ $product->formatted_price }}</span>
            </span>
        </div>
    </div>
</article>
