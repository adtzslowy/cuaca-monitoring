<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\Sensor;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Data Cuaca')]
class Readings extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterDevice = '';
    public string $filterType = '';
    public string $filterDate = '';

    public array $types = [
        'suhu'          => ['label' => 'Suhu',          'unit' => '°C',  'color' => 'amber'],
        'kelembapan'    => ['label' => 'Kelembapan',    'unit' => '%',   'color' => 'sky'],
        'tekanan_udara' => ['label' => 'Tekanan Udara', 'unit' => 'hPa', 'color' => 'emerald'],
        'curah_hujan'   => ['label' => 'Curah Hujan',   'unit' => 'mm',  'color' => 'indigo'],
        'hujan'         => ['label' => 'Status Hujan',  'unit' => '',    'color' => 'zinc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDevice(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDate(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterDevice', 'filterType', 'filterDate']);
        $this->resetPage();
    }

    public function render()
    {
        $latestTime = Sensor::max('recorded_at');

        $latestSensors = Sensor::where('recorded_at', $latestTime)->get();

        $summary = [
            'suhu'          => $latestSensors->where('type', 'suhu')->avg('value'),
            'kelembapan'    => $latestSensors->where('type', 'kelembapan')->avg('value'),
            'curah_hujan'   => $latestSensors->where('type', 'curah_hujan')->avg('value'),
            'hujan_count'   => $latestSensors->where('type', 'hujan')->where('value', 1)->count(),
        ];

        $readings = Sensor::query()
            ->with('device')
            ->when($this->search, fn ($q) =>
                $q->whereHas('device', fn ($q) =>
                    $q->where('device_name', 'like', "%{$this->search}%")
                      ->orWhere('device_id', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterDevice, fn ($q) => $q->where('device_id', $this->filterDevice))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterDate, fn ($q) => $q->whereDate('recorded_at', $this->filterDate))
            ->orderByDesc('recorded_at')
            ->paginate(15);

        return view('livewire.readings', [
            'readings' => $readings,
            'devices'  => Device::orderBy('device_name')->get(),
            'summary'  => $summary,
        ]);
    }
}
