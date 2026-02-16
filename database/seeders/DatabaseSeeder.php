<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@iotbanjir.local'],
            [
                'name' => 'Admin IoT',
                'password' => Hash::make('password'),
            ]
        );

        // Create default settings
        $settings = [
            ['key' => 'sensor_height', 'value' => '20', 'description' => 'Tinggi sensor dari dasar wadah (cm)'],
            ['key' => 'danger_threshold', 'value' => '5', 'description' => 'Jarak minimal bahaya (cm) - air sangat tinggi'],
            ['key' => 'warning_threshold', 'value' => '7', 'description' => 'Jarak siaga (cm) - air mulai tinggi'],
            ['key' => 'safe_threshold', 'value' => '10', 'description' => 'Jarak aman (cm) - air rendah'],
            ['key' => 'reading_interval', 'value' => '3', 'description' => 'Interval pembacaan sensor (detik)'],
            ['key' => 'buzzer_enabled', 'value' => '1', 'description' => 'Aktifkan buzzer saat bahaya'],
        ];
        foreach ($settings as $s) {
            Setting::create($s);
        }

        // Create devices
        $device1 = Device::create([
            'name' => 'Sensor Utama - Node1',
            'location' => 'Sungai Ciliwung KM 5',
            'ip_address' => '192.168.1.100',
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        $device2 = Device::create([
            'name' => 'Sensor Cadangan - Node2',
            'location' => 'Bendungan Katulampa',
            'ip_address' => '192.168.1.101',
            'is_online' => false,
            'last_seen_at' => now()->subHours(6),
        ]);

        // Generate realistic sensor readings for the past 24 hours
        $sensorHeight = 20;
        $startTime = Carbon::now()->subHours(24);

        // Simulate a flood scenario: normal -> rising -> peak -> receding
        $phases = [
            // Phase 1: Normal (0-6 hours) - distance 15-18cm (low water)
            ['hours' => 6, 'distMin' => 14, 'distMax' => 18],
            // Phase 2: Rising (6-10 hours) - distance 8-14cm (water rising)
            ['hours' => 4, 'distMin' => 8, 'distMax' => 14],
            // Phase 3: Warning peak (10-13 hours) - distance 5-8cm (siaga)
            ['hours' => 3, 'distMin' => 5, 'distMax' => 8],
            // Phase 4: Danger peak (13-15 hours) - distance 2-5cm (bahaya!)
            ['hours' => 2, 'distMin' => 2, 'distMax' => 5],
            // Phase 5: Receding (15-20 hours) - distance 6-12cm 
            ['hours' => 5, 'distMin' => 6, 'distMax' => 12],
            // Phase 6: Back to normal (20-24 hours) - distance 13-17cm
            ['hours' => 4, 'distMin' => 13, 'distMax' => 17],
        ];

        $currentTime = $startTime->copy();
        $readingInterval = 5; // minutes between readings

        foreach ($phases as $phase) {
            $phaseMinutes = $phase['hours'] * 60;
            $readings = intval($phaseMinutes / $readingInterval);

            for ($i = 0; $i < $readings; $i++) {
                $progress = $i / max($readings - 1, 1);

                // Add some noise to the distance
                $baseDistance = $phase['distMin'] + ($phase['distMax'] - $phase['distMin']) * (1 - $progress);
                $noise = (mt_rand(-100, 100) / 100) * 0.5;
                $distance = max(1, round($baseDistance + $noise, 2));

                $waterLevel = max(0, $sensorHeight - $distance);
                $percent = min(100, max(0, ($waterLevel / $sensorHeight) * 100));

                if ($distance <= 5) {
                    $status = 'bahaya';
                } elseif ($distance <= 7) {
                    $status = 'siaga';
                } else {
                    $status = 'aman';
                }

                SensorReading::create([
                    'device_id' => $device1->id,
                    'distance_cm' => $distance,
                    'water_level_cm' => round($waterLevel, 2),
                    'water_level_percent' => round($percent, 2),
                    'status' => $status,
                    'created_at' => $currentTime->copy(),
                    'updated_at' => $currentTime->copy(),
                ]);

                $currentTime->addMinutes($readingInterval);
            }
        }

        // Create alerts
        $alerts = [
            [
                'type' => 'danger',
                'title' => '🚨 BAHAYA: Ketinggian Air Kritis!',
                'message' => 'Ketinggian air di Sungai Ciliwung KM 5 mencapai 17.5 cm (87.5%). Segera lakukan evakuasi!',
                'water_level' => 17.5,
                'is_read' => false,
                'created_at' => now()->subHours(10),
            ],
            [
                'type' => 'warning',
                'title' => '⚠️ SIAGA: Air Mulai Naik',
                'message' => 'Ketinggian air di Sungai Ciliwung KM 5 mencapai 13.2 cm (66%). Pantau terus perkembangan.',
                'water_level' => 13.2,
                'is_read' => false,
                'created_at' => now()->subHours(14),
            ],
            [
                'type' => 'warning',
                'title' => '⚠️ SIAGA: Kenaikan Terdeteksi',
                'message' => 'Sensor mendeteksi kenaikan air secara bertahap di Sungai Ciliwung KM 5. Level: 12.0 cm (60%).',
                'water_level' => 12.0,
                'is_read' => true,
                'created_at' => now()->subHours(15),
            ],
            [
                'type' => 'info',
                'title' => 'ℹ️ INFO: Kondisi Normal',
                'message' => 'Ketinggian air di Sungai Ciliwung KM 5 telah kembali normal. Level: 4.0 cm (20%).',
                'water_level' => 4.0,
                'is_read' => true,
                'created_at' => now()->subHours(4),
            ],
            [
                'type' => 'danger',
                'title' => '🚨 BAHAYA: Level Maksimum!',
                'message' => 'PERINGATAN MAKSIMUM! Air di Sungai Ciliwung KM 5 mencapai 18.0 cm (90%). Evakuasi warga segera!',
                'water_level' => 18.0,
                'is_read' => false,
                'created_at' => now()->subHours(9),
            ],
            [
                'type' => 'info',
                'title' => 'ℹ️ Perangkat Terhubung',
                'message' => 'Sensor Utama - Node1 (192.168.1.100) berhasil terhubung ke server.',
                'water_level' => null,
                'is_read' => true,
                'created_at' => now()->subHours(24),
            ],
        ];

        foreach ($alerts as $alert) {
            Alert::create($alert);
        }
    }
}
