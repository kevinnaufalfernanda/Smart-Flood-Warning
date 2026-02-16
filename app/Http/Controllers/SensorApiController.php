<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\Setting;
use Illuminate\Http\Request;

class SensorApiController extends Controller
{
    /**
     * Receive data from NodeMCU ESP8266
     * POST /api/sensor
     * Params: distance (float), device_id (int, optional)
     */
    public function store(Request $request)
    {
        $request->validate([
            'distance' => 'required|numeric|min:0|max:400',
            'device_id' => 'nullable|integer|exists:devices,id',
        ]);

        $distance = floatval($request->input('distance'));
        $deviceId = $request->input('device_id', 1);

        // Get sensor height from settings
        $sensorHeight = floatval(Setting::getValue('sensor_height', 20));

        // Calculate water level
        $calc = SensorReading::calculateWaterLevel($distance, $sensorHeight);

        // Save reading
        $reading = SensorReading::create([
            'device_id' => $deviceId,
            'distance_cm' => $distance,
            'water_level_cm' => $calc['water_level_cm'],
            'water_level_percent' => $calc['water_level_percent'],
            'status' => $calc['status'],
        ]);

        // Update device last seen
        Device::where('id', $deviceId)->update([
            'is_online' => true,
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // Create alert if status is danger or warning
        if ($calc['status'] === 'bahaya') {
            Alert::create([
                'type' => 'danger',
                'title' => '🚨 BAHAYA: Ketinggian Air Kritis!',
                'message' => "Ketinggian air mencapai {$calc['water_level_cm']} cm ({$calc['water_level_percent']}%). Jarak sensor: {$distance} cm. Segera ambil tindakan!",
                'water_level' => $calc['water_level_cm'],
            ]);
        } elseif ($calc['status'] === 'siaga') {
            // Only create siaga alert if last alert was not siaga (avoid spam)
            $lastAlert = Alert::latest()->first();
            if (!$lastAlert || $lastAlert->type !== 'warning') {
                Alert::create([
                    'type' => 'warning',
                    'title' => '⚠️ SIAGA: Air Mulai Naik',
                    'message' => "Ketinggian air mencapai {$calc['water_level_cm']} cm ({$calc['water_level_percent']}%). Pantau terus perkembangan.",
                    'water_level' => $calc['water_level_cm'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $reading,
            'status' => $calc['status'],
            'message' => 'Data sensor berhasil disimpan',
        ], 201);
    }

    /**
     * Get latest sensor reading
     * GET /api/sensor/latest
     */
    public function latest()
    {
        $reading = SensorReading::with('device')->latest()->first();

        return response()->json([
            'success' => true,
            'data' => $reading,
        ]);
    }

    /**
     * Get historical readings
     * GET /api/sensor/history?hours=24&limit=100
     */
    public function history(Request $request)
    {
        $hours = $request->input('hours', 24);
        $limit = min($request->input('limit', 200), 500);

        $readings = SensorReading::where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $readings->count(),
            'data' => $readings,
        ]);
    }
}
