<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = ['device_id', 'distance_cm', 'water_level_cm', 'water_level_percent', 'status'];

    protected $casts = [
        'distance_cm' => 'float',
        'water_level_cm' => 'float',
        'water_level_percent' => 'float',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public static function calculateWaterLevel(float $distance, float $sensorHeight = 20): array
    {
        $waterLevel = max(0, $sensorHeight - $distance);
        $percent = min(100, max(0, ($waterLevel / $sensorHeight) * 100));

        // Determine status based on distance
        $settings = Setting::pluck('value', 'key');
        $dangerThreshold = floatval($settings['danger_threshold'] ?? 5);
        $warningThreshold = floatval($settings['warning_threshold'] ?? 7);

        if ($distance <= $dangerThreshold) {
            $status = 'bahaya';
        } elseif ($distance <= $warningThreshold) {
            $status = 'siaga';
        } else {
            $status = 'aman';
        }

        return [
            'water_level_cm' => round($waterLevel, 2),
            'water_level_percent' => round($percent, 2),
            'status' => $status,
        ];
    }
}
