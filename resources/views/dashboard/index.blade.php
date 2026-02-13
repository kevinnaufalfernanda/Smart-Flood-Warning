@extends('layouts.dashboard')

@section('title', 'Dashboard Overview')

@section('content')
<div class="row g-4">
    <!-- Left Column: Hero Status (Gauge) -->
    <div class="col-xl-4 col-md-12">
        <div class="glass-panel hero-card h-100">
            <div class="d-flex justify-content-between w-100 mb-4">
                <h5 class="m-0 text-white"><i class="fas fa-tower-observation me-2 text-cyan"></i>Status Air</h5>
                <div class="live-indicator">
                    <span class="pulse-dot"></span> Live
                </div>
            </div>

            <!-- Content Wrapper for Vertical Centering -->
            <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
                <!-- Gauge Chart -->
                <div class="gauge-container">
                    <canvas id="gaugeChart"></canvas>
                    <!-- Central Value -->
                    <div class="position-absolute start-50 translate-middle text-center" style="top: 68%;">
                        <h2 class="m-0 fw-bold text-white" id="currentWaterLevel" style="font-size: 3rem;">
                            {{ $latestReading ? $latestReading->water_level_cm : 0 }}
                        </h2>
                        <small class="text-secondary">cm</small>
                    </div>
                </div>

                <!-- Detailed Status -->
                <div class="text-center mt-4">
                     <h3 class="mb-2 fw-bold" id="statusText" style="letter-spacing: 2px;">
                        @if(isset($latestStatus))
                            @if($latestStatus == 'aman') <span class="text-success">AMAN</span>
                            @elseif($latestStatus == 'siaga') <span class="text-warning">SIAGA</span>
                            @else <span class="text-danger">BAHAYA</span>
                            @endif
                        @else <span class="text-secondary">OFFLINE</span>
                        @endif
                    </h3>
                    <div class="d-flex justify-content-center gap-4 mt-3">
                        <div class="text-center">
                            <small class="text-secondary d-block">Jarak Sensor</small>
                            <span id="currentDistance" class="text-white fw-bold fs-5">{{ $latestReading ? $latestReading->distance_cm : 0 }} cm</span>
                        </div>
                        <div class="text-center">
                            <small class="text-secondary d-block">Terakhir Update</small>
                            <span id="latestUpdate" class="text-white fw-bold fs-5">{{ $latestReading ? $latestReading->created_at->format('H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Stats & Data -->
    <div class="col-xl-8 col-md-12">
        <div class="row g-4">
            <!-- Stat Cards -->
            <div class="col-md-4">
                <div class="glass-panel stat-item">
                    <div class="stat-content">
                        <div class="stat-label">Total Data</div>
                        <h3 id="totalReadings">{{ number_format($totalReadings) }}</h3>
                    </div>
                    <div class="stat-icon text-cyan">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-panel stat-item">
                    <div class="stat-content">
                        <div class="stat-label">Perangkat Aktif</div>
                        <h3 id="activeDevices">{{ $activeDevices }}</h3>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="fas fa-wifi"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-panel stat-item">
                    <div class="stat-content">
                        <div class="stat-label">Peringatan</div>
                        <h3 id="unreadAlerts" class="{{ $unreadAlerts > 0 ? 'text-danger' : '' }}">{{ $unreadAlerts }}</h3>
                    </div>
                    <div class="stat-icon text-danger">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>

            <!-- Main Chart -->
            <div class="col-12">
                <div class="glass-panel p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <h6 class="m-0 text-white"><i class="fas fa-chart-area me-2 text-purple"></i>Tren Ketinggian Air (24 Jam)</h6>
                        <select class="form-control-glass py-1 px-2" style="font-size: 12px; width: auto;" onchange="updateChartTimeframe(this.value)">
                            <option value="24">24 Jam</option>
                            <option value="12">12 Jam</option>
                            <option value="6">6 Jam</option>
                            <option value="1">1 Jam</option>
                        </select>
                    </div>
                    <div class="chart-container" style="height: 280px;">
                        <canvas id="waterLevelChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="col-12">
                <div class="glass-panel p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <h6 class="m-0 text-white"><i class="fas fa-triangle-exclamation me-2 text-warning"></i>Peringatan Terkini</h6>
                        <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-link text-secondary text-decoration-none" style="font-size: 12px;">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    <div id="recentAlertsList" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
                        @forelse($recentAlerts as $alert)
                        <div class="alert-row">
                            <div class="alert-icon-sm {{ $alert->type === 'danger' ? 'bg-danger-subtle' : ($alert->type === 'warning' ? 'bg-warning-subtle' : 'bg-cyan-subtle') }}">
                                <i class="fas fa-{{ $alert->type === 'danger' ? 'exclamation-circle' : ($alert->type === 'warning' ? 'exclamation-triangle' : 'info-circle') }}"></i>
                            </div>
                            <div class="alert-info">
                                <h6>{{ $alert->title }}</h6>
                                <p>{{ Str::limit($alert->message, 60) }}</p>
                            </div>
                            <div class="alert-time">
                                {{ $alert->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle mb-2" style="font-size: 24px; opacity: 0.3;"></i>
                            <p class="m-0" style="font-size: 13px;">Tidak ada peringatan terkini</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // -- Setup Charts --
    const ctxGauge = document.getElementById('gaugeChart').getContext('2d');
    const ctxLine = document.getElementById('waterLevelChart').getContext('2d');
    
    // Colors
    const colorCyan = '#22d3ee';
    const colorCyanAlpha = 'rgba(34, 211, 238, 0.2)';
    const colorPurple = '#a855f7';
    const colorDanger = '#ef4444';
    const colorWarning = '#f59e0b';
    const colorSuccess = '#10b981';

    // Gauge Chart
    let gaugeChart = new Chart(ctxGauge, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [0, 100],
                backgroundColor: [colorCyan, 'rgba(255,255,255,0.05)'],
                borderWidth: 0,
                cutout: '85%',
                rotation: -90,
                circumference: 180,
                borderRadius: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });

    // Line Chart
    let lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Ketinggian Air (cm)',
                data: [],
                borderColor: colorCyan,
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(34, 211, 238, 0.4)');
                    gradient.addColorStop(1, 'rgba(34, 211, 238, 0.0)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 0,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(13, 17, 23, 0.9)',
                    titleColor: '#fff',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: { label: (c) => `Level: ${c.parsed.y} cm` }
                }
            },
            scales: {
                x: { grid: { display: false, drawBorder: false }, ticks: { color: '#6e7681' } },
                y: { grid: { color: 'rgba(255,255,255,0.05)', borderDash: [5, 5] }, ticks: { color: '#6e7681' }, min: 0 }
            }
        }
    });

    // Initial Data Load
    const initialData = @json($recentReadings);
    updateLineChart(initialData.reverse());
    
    // Set initial gauge
    const initialLevel = {{ $latestReading ? $latestReading->water_level_percent : 0 }};
    updateGauge(initialLevel);


    // -- Functions --
    function updateGauge(percent) {
        let color = colorSuccess;
        if(percent >= 50) color = colorWarning;
        if(percent >= 75) color = colorDanger;
        
        gaugeChart.data.datasets[0].data = [percent, 100 - percent];
        gaugeChart.data.datasets[0].backgroundColor = [color, 'rgba(255,255,255,0.05)'];
        gaugeChart.update();

        // Update Visual Tank (Removed)
        // document.getElementById('waterLevelVisual').style.height = percent + '%';
        // document.getElementById('waterLevelVisual').style.background = 
        //     `linear-gradient(180deg, ${color}, ${color}88)`;
    }

    function updateLineChart(data) {
        lineChart.data.labels = data.map(d => {
            const date = new Date(d.created_at);
            return date.getHours() + ':' + (date.getMinutes()<10?'0':'') + date.getMinutes();
        });
        lineChart.data.datasets[0].data = data.map(d => d.water_level_cm);
        lineChart.update();
    }

    // Real-time Polling
    function fetchData() {
        fetch('{{ route("api.sensor.latest") }}')
            .then(r => r.json())
            .then(res => {
                if(res.success && res.data) {
                    const d = res.data;
                    
                    // Update Texts
                    document.getElementById('currentWaterLevel').innerText = d.water_level_cm;
                    document.getElementById('currentDistance').innerText = d.distance_cm + ' cm';
                    document.getElementById('latestUpdate').innerText = new Date(d.created_at).toLocaleTimeString();
                    
                    // Update Status Text
                    const statusEl = document.getElementById('statusText');
                    if(d.status === 'aman') statusEl.innerHTML = '<span class="text-success">AMAN</span>';
                    else if(d.status === 'siaga') statusEl.innerHTML = '<span class="text-warning">SIAGA</span>';
                    else statusEl.innerHTML = '<span class="text-danger">BAHAYA</span>';

                    // Update Visuals
                    updateGauge(d.water_level_percent);

                    // Push to Chart
                    const timeLabel = new Date(d.created_at).getHours() + ':' + new Date(d.created_at).getMinutes();
                    if(lineChart.data.labels[lineChart.data.labels.length - 1] !== timeLabel) {
                        lineChart.data.labels.push(timeLabel);
                        lineChart.data.datasets[0].data.push(d.water_level_cm);
                        if(lineChart.data.labels.length > 20) {
                            lineChart.data.labels.shift();
                            lineChart.data.datasets[0].data.shift();
                        }
                        lineChart.update();
                    }
                }
            })
            .catch(e => console.error(e));
    }

    function fetchStats() {
        // Optional: Endpoint to get updated counts for unread alerts etc.
        // For now we just reload page every 5 mins or rely on echoes if we had websockets
    }

    setInterval(fetchData, 3000); // Poll every 3s

</script>
<style>
    /* Pulse Animation for Live Indicator */
    .live-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: var(--accent-success);
        font-weight: 600;
        background: rgba(16, 185, 129, 0.1);
        padding: 4px 8px;
        border-radius: 20px;
    }
    .pulse-dot {
        width: 6px;
        height: 6px;
        background: var(--accent-success);
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>
@endpush
