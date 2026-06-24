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
            <h2 class="text-base font-semibold text-foreground">Manajemen User</h2>
            <p class="text-xs text-muted-foreground mt-0.5">Kelola akun dan hak akses pengguna sistem</p>
        </div>
        <button
            wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
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
                placeholder="Cari nama atau email..."
                class="w-full rounded-lg border border-border bg-input pl-9 pr-4 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary"
            >
        </div>
        <select
            wire:model.live="filterRole"
            class="rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="">Semua Role</option>
            <option value="superadmin">Superadmin</option>
            <option value="operator">Operator</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/40">
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Dibuat</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($users as $user)
                    <tr class="bg-card hover:bg-muted/30 transition">
                        <td class="px-4 py-3 text-muted-foreground text-xs">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-foreground">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @foreach ($user->roles as $role)
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                    {{ $role->name === 'admin'
                                        ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20'
                                        : 'bg-violet-500/10 text-violet-400 border border-violet-500/20'
                                    }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    wire:click="openEdit({{ $user->id }})"
                                    class="rounded-md p-1.5 text-muted-foreground hover:bg-input hover:text-foreground transition"
                                    title="Edit"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $user->id }})"
                                    class="rounded-md p-1.5 text-muted-foreground hover:bg-red-500/10 hover:text-red-400 transition"
                                    title="Hapus"
                                    @if ($user->id === auth()->id()) disabled @endif
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
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">
                            Tidak ada user ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="text-sm text-muted-foreground">
        {{ $users->links() }}
    </div>

    {{-- ── MODAL CREATE / EDIT ── --}}
    <x-ui.dialog wire:model="showModal">
        <x-slot name="title">
            {{ $isEdit ? 'Edit User' : 'Tambah User Baru' }}
        </x-slot>

        <div class="space-y-4">
            {{-- Nama --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Nama Lengkap</label>
                <input
                    wire:model="name"
                    type="text"
                    placeholder="Masukkan nama lengkap"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('name') border-red-500/50 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Email</label>
                <input
                    wire:model="email"
                    type="email"
                    placeholder="email@example.com"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('email') border-red-500/50 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Role</label>
                <select
                    wire:model="selectedRole"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('selectedRole') border-red-500/50 @enderror"
                >
                    <option value="">Pilih role...</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                @error('selectedRole')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Password
                    @if ($isEdit)
                        <span class="text-muted-foreground/60 font-normal">(kosongkan jika tidak diubah)</span>
                    @endif
                </label>
                <input
                    wire:model="password"
                    type="password"
                    placeholder="{{ $isEdit ? 'Password baru (opsional)' : 'Min. 8 karakter' }}"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('password') border-red-500/50 @enderror"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Konfirmasi Password</label>
                <input
                    wire:model="passwordConfirm"
                    type="password"
                    placeholder="Ulangi password"
                    class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary
                        @error('passwordConfirm') border-red-500/50 @enderror"
                >
                @error('passwordConfirm')
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
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah User' }}
                </span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </x-slot>
    </x-ui.dialog>

    {{-- ── MODAL KONFIRMASI HAPUS ── --}}
    <x-ui.dialog wire:model="showDelete" maxWidth="sm">
        <x-slot name="title">Hapus User</x-slot>

        <p class="text-sm text-muted-foreground">
            Yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.
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
