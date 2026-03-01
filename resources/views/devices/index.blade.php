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
                <div class="d-flex align-items-center gap-2">
                    <div class="status-badge {{ $device->is_online ? 'success' : 'danger' }} px-2 py-1" style="font-size: 10px;">
                        {{ $device->is_online ? 'ONLINE' : 'OFFLINE' }}
                    </div>
                    <button class="btn btn-sm text-cyan p-1 lh-1" data-bs-toggle="modal" data-bs-target="#editDeviceModal{{ $device->id }}" title="Edit Perangkat" style="background: rgba(34, 211, 238, 0.1); border-radius: 6px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-pen" style="font-size: 10px;"></i>
                    </button>
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
            <div class="p-3 rounded-3 bg-themed-subtle border border-subtle">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-secondary text-uppercase fw-bold" style="font-size: 10px;">Status Terkini</small>
                    <span class="fw-bold {{ $device->latestReading->status == 'bahaya' ? 'text-danger' : ($device->latestReading->status == 'siaga' ? 'text-warning' : 'text-success') }}" style="font-size: 11px;">
                        {{ strtoupper($device->latestReading->status) }}
                    </span>
                </div>
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="p-2 rounded bg-themed-subtle border border-subtle">
                            <div class="text-cyan fw-bold fs-5">{{ number_format($device->latestReading->water_level_cm, 1) }}</div>
                            <small class="text-secondary" style="font-size: 10px;">Level (cm)</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded bg-themed-subtle border border-subtle">
                            <div class="text-primary fw-bold fs-5">{{ number_format($device->latestReading->distance_cm, 1) }}</div>
                            <small class="text-secondary" style="font-size: 10px;">Jarak (cm)</small>
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
                <ol class="text-secondary ps-3" style="font-size: 13px; line-height: 2.2;">
                    <li>Sensor HC-SR04 → NodeMCU ESP8266</li>
                    <li>NodeMCU → WiFi Hotspot HP/Router</li>
                    <li>Laptop (server) ← WiFi yang sama</li>
                    <li>NodeMCU POST ke <code class="text-cyan">http://[IP_LAPTOP]:8000/api/sensor</code></li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6 class="text-cyan mb-3">Wiring HC-SR04</h6>
                <table class="text-secondary" style="font-size: 13px; line-height: 2.2;">
                    <tr><td class="pe-3 fw-bold">VCC</td><td>→ 5V (NodeMCU VIN)</td></tr>
                    <tr><td class="pe-3 fw-bold">GND</td><td>→ GND</td></tr>
                    <tr><td class="pe-3 fw-bold">TRIG</td><td>→ D1 (GPIO5)</td></tr>
                    <tr><td class="pe-3 fw-bold">ECHO</td><td>→ D2 (GPIO4) via Voltage Divider</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($devices as $device)
<div class="modal fade" id="editDeviceModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-panel border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fs-6"><i class="fas fa-pen text-cyan me-2"></i>Edit Pengaturan Perangkat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
            </div>
            <form action="{{ route('devices.update', $device->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary small mb-1">Nama Sensor</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ $device->name }}" required placeholder="Contoh: Sensor Utama - Node1">
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-secondary small mb-1">Nama Lokasi</label>
                        <input type="text" name="location" class="form-control" 
                               value="{{ $device->location }}" placeholder="Contoh: Sungai Ciliwung KM 5">
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary border-0" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

