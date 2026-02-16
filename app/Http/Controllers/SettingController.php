<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'sensor_height' => 'required|numeric|min:1|max:500',
            'danger_threshold' => 'required|numeric|min:1|max:500',
            'warning_threshold' => 'required|numeric|min:1|max:500',
            'safe_threshold' => 'required|numeric|min:1|max:500',
            'reading_interval' => 'required|numeric|min:1|max:60',
            'buzzer_enabled' => 'nullable|boolean',
        ]);

        $keys = ['sensor_height', 'danger_threshold', 'warning_threshold', 'safe_threshold', 'reading_interval'];

        foreach ($keys as $key) {
            Setting::setValue($key, $request->input($key));
        }

        Setting::setValue('buzzer_enabled', $request->has('buzzer_enabled') ? '1' : '0');

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}
