<div wire:poll.15s x-data="{ panelOpen: false }" class="flex flex-col gap-4 h-[calc(100vh-4rem-3rem)]">

    {{-- Layout split --}}
    <div class="flex gap-4 flex-1 min-h-0">

        {{-- ── PETA (full saat panel disembunyikan) ── --}}
        <div class="relative flex-[3] rounded-xl border border-border overflow-hidden">

            {{-- Badge Windy --}}
            @if($windyKey)
                <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 rounded-lg bg-card/90 backdrop-blur border border-border px-2.5 py-1.5 text-xs font-medium text-foreground shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-primary"></span>
                    Windy + Leaflet
                </div>
            @else
                <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 rounded-lg bg-card/90 backdrop-blur border border-border px-2.5 py-1.5 text-xs font-medium text-muted-foreground shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                    OpenStreetMap (Windy: no key)
                </div>
            @endif

            {{-- Toggle panel stasiun --}}
            <button
                @click="panelOpen = !panelOpen"
                type="button"
                class="absolute top-3 right-3 z-[1000] flex items-center gap-1.5 rounded-lg bg-card/90 backdrop-blur border border-border px-2.5 py-1.5 text-xs font-medium text-foreground shadow-sm transition hover:bg-input"
                :title="panelOpen ? 'Sembunyikan panel stasiun' : 'Tampilkan panel stasiun'"
            >
                <x-heroicon-o-bars-3 class="h-4 w-4" x-show="!panelOpen" x-cloak />
                <x-heroicon-o-x-mark class="h-4 w-4" x-show="panelOpen" x-cloak />
                <span x-text="panelOpen ? 'Sembunyikan Panel' : 'Tampilkan Panel'"></span>
            </button>

            <div
                wire:ignore
                x-data="monitoringMap(@js($devices), @js($activeDevice), @js($windyKey))"
                x-init="init()"
                @device-selected.window="selectMarker($event.detail.id)"
                class="w-full h-full"
            >
                {{-- Windy requires id="windy", Leaflet-only uses id="leaflet-map" --}}
                <div id="windy" class="w-full h-full" style="display:none"></div>
                <div id="leaflet-map" class="w-full h-full"></div>
            </div>
        </div>

        {{-- ── PANEL KANAN (40%, toggle) ── --}}
        <div
            x-show="panelOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="flex-[2] flex flex-col gap-3 min-h-0 overflow-y-auto"
        >

            {{-- List stasiun --}}
            <div class="rounded-xl border border-border bg-card overflow-hidden shrink-0">
                <div class="border-b border-border px-4 py-3">
                    <h3 class="text-sm font-semibold text-foreground">Daftar Stasiun</h3>
                </div>
                <div class="divide-y divide-border max-h-44 overflow-y-auto">
                    @foreach ($devices as $device)
                        <button
                            wire:click="selectDevice({{ $device['id'] }})"
                            @click="window.dispatchEvent(new CustomEvent('device-selected', { detail: { id: {{ $device['id'] }} } }))"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-input
                                {{ $selectedDevice === $device['id'] ? 'bg-primary/10' : '' }}"
                        >
                            <span class="h-2 w-2 rounded-full shrink-0
                                {{ $device['status'] === 'online' ? 'bg-emerald-500' : 'bg-zinc-400' }}">
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-foreground truncate
                                    {{ $selectedDevice === $device['id'] ? 'text-primary' : '' }}">
                                    {{ $device['device_name'] }}
                                </p>
                                @if($device['location'])
                                    <p class="text-xs text-muted-foreground truncate">{{ $device['location'] }}</p>
                                @endif
                            </div>
                            <span class="text-xs shrink-0 {{ $device['status'] === 'online' ? 'text-emerald-500' : 'text-zinc-400' }}">
                                {{ $device['status'] === 'online' ? 'Online' : 'Offline' }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Detail stasiun aktif --}}
            @if ($activeDevice)
                <div class="rounded-xl border border-border bg-card flex-1 flex flex-col">
                    {{-- Header stasiun --}}
                    <div class="border-b border-border px-4 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-foreground truncate">{{ $activeDevice['device_name'] }}</h3>
                            @if($activeDevice['location'])
                                <p class="text-xs text-muted-foreground">{{ $activeDevice['location'] }}</p>
                            @endif
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0
                            {{ $activeDevice['status'] === 'online'
                                ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                                : 'bg-zinc-500/10 text-zinc-400 border border-zinc-500/20' }}">
                            <span class="h-1.5 w-1.5 rounded-full
                                {{ $activeDevice['status'] === 'online' ? 'bg-emerald-500' : 'bg-zinc-400' }}">
                            </span>
                            {{ $activeDevice['status'] === 'online' ? 'Online' : 'Offline' }}
                        </span>
                    </div>

                    {{-- Grid sensor --}}
                    <div class="grid grid-cols-2 gap-3 p-4">
                        <div class="rounded-lg bg-input p-3">
                            <p class="text-xs text-muted-foreground mb-1">Suhu</p>
                            <p class="text-xl font-semibold text-foreground">
                                {{ $activeDevice['suhu'] !== null ? $activeDevice['suhu'].'°C' : '—' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-input p-3">
                            <p class="text-xs text-muted-foreground mb-1">Kelembapan</p>
                            <p class="text-xl font-semibold text-foreground">
                                {{ $activeDevice['kelembapan'] !== null ? $activeDevice['kelembapan'].'%' : '—' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-input p-3">
                            <p class="text-xs text-muted-foreground mb-1">Tekanan Udara</p>
                            <p class="text-xl font-semibold text-foreground">
                                {{ $activeDevice['tekanan_udara'] !== null ? $activeDevice['tekanan_udara'].' hPa' : '—' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-input p-3">
                            <p class="text-xs text-muted-foreground mb-1">Curah Hujan</p>
                            <p class="text-xl font-semibold text-foreground">
                                {{ $activeDevice['curah_hujan'] !== null ? $activeDevice['curah_hujan'].' mm' : '—' }}
                            </p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-input p-3 flex items-center justify-between">
                            <p class="text-xs text-muted-foreground">Status Hujan</p>
                            <span class="{{ $activeDevice['hujan'] ? 'text-sky-500 font-medium' : 'text-muted-foreground' }} text-sm">
                                {{ $activeDevice['hujan'] ? '🌧 Sedang Hujan' : 'Tidak Hujan' }}
                            </span>
                        </div>
                    </div>

                    {{-- Koordinat & last sync --}}
                    <div class="mt-auto border-t border-border px-4 py-3 flex items-center justify-between text-xs text-muted-foreground">
                        <span>
                            @if($activeDevice['latitude'] && $activeDevice['longitude'])
                                {{ $activeDevice['latitude'] }}, {{ $activeDevice['longitude'] }}
                            @else
                                Koordinat belum diset
                            @endif
                        </span>
                        <span>{{ $activeDevice['last_synced'] ?? '—' }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('head')
@if($windyKey)
{{-- Windy requires Leaflet 1.4.x loaded first, then libBoot.js --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js"></script>
<script src="https://api.windy.com/assets/map-forecast/libBoot.js"></script>
@else
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif
@endpush

@push('scripts')

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('monitoringMap', (devices, activeDevice, windyKey) => ({
        map: null,
        markers: {},

        init() {
            const center = activeDevice?.latitude && activeDevice?.longitude
                ? [parseFloat(activeDevice.latitude), parseFloat(activeDevice.longitude)]
                : [-1.8272, 110.1395]; // default: Ketapang

            if (windyKey) {
                this.initWindy(center, windyKey);
            } else {
                this.initLeaflet(center);
            }
        },

        initLeaflet(center) {
            document.getElementById('leaflet-map').style.display = 'block';
            document.getElementById('windy').style.display = 'none';

            this.map = L.map('leaflet-map', { zoomControl: true }).setView(center, 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);

            this.addMarkers();
        },

        initWindy(center, key) {
            document.getElementById('windy').style.display = 'block';
            document.getElementById('leaflet-map').style.display = 'none';

            const tryInit = () => {
                if (typeof windyInit !== 'undefined') {
                    windyInit({
                        key,
                        verbose: false,
                        lat: center[0],
                        lon: center[1],
                        zoom: 11,
                    }, windyAPI => {
                        this.map = windyAPI.map;
                        this.addMarkers();
                    });
                } else {
                    setTimeout(tryInit, 100);
                }
            };
            tryInit();
        },

        addMarkers() {
            devices.forEach(device => {
                if (!device.latitude || !device.longitude) return;

                const isOnline = device.status === 'online';
                const color   = isOnline ? '#10b981' : '#71717a';

                const icon = L.divIcon({
                    className: '',
                    html: `<div style="
                        width:14px;height:14px;
                        background:${color};
                        border:2px solid white;
                        border-radius:50%;
                        box-shadow:0 1px 4px rgba(0,0,0,.4);
                    "></div>`,
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                });

                const popup = L.popup({ maxWidth: 220, className: 'monitoring-popup' }).setContent(`
                    <div style="font-family:inherit;padding:4px 2px">
                        <p style="font-weight:600;font-size:13px;margin:0 0 4px">${device.device_name}</p>
                        <p style="font-size:11px;color:#888;margin:0 0 8px">${device.location || ''}</p>
                        <table style="font-size:12px;width:100%;border-collapse:collapse">
                            <tr><td style="color:#888;padding:2px 0">Suhu</td><td style="text-align:right;font-weight:500">${device.suhu !== null ? device.suhu+'°C' : '—'}</td></tr>
                            <tr><td style="color:#888;padding:2px 0">Kelembapan</td><td style="text-align:right;font-weight:500">${device.kelembapan !== null ? device.kelembapan+'%' : '—'}</td></tr>
                            <tr><td style="color:#888;padding:2px 0">Curah Hujan</td><td style="text-align:right;font-weight:500">${device.curah_hujan !== null ? device.curah_hujan+' mm' : '—'}</td></tr>
                            <tr><td style="color:#888;padding:2px 0">Tekanan</td><td style="text-align:right;font-weight:500">${device.tekanan_udara !== null ? device.tekanan_udara+' hPa' : '—'}</td></tr>
                        </table>
                        <p style="font-size:10px;color:#aaa;margin:8px 0 0;text-align:right">${device.last_synced || ''}</p>
                    </div>
                `);

                const marker = L.marker([device.latitude, device.longitude], { icon })
                    .addTo(this.map)
                    .bindPopup(popup)
                    .on('click', () => {
                        @this.selectDevice(device.id);
                    });

                this.markers[device.id] = marker;
            });

            // Buka popup device aktif saat load
            if (activeDevice?.id && this.markers[activeDevice.id]) {
                this.markers[activeDevice.id].openPopup();
            }
        },

        selectMarker(id) {
            if (this.markers[id]) {
                this.map.setView(this.markers[id].getLatLng(), 13, { animate: true });
                this.markers[id].openPopup();
            }
        },
    }));
});
</script>
@endpush
