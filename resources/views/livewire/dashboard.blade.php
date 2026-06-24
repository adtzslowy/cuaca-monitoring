<div
    wire:poll.10s
    class="space-y-6"
    x-data
    @device-changed.window="$wire.onDeviceChanged($event.detail.id)"
>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-border bg-card p-5 transition hover:shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-input text-muted-foreground">
                    <x-heroicon-o-cpu-chip class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-foreground">{{ $total }}</p>
                    <p class="text-xs text-muted-foreground">Total Stasiun</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-5 transition hover:shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <x-heroicon-o-signal class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $online }}</p>
                    <p class="text-xs text-muted-foreground">Online</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-5 transition hover:shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                    <x-heroicon-o-signal-slash class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-rose-600 dark:text-rose-400">{{ $offline }}</p>
                    <p class="text-xs text-muted-foreground">Offline</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-5 transition hover:shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
                    <x-heroicon-o-cloud class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-sky-600 dark:text-sky-400">{{ $hujan }}</p>
                    <p class="text-xs text-muted-foreground">Stasiun Hujan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik tren per-metrik --}}
    <div class="rounded-xl border border-border bg-card p-5">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-foreground">Tren {{ $metrics[$selectedMetric]['label'] }} — {{ $activeDevice?->device_name ?? $activeDevice?->device_id ?? '-' }}</h2>
                <p class="text-xs text-muted-foreground">12 data terakhir</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Live
                </span>

                {{-- Dropdown pilih metrik --}}
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex items-center gap-2 rounded-lg border border-border bg-input px-3 py-1.5 text-xs font-medium text-foreground transition hover:bg-border/60"
                    >
                        {{ $metrics[$selectedMetric]['label'] }}
                        <x-heroicon-m-chevron-down class="h-3.5 w-3.5 text-muted-foreground" />
                    </button>
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition
                        class="absolute right-0 z-10 mt-1 w-44 rounded-lg border border-border bg-card shadow-lg py-1"
                    >
                        @foreach ($metrics as $key => $meta)
                            <button
                                wire:click="$set('selectedMetric', '{{ $key }}')"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs transition hover:bg-input
                                    {{ $selectedMetric === $key ? 'font-semibold text-foreground' : 'text-muted-foreground' }}"
                            >
                                <span class="h-2 w-2 rounded-full shrink-0" style="background:{{ $meta['color'] }}"></span>
                                {{ $meta['label'] }}
                                @if ($selectedMetric === $key)
                                    <x-heroicon-m-check class="ml-auto h-3.5 w-3.5" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div wire:ignore x-data="weatherChart(@js($chart))" x-init="init()">
            <div x-ref="chart"></div>
        </div>
    </div>

    {{-- Tabel data terbaru dengan paginasi --}}
    <div class="rounded-xl border border-border bg-card">
        <div class="border-b border-border px-5 py-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-foreground">Data Terbaru per Stasiun</h2>
            <span class="text-xs text-muted-foreground">
                Menampilkan {{ ($page - 1) * $perPage + 1 }}–{{ min($page * $perPage, $total) }} dari {{ $total }} stasiun
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border text-left text-xs text-muted-foreground">
                        <th class="px-5 py-3 font-medium">Stasiun</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Suhu (°C)</th>
                        <th class="px-5 py-3 font-medium">Tekanan (hPa)</th>
                        <th class="px-5 py-3 font-medium">Kelembapan (%)</th>
                        <th class="px-5 py-3 font-medium">Curah Hujan (mm)</th>
                        <th class="px-5 py-3 font-medium">Hujan</th>
                        <th class="px-5 py-3 font-medium">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($pagedDevices as $device)
                        @php $s = $latestSensors[$device->id] ?? collect(); @endphp
                        <tr class="text-foreground transition hover:bg-input/60">
                            <td class="px-5 py-3 font-medium">{{ $device->device_name ?? $device->device_id }}</td>
                            <td class="px-5 py-3">
                                @if ($device->status === 'online')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-input px-2 py-0.5 text-xs font-medium text-muted-foreground">
                                        <span class="h-1.5 w-1.5 rounded-full bg-muted"></span> Offline
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $s['suhu']?->value ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $s['tekanan_udara']?->value ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $s['kelembapan']?->value ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $s['curah_hujan']?->value ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if (isset($s['hujan']) && (int) $s['hujan']->value === 1)
                                    <span class="text-sky-600 dark:text-sky-400">Ya</span>
                                @else
                                    <span class="text-muted-foreground">Tidak</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-muted-foreground">
                                {{ $device->last_synced_at ? \Carbon\Carbon::parse($device->last_synced_at)->format('d/m H:i') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginasi bergaya shadcn --}}
        @if ($totalPages > 1)
            <div class="border-t border-border px-5 py-3 flex items-center justify-between gap-4">
                <p class="text-xs text-muted-foreground">
                    Halaman {{ $page }} dari {{ $totalPages }}
                </p>
                <div class="flex items-center gap-1">
                    {{-- Prev --}}
                    <button
                        wire:click="previousPage"
                        @disabled($page <= 1)
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border text-xs text-foreground transition hover:bg-input disabled:pointer-events-none disabled:opacity-40"
                    >
                        <x-heroicon-m-chevron-left class="h-4 w-4" />
                    </button>

                    {{-- Nomor halaman --}}
                    @for ($i = 1; $i <= $totalPages; $i++)
                        @if ($i === 1 || $i === $totalPages || abs($i - $page) <= 1)
                            <button
                                wire:click="$set('page', {{ $i }})"
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2.5 text-xs transition
                                    {{ $i === $page
                                        ? 'border-primary bg-primary text-primary-foreground font-semibold'
                                        : 'border-border text-foreground hover:bg-input'
                                    }}"
                            >
                                {{ $i }}
                            </button>
                        @elseif ($i === 2 && $page > 3)
                            <span class="inline-flex h-8 w-8 items-center justify-center text-xs text-muted-foreground">…</span>
                        @elseif ($i === $totalPages - 1 && $page < $totalPages - 2)
                            <span class="inline-flex h-8 w-8 items-center justify-center text-xs text-muted-foreground">…</span>
                        @endif
                    @endfor

                    {{-- Next --}}
                    <button
                        wire:click="nextPage({{ $totalPages }})"
                        @disabled($page >= $totalPages)
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border text-xs text-foreground transition hover:bg-input disabled:pointer-events-none disabled:opacity-40"
                    >
                        <x-heroicon-m-chevron-right class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
