<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Data Stasiun')]
class Devices extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDelete = false;
    public bool $isEdit = false;

    public ?int $deviceId = null;
    public string $device_id = '';
    public string $device_name = '';
    public string $location = '';
    public ?string $latitude = null;
    public ?string $longitude = null;
    public string $status = 'offline';

    public string $search = '';
    public string $filterStatus = '';

    protected function rules(): array
    {
        return [
            'device_id'   => ['required', 'string', 'max:255', $this->isEdit ? "unique:devices,device_id,{$this->deviceId}" : 'unique:devices,device_id'],
            'device_name' => ['required', 'string', 'max:255'],
            'location'    => ['nullable', 'string', 'max:255'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'status'      => ['required', 'in:online,offline'],
        ];
    }

    protected array $messages = [
        'device_id.required'   => 'ID stasiun wajib diisi.',
        'device_id.unique'     => 'ID stasiun sudah digunakan.',
        'device_name.required' => 'Nama stasiun wajib diisi.',
        'latitude.numeric'     => 'Latitude harus berupa angka.',
        'latitude.between'     => 'Latitude harus antara -90 dan 90.',
        'longitude.numeric'    => 'Longitude harus berupa angka.',
        'longitude.between'    => 'Longitude harus antara -180 dan 180.',
        'status.required'      => 'Status wajib dipilih.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['deviceId', 'device_id', 'device_name', 'location', 'latitude', 'longitude']);
        $this->status = 'offline';
        $this->resetErrorBag();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $device = Device::findOrFail($id);

        $this->deviceId    = $device->id;
        $this->device_id   = $device->device_id;
        $this->device_name = $device->device_name;
        $this->location    = $device->location ?? '';
        $this->latitude    = $device->latitude;
        $this->longitude   = $device->longitude;
        $this->status      = $device->status;

        $this->resetErrorBag();
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'device_id'   => $this->device_id,
            'device_name' => $this->device_name,
            'location'    => $this->location ?: null,
            'latitude'    => $this->latitude !== '' ? $this->latitude : null,
            'longitude'   => $this->longitude !== '' ? $this->longitude : null,
            'status'      => $this->status,
        ];

        if ($this->isEdit) {
            Device::findOrFail($this->deviceId)->update($data);
            $message = 'Stasiun berhasil diperbaharui.';
        } else {
            Device::create($data);
            $message = 'Stasiun berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->reset(['deviceId', 'device_id', 'device_name', 'location', 'latitude', 'longitude']);

        session()->flash('success', $message);
    }

    public function confirmDelete(int $id): void
    {
        $this->deviceId = $id;
        $this->showDelete = true;
    }

    public function destroy(): void
    {
        Device::findOrFail($this->deviceId)->delete();

        $this->showDelete = false;
        $this->deviceId   = null;

        session()->flash('success', 'Stasiun berhasil dihapus.');
    }

    public function render()
    {
        $devices = Device::withCount('sensors')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q) =>
                    $q->where('device_name', 'like', "%{$this->search}%")
                      ->orWhere('device_id', 'like', "%{$this->search}%")
                      ->orWhere('location', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('device_name')
            ->paginate(10);

        return view('livewire.devices', compact('devices'));
    }
}
