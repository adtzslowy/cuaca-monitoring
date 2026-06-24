@props([
    'maxWidth' => 'md', // sm | md | lg | xl
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        default => 'max-w-md',
    };
@endphp

{{-- Backdrop --}}
<div
    x-data
    x-show="$wire.{{ $attributes->wire('model')->value() }}"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    x-cloak
>
    {{-- Panel --}}
    <div
        x-show="$wire.{{ $attributes->wire('model')->value() }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        @click.outside="$wire.set('{{ $attributes->wire('model')->value() }}', false)"
        class="w-full {{ $maxWidthClass }} rounded-xl border border-border bg-card shadow-2xl"
    >
        {{-- Header --}}
        @if (isset($title))
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <h3 class="text-sm font-semibold text-foreground">{{ $title }}</h3>
                <button
                    @click="$wire.set('{{ $attributes->wire('model')->value() }}', false)"
                    class="rounded-md p-1 text-muted-foreground hover:bg-input hover:text-foreground transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Body --}}
        <div class="px-5 py-4">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if (isset($footer))
            <div class="flex items-center justify-end gap-2 border-t border-border px-5 py-4">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
