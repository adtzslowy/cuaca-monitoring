<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\Sensor;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public string $selectedMetric = 'suhu';
    public ?int $selectedDevice = null;
    public int $page = 1;
    public int $perPage = 5;

    public array $metrics = [
        'suhu'          => ['label' => 'Suhu',          'unit' => '°C',  'color' => '#f59e0b'],
        'kelembapan'    => ['label' => 'Kelembapan',    'unit' => '%',   'color' => '#0ea5e9'],
        'curah_hujan'   => ['label' => 'Curah Hujan',   'unit' => 'mm',  'color' => '#6366f1'],
        'tekanan_udara' => ['label' => 'Tekanan Udara', 'unit' => 'hPa', 'color' => '#10b981'],
    ];

    public function mount(): void
    {
        $this->selectedDevice = Device::orderBy('id')->value('id');
    }

    #[On('device-changed')]
    public function onDeviceChanged(int $id): void
    {
        $this->selectedDevice = $id;
        $this->page = 1;
    }

    public function updatedSelectedMetric(): void
    {
        $this->page = 1;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) $this->page--;
    }

    public function nextPage(int $totalPages): void
    {
        if ($this->page < $totalPages) $this->page++;
    }

    public function render()
    {
        $total   = Device::count();
        $online  = Device::where('status', 'online')->count();
        $offline = $total - $online;

        $latestTime = Sensor::max('recorded_at');

        $hujan = Sensor::where('type', 'hujan')
            ->where('recorded_at', $latestTime)
            ->where('value', 1)
            ->count();

        $devices      = Device::orderBy('id')->get();
        $totalPages   = (int) ceil($devices->count() / $this->perPage);
        $pagedDevices = $devices->forPage($this->page, $this->perPage);

        $latestSensors = Sensor::where('recorded_at', $latestTime)
            ->get()
            ->groupBy('device_id')
            ->map(fn ($rows) => $rows->keyBy('type'));

        // Chart per device
        $activeDevice = $this->selectedDevice
            ? Device::find($this->selectedDevice)
            : $devices->first();

        $trend = Sensor::query()
            ->where('device_id', $activeDevice?->id)
            ->where('type', $this->selectedMetric)
            ->select('recorded_at', 'value')
            ->orderByDesc('recorded_at')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $meta = $this->metrics[$this->selectedMetric];

        $chart = [
            'categories'   => $trend->map(fn ($r) => \Carbon\Carbon::parse($r->recorded_at)->format('H:i'))->all(),
            'data'         => $trend->map(fn ($r) => round((float) $r->value, 2))->all(),
            'label'        => $meta['label'],
            'unit'         => $meta['unit'],
            'color'        => $meta['color'],
            'deviceName'   => $activeDevice?->device_name ?? $activeDevice?->device_id ?? '-',
        ];

        $this->dispatch('chart-updated', chart: $chart);

        return view('livewire.dashboard', [
            'total'         => $total,
            'online'        => $online,
            'offline'       => $offline,
            'hujan'         => $hujan,
            'pagedDevices'  => $pagedDevices,
            'totalPages'    => $totalPages,
            'latestSensors' => $latestSensors,
            'chart'         => $chart,
            'activeDevice'  => $activeDevice,
        ]);
    }
}
