<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\Sensor;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Monitoring')]
class Monitoring extends Component
{
    public ?int $selectedDevice = null;

    public function selectDevice(int $id): void
    {
        $this->selectedDevice = $id;
    }

    public function render()
    {
        $latestTime = Sensor::max('recorded_at');

        $latestSensors = Sensor::where('recorded_at', $latestTime)
            ->get()
            ->groupBy('device_id')
            ->map(fn ($rows) => $rows->keyBy('type'));

        $devices = Device::orderBy('device_name')->get()->map(function ($device) use ($latestSensors) {
            $sensors = $latestSensors[$device->id] ?? collect();
            return [
                'id'           => $device->id,
                'device_id'    => $device->device_id,
                'device_name'  => $device->device_name,
                'location'     => $device->location,
                'latitude'     => $device->latitude,
                'longitude'    => $device->longitude,
                'status'       => $device->status,
                'last_synced'  => $device->last_synced_at?->diffForHumans(),
                'suhu'         => $sensors['suhu']?->value ?? null,
                'kelembapan'   => $sensors['kelembapan']?->value ?? null,
                'tekanan_udara'=> $sensors['tekanan_udara']?->value ?? null,
                'curah_hujan'  => $sensors['curah_hujan']?->value ?? null,
                'hujan'        => isset($sensors['hujan']) ? (int) $sensors['hujan']->value === 1 : false,
            ];
        });

        $activeDevice = $this->selectedDevice
            ? $devices->firstWhere('id', $this->selectedDevice)
            : $devices->first();

        if (!$this->selectedDevice && $devices->isNotEmpty()) {
            $this->selectedDevice = $devices->first()['id'];
        }

        return view('livewire.monitoring', [
            'devices'      => $devices,
            'activeDevice' => $activeDevice,
            'windyKey'     => config('services.windy.key'),
        ]);
    }
}
