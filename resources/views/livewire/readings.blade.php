<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-foreground">Data Cuaca</h2>
            <p class="text-xs text-muted-foreground mt-0.5">Riwayat pembacaan sensor dari seluruh stasiun</p>
        </div>
    </div>

    {{-- Kartu ringkasan kondisi terkini --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                    <x-heroicon-o-sun class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-foreground">
                        {{ $summary['suhu'] !== null ? round($summary['suhu'], 1).'°C' : '—' }}
                    </p>
                    <p class="text-xs text-muted-foreground">Rata-rata Suhu</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
                    <x-heroicon-o-cloud class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-foreground">
                        {{ $summary['kelembapan'] !== null ? round($summary['kelembapan'], 1).'%' : '—' }}
                    </p>
                    <p class="text-xs text-muted-foreground">Rata-rata Kelembapan</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                    <x-heroicon-o-beaker class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-foreground">
                        {{ $summary['curah_hujan'] !== null ? round($summary['curah_hujan'], 1).' mm' : '—' }}
                    </p>
                    <p class="text-xs text-muted-foreground">Rata-rata Curah Hujan</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-500/15 dark:text-zinc-400">
                    <x-heroicon-o-cloud-arrow-down class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-foreground">{{ $summary['hujan_count'] }}</p>
                    <p class="text-xs text-muted-foreground">Stasiun Sedang Hujan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="flex flex-col gap-3 lg:flex-row">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari nama atau ID stasiun..."
                class="w-full rounded-lg border border-border bg-input pl-9 pr-4 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary"
            >
        </div>
        <select
            wire:model.live="filterDevice"
            class="rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="">Semua Stasiun</option>
            @foreach ($devices as $device)
                <option value="{{ $device->id }}">{{ $device->device_name }}</option>
            @endforeach
        </select>
        <select
            wire:model.live="filterType"
            class="rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="">Semua Jenis Sensor</option>
            @foreach ($types as $key => $meta)
                <option value="{{ $key }}">{{ $meta['label'] }}</option>
            @endforeach
        </select>
        <input
            wire:model.live="filterDate"
            type="date"
            class="rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @if ($search || $filterDevice || $filterType || $filterDate)
            <button
                wire:click="resetFilters"
                class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm text-muted-foreground transition hover:bg-input hover:text-foreground"
            >
                <x-heroicon-o-x-mark class="h-4 w-4" />
                Reset
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border border-border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/40">
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Stasiun</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Jenis Sensor</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground">Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @php
                    $badgeClasses = [
                        'amber'   => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                        'sky'     => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                        'indigo'  => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
                        'zinc'    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
                    ];
                @endphp
                @forelse ($readings as $reading)
                    @php
                        $meta = $types[$reading->type] ?? ['label' => $reading->type, 'unit' => $reading->unit, 'color' => 'zinc'];
                        $badge = $badgeClasses[$meta['color']] ?? $badgeClasses['zinc'];
                    @endphp
                    <tr class="bg-card hover:bg-muted/30 transition">
                        <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                            {{ $reading->recorded_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="leading-tight">
                                <span class="font-medium text-foreground">{{ $reading->device?->device_name ?? '—' }}</span>
                                <p class="text-xs text-muted-foreground font-mono">{{ $reading->device?->device_id }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ $meta['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-foreground">
                            @if ($reading->type === 'hujan')
                                {{ (int) $reading->value === 1 ? 'Ya' : 'Tidak' }}
                            @else
                                {{ $reading->value !== null ? rtrim(rtrim($reading->value, '0'), '.') : '—' }} {{ $meta['unit'] }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-sm text-muted-foreground">
                            Tidak ada data cuaca ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="text-sm text-muted-foreground">
        {{ $readings->links() }}
    </div>

</div>
