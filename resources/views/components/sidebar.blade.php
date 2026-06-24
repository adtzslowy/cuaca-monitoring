<aside
    x-show="sidebarOpen || window.innerWidth >= 768"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-[200] w-64 bg-card border-r border-border md:translate-x-0 flex flex-col"
    @click.outside="if (window.innerWidth < 768) sidebarOpen = false"
>
    {{-- Logo --}}
    <div class="flex h-16 items-center gap-3 border-b border-border px-6">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary border border-border">
            <x-heroicon-o-signal class="h-5 w-5 text-primary-foreground" />
        </div>
        <div class="leading-tight">
            <p class="text-sm font-semibold text-foreground font-ui">Cuaca Monitor</p>
            <p class="text-xs text-muted-foreground">Ketapang</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex flex-col gap-4 px-3 py-4 flex-1 overflow-y-auto">

        @php
            $navGroups = [
                [
                    'label' => null,
                    'items' => [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'heroicon-o-home'],
                        ['route' => 'monitoring', 'label' => 'Monitoring', 'icon' => 'heroicon-o-signal'],
                    ],
                ],
                [
                    'label' => 'Master Data',
                    'items' => [
                        ['route' => 'devices.index', 'label' => 'Data Stasiun', 'icon' => 'heroicon-o-cpu-chip'],
                        ['route' => 'readings.index', 'label' => 'Data Cuaca', 'icon' => 'heroicon-o-chart-bar'],
                    ],
                ],
                [
                    'label' => 'Manage System',
                    'items' => [
                        ['route' => 'users.index', 'label' => 'Manajemen User', 'icon' => 'heroicon-o-users'],
                    ],
                ],
            ];
        @endphp

        @foreach ($navGroups as $group)
            <div class="flex flex-col gap-1">
                @if ($group['label'])
                    <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-widest text-muted-foreground/60">
                        {{ $group['label'] }}
                    </p>
                @endif

                @foreach ($group['items'] as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a
                        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                        @click="if (window.innerWidth < 768) sidebarOpen = false"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                            {{ $active
                                ? 'bg-primary/10 text-primary border border-primary/20 font-semibold'
                                : 'text-muted-foreground hover:bg-input hover:text-foreground'
                            }}"
                    >
                        @svg($item['icon'], 'h-5 w-5 shrink-0')
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        @endforeach

    </nav>

    {{-- Status Sync --}}
    <div class="border-t border-border p-4 text-xs text-muted-foreground">
        <p class="font-medium text-foreground mb-1">Status Sync</p>
        <p>
            @php $latestSync = \App\Models\Device::max('last_synced_at'); @endphp
            {{ $latestSync ? \Carbon\Carbon::parse($latestSync)->diffForHumans() : 'Belum ada data' }}
        </p>
    </div>
</aside>
