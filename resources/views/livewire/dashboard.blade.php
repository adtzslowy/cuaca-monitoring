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

    {{-- Grafik tren per-metrik — gaya widget cuaca --}}
    <div class="rounded-xl border border-border bg-card p-5">

        {{-- Header hero: kondisi terkini --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                {{-- Ikon kondisi --}}
                <div class="text-primary">
                    @if ($summary['raining'])
                        <x-heroicon-o-cloud class="h-12 w-12 text-sky-500" />
                    @else
                        <x-heroicon-o-sun class="h-12 w-12 text-amber-500" />
                    @endif
                </div>

                {{-- Nilai besar + satuan --}}
                <div class="flex items-start">
                    <span class="text-5xl font-semibold leading-none text-foreground">
                        {{ $summary['value'] ?? '—' }}
                    </span>
                    <span class="ml-1 mt-1 text-sm font-medium text-muted-foreground">{{ $summary['unit'] }}</span>
                </div>

                {{-- Mini stats --}}
                <div class="hidden space-y-0.5 border-l border-border pl-4 text-xs text-muted-foreground sm:block">
                    <p>Curah hujan: <span class="text-foreground">{{ $summary['curah'] ?? '—' }} mm</span></p>
                    <p>Kelembapan: <span class="text-foreground">{{ $summary['kelembapan'] ?? '—' }}%</span></p>
                    <p>Suhu: <span class="text-foreground">{{ $summary['suhu'] ?? '—' }}°C</span></p>
                </div>
            </div>

            {{-- Nama stasiun + hari + kondisi --}}
            <div class="text-left sm:text-right">
                <h2 class="text-base font-semibold text-foreground">{{ $activeDevice?->device_name ?? $activeDevice?->device_id ?? '-' }}</h2>
                <p class="text-sm text-muted-foreground">{{ $summary['day'] }}</p>
                <p class="text-sm text-muted-foreground">{{ $summary['condition'] }}</p>
            </div>
        </div>

        {{-- Tab metrik (ala Suhu | Presipitasi | Angin) --}}
        <div class="mt-4 flex items-center gap-6 border-b border-border">
            @foreach ($metrics as $key => $meta)
                <button
                    wire:click="$set('selectedMetric', '{{ $key }}')"
                    class="relative -mb-px pb-2.5 text-sm font-medium transition
                        {{ $selectedMetric === $key
                            ? 'text-foreground'
                            : 'text-muted-foreground hover:text-foreground'
                        }}"
                >
                    {{ $meta['label'] }}
                    @if ($selectedMetric === $key)
                        <span class="absolute inset-x-0 -bottom-px h-0.5 rounded-full" style="background:{{ $meta['color'] }}"></span>
                    @endif
                </button>
            @endforeach

            <span class="ml-auto flex items-center gap-1.5 pb-2.5 text-xs text-muted-foreground">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                Live · 12 data terakhir
            </span>
        </div>

        {{-- Chart --}}
        <div class="mt-2" wire:ignore x-data="weatherChart(@js($chart))" x-init="init()">
            <div x-ref="chart"></div>
        </div>

        {{-- Ringkasan 7 hari terakhir (agregat historis suhu) --}}
        <div class="mt-2 grid grid-cols-4 gap-2 border-t border-border pt-4 sm:grid-cols-7">
            @foreach ($daily as $d)
                <div class="flex flex-col items-center gap-1.5 rounded-lg py-2 transition
                    {{ $d['isToday'] ? 'bg-input' : 'hover:bg-input/50' }}">
                    <span class="text-xs font-medium {{ $d['isToday'] ? 'text-foreground' : 'text-muted-foreground' }}">
                        {{ $d['day'] }}
                    </span>
                    @if ($d['rain'])
                        <x-heroicon-o-cloud class="h-6 w-6 text-sky-500" />
                    @else
                        <x-heroicon-o-sun class="h-6 w-6 text-amber-500" />
                    @endif
                    @if ($d['avg'] !== null)
                        <div class="flex items-baseline text-xs">
                            <span class="font-semibold text-foreground">{{ $d['avg'] }}°</span>
                        </div>
                    @else
                        <span class="text-xs text-muted-foreground">—</span>
                    @endif
                </div>
            @endforeach
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
