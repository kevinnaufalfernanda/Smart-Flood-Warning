<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $latestReading = SensorReading::latest()->first();
        $totalReadings = SensorReading::count();
        $activeDevices = Device::where('is_online', true)->count();
        $totalDevices = Device::count();
        $unreadAlerts = Alert::unread()->count();
        $recentAlerts = Alert::latest()->take(5)->get();
        $recentReadings = SensorReading::with('device')->latest()->take(10)->get();

        // Stats for cards
        $todayReadings = SensorReading::whereDate('created_at', today())->count();
        $dangerCount = SensorReading::whereDate('created_at', today())->where('status', 'bahaya')->count();

        return view('dashboard.index', compact(
            'latestReading',
            'totalReadings',
            'activeDevices',
            'totalDevices',
            'unreadAlerts',
            'recentAlerts',
            'recentReadings',
            'todayReadings',
            'dangerCount'
        ));
    }

    public function getRealtimeData()
    {
        $latest = SensorReading::with('device')->latest()->first();
        $last24h = SensorReading::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->get(['water_level_cm', 'water_level_percent', 'distance_cm', 'status', 'created_at']);

        $statusCounts = SensorReading::where('created_at', '>=', now()->subHours(24))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $unreadAlerts = Alert::unread()->count();

        return response()->json([
            'latest' => $latest,
            'chart_data' => $last24h->map(function ($r) {
                return [
                    'time' => $r->created_at->format('H:i'),
                    'water_level' => $r->water_level_cm,
                    'percent' => $r->water_level_percent,
                    'distance' => $r->distance_cm,
                    'status' => $r->status,
                ];
            }),
            'status_counts' => $statusCounts,
            'unread_alerts' => $unreadAlerts,
        ]);
    }
}
