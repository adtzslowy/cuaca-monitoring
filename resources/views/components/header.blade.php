@php
    $allDevices = \App\Models\Device::orderBy('device_name', 'asc')->orderBy('device_id', 'asc')->get(['id', 'device_id', 'device_name', 'status']);
@endphp

<header class="sticky top-0 z-[198] flex h-16 items-center border-b border-border bg-card px-3 md:px-6 gap-2 md:gap-4">

    {{-- Kiri: hamburger + judul --}}
    <div class="flex items-center gap-2 shrink-0">
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="rounded-lg p-2 text-muted-foreground hover:bg-input md:hidden transition">
            <x-heroicon-o-bars-3 class="h-6 w-6" />
        </button>
        <h1 class="text-sm md:text-base font-semibold text-foreground truncate max-w-[120px] sm:max-w-none">{{ $title ?? 'Dashboard' }}</h1>
        <div class="h-4 w-px bg-border/50 hidden sm:block"></div>
    </div>

    {{-- Tengah: Searchable Device Dropdown --}}
    <div
        x-data="{
            open: false,
            search: '',
            devices: {{ $allDevices->toJson() }},
            selectedId: null,
            selectedLabel: 'Pilih Alat',

            init() {
                const saved = localStorage.getItem('selected-device-id');
                if (saved) {
                    const found = this.devices.find(d => String(d.id) === saved);
                    if (found) {
                        this.selectedId = found.id;
                        this.selectedLabel = found.device_name || found.device_id;
                    }
                } else if (this.devices.length) {
                    this.select(this.devices[0]);
                }
            },

            select(device) {
                this.selectedId = device.id;
                this.selectedLabel = device.device_name || device.device_id;
                localStorage.setItem('selected-device-id', device.id);
                this.open = false;
                this.search = '';
                window.dispatchEvent(new CustomEvent('device-changed', { detail: { id: device.id } }));
            },

            get filtered() {
                if (!this.search) return this.devices;
                const q = this.search.toLowerCase();
                return this.devices.filter(d =>
                    (d.device_name || '').toLowerCase().includes(q) ||
                    (d.device_id || '').toLowerCase().includes(q)
                );
            }
        }"
        class="relative min-w-0 flex-1 sm:flex-none sm:w-52"
        @click.outside="open = false; search = ''"
        x-init="init()"
    >
        {{-- Trigger --}}
        <button
            @click="open = !open"
            class="flex w-full items-center justify-between gap-2 rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground transition hover:bg-border/60"
        >
            <div class="flex items-center gap-2 min-w-0">
                <x-heroicon-o-cpu-chip class="h-4 w-4 shrink-0 text-muted-foreground" />
                <span class="truncate text-xs sm:text-sm" x-text="selectedLabel"></span>
            </div>
            <x-heroicon-m-chevron-up-down class="h-4 w-4 shrink-0 text-muted-foreground" />
        </button>

        {{-- Dropdown --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute left-0 top-full mt-1 w-64 rounded-lg border border-border bg-card shadow-lg z-50"
        >
            <div class="p-2 border-b border-border">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                    <input
                        x-model="search"
                        x-ref="searchInput"
                        x-init="$watch('open', v => v && $nextTick(() => $refs.searchInput.focus()))"
                        type="text"
                        placeholder="Cari alat..."
                        class="w-full rounded-md border border-border bg-input pl-8 pr-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>
            </div>

            <div class="max-h-52 overflow-y-auto py-1">
                <template x-for="device in filtered" :key="device.id">
                    <button
                        @click="select(device)"
                        class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm transition hover:bg-input"
                        :class="device.id === selectedId ? 'text-primary font-medium' : 'text-foreground'"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full shrink-0"
                            :class="device.status === 'online' ? 'bg-emerald-500' : 'bg-zinc-400'"
                        ></span>
                        <span class="truncate" x-text="device.device_name || device.device_id"></span>
                        <x-heroicon-m-check
                            class="ml-auto h-3.5 w-3.5 shrink-0 text-primary"
                            x-show="device.id === selectedId"
                        />
                    </button>
                </template>

                <div x-show="filtered.length === 0" class="px-3 py-4 text-center text-xs text-muted-foreground">
                    Alat tidak ditemukan.
                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: tema + user --}}
    <div class="flex items-center gap-1 md:gap-3 shrink-0 ml-auto">
        <button
            x-data="{
                dark: document.documentElement.classList.contains('dark'),
                toggle() {
                    this.dark = !this.dark;
                    document.documentElement.classList.toggle('dark', this.dark);
                    localStorage.setItem('theme-dashboard', this.dark ? 'dark' : 'light');
                }
            }"
            @click="toggle()"
            :title="dark ? 'Mode terang' : 'Mode gelap'"
            class="rounded-lg p-2 text-muted-foreground hover:bg-input transition">
            <x-heroicon-o-sun x-show="dark" class="h-5 w-5" />
            <x-heroicon-o-moon x-show="!dark" class="h-5 w-5" />
        </button>

        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="flex items-center gap-1.5 rounded-lg px-1.5 py-1.5 text-muted-foreground hover:bg-input transition">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-card border border-border text-xs font-bold text-foreground shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="hidden md:block text-sm font-medium text-foreground">
                    {{ auth()->user()->name ?? 'User' }}
                </span>
                <x-heroicon-m-chevron-down class="hidden sm:block h-4 w-4" />
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                class="absolute right-0 mt-2 w-48 rounded-lg border border-border bg-card shadow-lg py-1 z-50">
                <a href="#" class="flex items-center gap-2 px-4 py-2.5 text-sm text-foreground hover:bg-input transition">
                    <x-heroicon-o-user class="h-4 w-4" /> Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-rose-500 hover:bg-input transition">
                        <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
