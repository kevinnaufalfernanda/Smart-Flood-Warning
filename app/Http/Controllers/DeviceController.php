<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::withCount('sensorReadings')
            ->with('latestReading')
            ->get();

        $onlineCount = $devices->where('is_online', true)->count();
        $totalCount = $devices->count();

        return view('devices.index', compact('devices', 'onlineCount', 'totalCount'));
    }
}
