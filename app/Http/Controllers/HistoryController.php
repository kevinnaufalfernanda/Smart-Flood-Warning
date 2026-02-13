<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SensorReading::with('device')->latest();

        if ($request->filled('date')) {
            $start = \Carbon\Carbon::parse($request->date);
            $end = $start->copy()->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $readings = $query->paginate(20);

        // Stats
        $totalReadings = SensorReading::count();
        $avgWaterLevel = SensorReading::avg('water_level_cm');
        $maxWaterLevel = SensorReading::max('water_level_cm');

        return view('history.index', compact('readings', 'totalReadings', 'avgWaterLevel', 'maxWaterLevel'));
    }
}
