<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncAlat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:alat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Singkronisasi alat dan data sensor dari API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = config('services.alat.url');

        $this->info("Mengambil data dari API: {$url}");

        try {
            $response = Http::timeout(10)->retry(3, 2000)->withoutVerifying()->get($url);
            if ($response->failed()) {
                $this->error("Request gagal. Status: {$response->status()}");
                Log::error('Fetch cuaca gagal', ['status' => $response->status()]);
                return self::FAILURE;
            }

            $data = $response->json();
            $stations = $data['stations'] ?? [];

            if (empty($stations)) {
                $this->warn('Tidak ada data station yang diterima dari API.');
                return self::SUCCESS;
            }

            $deviceCount = 0;
            $sensorCount = 0;
            $timestamp = $data['timestamp'] ?? now();

            foreach ($stations as $stationId => $station) {
                $status = $station['status'] ?? 'offline';

                // Upsert device DULU — termasuk yang offline, biar status terekam
                $device = Device::updateOrCreate(
                    ['device_id' => $stationId],
                    [
                        'device_name' => ucfirst(str_replace('cuaca', 'Cuaca ', $stationId)),
                        'location' => $stationId,
                        'status' => $status,
                        'last_synced_at' => now(),
                    ],
                );
                $deviceCount++;

                // Baru skip insert SENSOR kalau offline / gak ada data
                if ($status !== 'online' || empty($station['data'])) {
                    Log::info('Station offline, sensor dilewati', ['station_id' => $stationId]);
                    continue;
                }

                // Insert sensor data dari station
                $sensorData = $station['data'];
                $sensorTypes = [
                    'suhu' => ['type' => 'suhu', 'unit' => '°C'],
                    'tekanan_udara' => ['type' => 'tekanan_udara', 'unit' => 'hPa'],
                    'kelembapan' => ['type' => 'kelembapan', 'unit' => '%'],
                    'curah_hujan' => ['type' => 'curah_hujan', 'unit' => 'mm'],
                    'hujan' => ['type' => 'hujan', 'unit' => null],
                ];

                foreach ($sensorTypes as $key => $config) {
                    if (isset($sensorData[$key]) && $sensorData[$key] !== null) {
                        // khusus 'hujan': "YA" -> 1, "TIDAK" -> 0
                        $value = $key === 'hujan'
                            ? (strtoupper((string) $sensorData[$key]) === 'YA' ? 1 : 0)
                            : $sensorData[$key];

                        Sensor::updateOrCreate(
                            [
                                'device_id'   => $device->id,
                                'type'        => $config['type'],
                                'recorded_at' => $timestamp,
                            ],
                            [
                                'value' => $value,
                                'unit'  => $config['unit'],
                            ]
                        );
                        $sensorCount++;
                    }
                }
            }

            $this->info("✓ Selesai! {$deviceCount} perangkat & {$sensorCount} sensor berhasil disingkronisasi.");
            Log::info('Sync cuaca berhasil', ['devices' => $deviceCount, 'sensors' => $sensorCount]);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✗ Gagal mengambil data: {$e->getMessage()}");
            Log::error('Sync cuaca error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }
}
