<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $fillable = [
        'device_id', 'type', 'value', 'unit', 'recorded_at'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
