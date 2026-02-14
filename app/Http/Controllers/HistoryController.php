<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SensorReading::with('device');

        // Date Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', $request->start_date);
        }
        
        // Device Filter
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }
        
        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sort = $request->input('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $readings = $query->paginate(20);

        // Stats
        $totalReadings = SensorReading::count();
        $avgWaterLevel = SensorReading::avg('water_level_cm');
        $maxWaterLevel = SensorReading::max('water_level_cm');

        $devices = Device::all();

        return view('history.index', compact('readings', 'totalReadings', 'avgWaterLevel', 'maxWaterLevel', 'devices'));
    }
}
