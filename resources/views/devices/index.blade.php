@extends('layouts.dashboard')

@section('title', 'Perangkat')

@section('content')
<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Perangkat Online</div>
                <h3>{{ $onlineCount }}</h3>
            </div>
            <div class="stat-icon text-success">
                <i class="fas fa-wifi"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Total Perangkat</div>
                <h3>{{ $totalCount }}</h3>
            </div>
            <div class="stat-icon text-cyan">
                <i class="fas fa-microchip"></i>
            </div>
        </div>
    </div>
</div>

<!-- Device Cards -->
<div class="row g-4">
    @forelse($devices as $device)
    <div class="col-lg-4 col-md-6">
        <div class="glass-panel h-100 p-4 position-relative">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-cyan-subtle">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div>
                        <h5 class="m-0 fw-bold text-white">{{ $device->name }}</h5>
                        <small class="text-secondary"><i class="fas fa-map-marker-alt me-1"></i> {{ $device->location ?? 'Lokasi tidak diatur' }}</small>
                    </div>
                </div>
                <div class="status-badge {{ $device->is_online ? 'success' : 'danger' }} px-2 py-1" style="font-size: 10px;">
                    {{ $device->is_online ? 'ONLINE' : 'OFFLINE' }}
                </div>
            </div>

            <!-- Meta -->
            <div class="mb-4" style="font-size: 13px; color: var(--text-secondary);">
                <div class="d-flex justify-content-between mb-2">
                    <span>IP Address</span>
                    <span class="text-white font-monospace">{{ $device->ip_address ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Pembacaan</span>
                    <span class="text-white">{{ number_format($device->sensor_readings_count) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Terakhir Aktif</span>
                    <span class="text-white">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '-' }}</span>
                </div>
            </div>

            <!-- Latest Reading -->
            @if($device->latestReading)
            <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Status Terkini</small>
                    <span class="fw-bold {{ $device->latestReading->status == 'bahaya' ? 'text-danger' : ($device->latestReading->status == 'siaga' ? 'text-warning' : 'text-success') }}" style="font-size: 11px;">
                        {{ strtoupper($device->latestReading->status) }}
                    </span>
                </div>
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="p-2 rounded bg-opacity-10 bg-white">
                            <div class="text-cyan fw-bold fs-5">{{ number_format($device->latestReading->water_level_cm, 1) }}</div>
                            <small class="text-muted" style="font-size: 10px;">Level (cm)</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded bg-opacity-10 bg-white">
                            <div class="text-white fw-bold fs-5">{{ number_format($device->latestReading->distance_cm, 1) }}</div>
                            <small class="text-muted" style="font-size: 10px;">Jarak (cm)</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="glass-panel p-5 text-center">
            <i class="fas fa-plug mb-3 text-secondary" style="font-size: 48px; opacity: 0.5;"></i>
            <h5 class="text-white">Belum Ada Perangkat</h5>
            <p class="text-secondary">Hubungkan NodeMCU ESP8266 ke jaringan WiFi dan kirim data ke endpoint API.</p>
        </div>
    </div>
    @endforelse
</div>

<!-- Guide -->
<div class="glass-panel mt-4 p-0 overflow-hidden">
    <div class="p-3 border-bottom border-white border-opacity-10" style="background: rgba(34, 211, 238, 0.05);">
        <h6 class="m-0 text-primary"><i class="fas fa-book me-2 text-cyan"></i>Panduan Koneksi NodeMCU</h6>
    </div>
    <div class="p-4">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-cyan mb-3">Topologi Koneksi</h6>
                <ul class="text-secondary" style="font-size: 13px; line-height: 2;">
                    <li>1️⃣ Sensor HC-SR04 → NodeMCU ESP8266</li>
                    <li>2️⃣ NodeMCU → WiFi Hotspot HP/Router</li>
                    <li>3️⃣ Laptop (server) ← WiFi yang sama</li>
                    <li>4️⃣ NodeMCU POST ke <code class="text-cyan">http://[IP_LAPTOP]:8000/api/sensor</code></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-cyan mb-3">Wiring HC-SR04</h6>
                <ul class="text-secondary" style="font-size: 13px; line-height: 2;">
                    <li>🔴 VCC → 5V (NodeMCU VIN)</li>
                    <li>⚫ GND → GND</li>
                    <li>🟡 TRIG → D1 (GPIO5)</li>
                    <li>🟢 ECHO → D2 (GPIO4) via Voltage Divider</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
