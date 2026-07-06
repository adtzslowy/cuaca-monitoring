<div class="space-y-5">

    {{-- Flash messages --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-400"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-foreground">Data Stasiun</h2>
            <p class="text-xs text-muted-foreground mt-0.5">Kelola stasiun pemantauan cuaca dan lokasinya</p>
        </div>
        <button
            wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Stasiun
        </button>
    </div>

    {{-- Filter & Search --}}
    <div class="flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari nama, ID, atau lokasi stasiun..."
                class="w-full rounded-lg border border-border bg-input pl-9 pr-4 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary"
            >
        </div>
        <select
            wire:model.live="filterStatus"
            class="rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="">Semua Status</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border border-border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/40">
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Stasiun</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Lokasi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Koordinat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Sensor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Sinkron Terakhir</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($devices as $device)
                    <tr class="bg-card hover:bg-muted/30 transition">
                        <td class="px-4 py-3 text-muted-foreground text-xs">
                            {{ $devices->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <x-heroicon-o-cpu-chip class="h-4 w-4" />
                                </div>
                                <div class="leading-tight">
                                    <span class="font-medium text-foreground">{{ $device->device_name }}</span>
                                    <p class="text-xs text-muted-foreground font-mono">{{ $device->device_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $device->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-muted-foreground font-mono">
                            @if ($device->latitude !== null && $device->longitude !== null)
                                {{ $device->latitude }}, {{ $device->longitude }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted-foreground text-xs">{{ $device->sensors_count }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium
                                {{ $device->status === 'online'
                                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                    : 'bg-zinc-500/10 text-zinc-400 border border-zinc-500/20'
                                }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $device->status === 'online' ? 'bg-emerald-400' : 'bg-zinc-400' }}"></span>
                                {{ ucfirst($device->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {{ $device->last_synced_at?->diffForHumans() ?? 'Belum ada' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    wire:click="openEdit({{ $device->id }})"
                                    class="rounded-md p-1.5 text-muted-foreground hover:bg-input hover:text-foreground transition"
                                    title="Edit"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $device->id }})"
                                    class="rounded-md p-1.5 text-muted-foreground hover:bg-red-500/10 hover:text-red-400 transition"
                                    title="Hapus"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                            Tidak ada stasiun ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="text-sm text-muted-foreground">
        {{ $devices->links() }}
    </div>

    {{-- ── MODAL CREATE / EDIT ── --}}
    <x-ui.dialog wire:model="showModal" maxWidth="lg">
        <x-slot name="title">
            {{ $isEdit ? 'Edit Stasiun' : 'Tambah Stasiun Baru' }}
        </x-slot>

        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Device ID --}}
                <div>
                    <label class="block text-xs font-medium text-muted-foreground mb-1.5">ID Stasiun</label>
                    <input
                        wire:model="device_id"
                        type="text"
                        placeholder="mis. STA-001"
                        class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                            @error('device_id') border-red-500/50 @enderror"
                    >
                    @error('device_id')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Device Name --}}
                <div>
                    <label class="block text-xs font-medium text-muted-foreground mb-1.5">Nama Stasiun</label>
                    <input
                        wire:model="device_name"
                        type="text"
                        placeholder="mis. Stasiun Ketapang"
                        class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                            @error('device_name') border-red-500/50 @enderror"
                    >
                    @error('device_name')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Location --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Lokasi</label>
                <input
                    wire:model="location"
                    type="text"
                    placeholder="mis. Kec. Delta Pawan, Ketapang"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('location') border-red-500/50 @enderror"
                >
                @error('location')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Latitude --}}
                <div>
                    <label class="block text-xs font-medium text-muted-foreground mb-1.5">Latitude</label>
                    <input
                        wire:model="latitude"
                        type="text"
                        placeholder="mis. -1.850000"
                        class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                            @error('latitude') border-red-500/50 @enderror"
                    >
                    @error('latitude')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Longitude --}}
                <div>
                    <label class="block text-xs font-medium text-muted-foreground mb-1.5">Longitude</label>
                    <input
                        wire:model="longitude"
                        type="text"
                        placeholder="mis. 109.980000"
                        class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                            @error('longitude') border-red-500/50 @enderror"
                    >
                    @error('longitude')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Status</label>
                <select
                    wire:model="status"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('status') border-red-500/50 @enderror"
                >
                    <option value="offline">Offline</option>
                    <option value="online">Online</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button
                wire:click="$set('showModal', false)"
                class="rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground hover:bg-input transition"
            >
                Batal
            </button>
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="save">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Stasiun' }}
                </span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </x-slot>
    </x-ui.dialog>

    {{-- ── MODAL KONFIRMASI HAPUS ── --}}
    <x-ui.dialog wire:model="showDelete" maxWidth="sm">
        <x-slot name="title">Hapus Stasiun</x-slot>

        <p class="text-sm text-muted-foreground">
            Yakin ingin menghapus stasiun ini? Seluruh data sensor yang terkait juga akan terhapus. Tindakan ini tidak dapat dibatalkan.
        </p>

        <x-slot name="footer">
            <button
                wire:click="$set('showDelete', false)"
                class="rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground hover:bg-input transition"
            >
                Batal
            </button>
            <button
                wire:click="destroy"
                wire:loading.attr="disabled"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="destroy">Ya, Hapus</span>
                <span wire:loading wire:target="destroy">Menghapus...</span>
            </button>
        </x-slot>
    </x-ui.dialog>

</div>
