@extends('layouts.dashboard')

@section('title', 'Pengaturan')

@section('content')
<form method="POST" action="{{ route('settings.update') }}">
    @csrf

    <div class="row g-4">
        <!-- Left Column: Config -->
        <div class="col-lg-8">
            <!-- Thresholds -->
            <div class="glass-panel mb-4">
                <div class="p-3 border-bottom border-subtle">
                    <h6 class="m-0 text-white"><i class="fas fa-sliders-h me-2 text-cyan"></i>Konfigurasi Threshold</h6>
                </div>
                <div class="p-4">
                    <p class="text-secondary mb-4" style="font-size: 13px;">
                        Atur batas jarak sensor (cm) untuk menentukan status ketinggian air. Semakin kecil jarak = semakin tinggi air.
                    </p>

                    <!-- Visual -->
                    <div class="mb-4 p-3 rounded" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.2);">
                        <div class="d-flex justify-content-between mb-2 text-uppercase fw-bold" style="font-size: 10px;">
                            <span class="text-danger">🔴 Bahaya</span>
                            <span class="text-warning">🟡 Siaga</span>
                            <span class="text-success">🟢 Aman</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-danger" style="width: 30%"></div>
                            <div class="progress-bar bg-warning" style="width: 30%"></div>
                            <div class="progress-bar bg-success" style="width: 40%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 text-secondary" style="font-size: 11px;">
                            <span>0 cm (dekat)</span>
                            <span>Jarak Sensor</span>
                            <span>20+ cm (jauh)</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded border border-danger border-opacity-25 bg-danger bg-opacity-10 h-100">
                                <h6 class="text-danger mb-1">🔴 Bahaya</h6>
                                <p class="text-secondary small mb-2">Jarak < X cm</p>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="danger_threshold" class="form-control-glass" step="0.1" value="{{ $settings['danger_threshold']->value ?? 5 }}" style="width: 100%;">
                                    <span class="text-secondary small fw-bold" style="white-space: nowrap;">cm</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded border border-warning border-opacity-25 bg-warning bg-opacity-10 h-100">
                                <h6 class="text-warning mb-1">🟡 Siaga</h6>
                                <p class="text-secondary small mb-2">Jarak < X cm</p>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="warning_threshold" class="form-control-glass" step="0.1" value="{{ $settings['warning_threshold']->value ?? 7 }}" style="width: 100%;">
                                    <span class="text-secondary small fw-bold" style="white-space: nowrap;">cm</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded border border-success border-opacity-25 bg-success bg-opacity-10 h-100">
                                <h6 class="text-success mb-1">🟢 Aman</h6>
                                <p class="text-secondary small mb-2">Jarak > X cm</p>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="safe_threshold" class="form-control-glass" step="0.1" value="{{ $settings['safe_threshold']->value ?? 10 }}" style="width: 100%;">
                                    <span class="text-secondary small fw-bold" style="white-space: nowrap;">cm</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sensors -->
            <div class="glass-panel">
                <div class="p-3 border-bottom border-subtle">
                    <h6 class="m-0 text-white"><i class="fas fa-microchip me-2 text-purple"></i>Konfigurasi Sensor</h6>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-white">Tinggi Sensor (cm)</label>
                            <input type="number" name="sensor_height" class="form-control-glass" step="0.1" value="{{ $settings['sensor_height']->value ?? 20 }}">
                            <div class="form-text text-secondary">Jarak dari sensor ke dasar wadah air.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white">Interval Baca (detik)</label>
                            <input type="number" name="reading_interval" class="form-control-glass" step="1" min="1" max="60" value="{{ $settings['reading_interval']->value ?? 3 }}">
                            <div class="form-text text-secondary">Jeda waktu antar pembacaan data.</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top border-subtle">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="buzzer_enabled" id="buzzerSwitch" value="1"
                                   {{ ($settings['buzzer_enabled']->value ?? '1') === '1' ? 'checked' : '' }}
                                   style="cursor: pointer;">
                            <label class="form-check-label text-white ms-2" for="buzzerSwitch">
                                <i class="fas fa-volume-up me-2 text-cyan"></i>Aktifkan Alarm Buzzer
                            </label>
                        </div>
                        <p class="text-secondary small ms-5 mt-1">Buzzer akan berbunyi otomatis saat status mencapai BAHAYA.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Info & Action -->
        <div class="col-lg-4">
            <div class="glass-panel">
                <div class="p-3 border-bottom border-subtle">
                    <h6 class="m-0 text-white"><i class="fas fa-info-circle me-2 text-cyan"></i>Prinsip Kerja</h6>
                </div>
                <div class="p-4">
                    <!-- Sensor Info -->
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(14, 165, 233, 0.15);">
                            <i class="fas fa-microchip text-cyan" style="font-size: 14px;"></i>
                        </div>
                        <div>
                            <strong class="text-white d-block" style="font-size: 13px;">Sensor HC-SR04</strong>
                            <small class="text-secondary">Mengukur jarak pantulan gelombang suara ke permukaan air.</small>
                        </div>
                    </div>

                    <!-- How it works -->
                    <div class="mb-3">
                        <small class="text-secondary text-uppercase fw-bold d-block mb-2" style="font-size: 10px; letter-spacing: 0.5px;">Cara Pengukuran</small>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-cyan bg-opacity-10 text-cyan" style="min-width: 22px;">1</span>
                                <small class="text-secondary">Sensor memancarkan gelombang ultrasonik</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-cyan bg-opacity-10 text-cyan" style="min-width: 22px;">2</span>
                                <small class="text-secondary">Gelombang memantul dari permukaan air</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-cyan bg-opacity-10 text-cyan" style="min-width: 22px;">3</span>
                                <small class="text-secondary">Jarak dihitung dari waktu pantul</small>
                            </div>
                        </div>
                    </div>

                    <!-- Formula -->
                    <div class="p-3 rounded mb-4" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.2);">
                        <small class="text-cyan fw-bold d-block mb-1" style="font-size: 11px;"><i class="fas fa-calculator me-1"></i>Rumus Perhitungan</small>
                        <code class="text-white" style="font-size: 13px;">Level Air = Tinggi Sensor − Jarak Terukur</code>
                    </div>

                    <button type="submit" class="btn btn-cyber w-100 py-3">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                    
                    @if ($errors->any())
                    <div class="alert alert-danger mt-3 mb-0 bg-danger bg-opacity-10 border-danger border-opacity-25 text-white">
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
