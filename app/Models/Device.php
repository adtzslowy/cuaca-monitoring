<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id', 'device_name', 'location', 'latitude', 'longitude', 'status', 'last_synced_at'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'last_synced_at' => 'datetime',
    ];

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }
}
