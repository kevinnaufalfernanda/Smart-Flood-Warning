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
                        <h6 class="m-0 text-white"><i class="fas fa-chart-area me-2 text-cyan"></i>Tren Ketinggian Air (24 Jam)</h6>
                        <div class="dropdown">
                            <button class="btn btn-glass dropdown-toggle" type="button" id="timeframeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-clock text-cyan"></i> <span id="timeframeLabel">24 Jam</span>
                            </button>
                            <ul class="dropdown-menu dropdown-glass dropdown-menu-end" aria-labelledby="timeframeDropdown">
                                <li><a class="dropdown-item active" href="#" onclick="event.preventDefault(); updateChartTimeframe(24); updateTimeframeLabel('24 Jam', this)">24 Jam</a></li>
                                <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); updateChartTimeframe(12); updateTimeframeLabel('12 Jam', this)">12 Jam</a></li>
                                <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); updateChartTimeframe(6); updateTimeframeLabel('6 Jam', this)">6 Jam</a></li>
                                <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); updateChartTimeframe(1); updateTimeframeLabel('1 Jam', this)">1 Jam</a></li>
                            </ul>
                        </div>
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
                        <h6 class="m-0 text-white"><i class="fas fa-triangle-exclamation me-2 text-cyan"></i>Peringatan Terkini</h6>
                        <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-link text-secondary text-decoration-none" style="font-size: 12px;">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    <div id="recentAlertsList" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
                        @forelse($recentAlerts as $alert)
                        <a href="{{ route('alerts.show', $alert->id) }}" class="text-decoration-none" style="color: inherit;">
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
                        </a>
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
    
    // -- Theme Helper --
    function getVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    // Colors (Initial)
    let colorCyan = getVar('--accent-cyan'); 
    let colorCyanAlpha = 'rgba(34, 211, 238, 0.2)'; // Fallback/Calc
    let colorPurple = getVar('--accent-purple');
    let colorDanger = getVar('--accent-danger');
    let colorWarning = getVar('--accent-warning');
    let colorSuccess = getVar('--accent-success');

    // Update Colors on Theme Change
    function updateChartTheme(e) {
        const theme = e.detail;
        
        // Refresh Variables
        colorCyan = getVar('--accent-cyan');
        colorPurple = getVar('--accent-purple');
        colorDanger = getVar('--accent-danger');
        colorWarning = getVar('--accent-warning');
        colorSuccess = getVar('--accent-success');

        const textColor = theme === 'light' ? '#000000' : '#94a3b8';
        const gridColor = theme === 'light' ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.05)';
        const tooltipBg = theme === 'light' ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.9)';
        const tooltipText = theme === 'light' ? '#000000' : '#e0f2fe';
        const gaugeBg = theme === 'light' ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.05)';

        // Update Line Chart
        lineChart.data.datasets[0].borderColor = colorCyan;
        lineChart.options.scales.x.ticks.color = textColor;
        lineChart.options.scales.x.grid.color = gridColor;
        lineChart.options.scales.y.ticks.color = textColor;
        lineChart.options.scales.y.grid.color = gridColor;
        lineChart.options.plugins.tooltip.backgroundColor = tooltipBg;
        lineChart.options.plugins.tooltip.titleColor = tooltipText;
        lineChart.options.plugins.tooltip.bodyColor = tooltipText; 
        lineChart.options.plugins.tooltip.borderColor = gridColor;
        
        lineChart.update();

        // Update Gauge Chart
        // We only change the empty part color mostly, active part is dynamic in updateGauge
        gaugeChart.data.datasets[0].backgroundColor[1] = gaugeBg;
        gaugeChart.update();
        
        // Retrigger gauge update to refresh active color using new vars
        // (We need current percent)
        const currentPercent = gaugeChart.data.datasets[0].data[0];
        updateGauge(currentPercent);
    }

    window.addEventListener('themeChanged', updateChartTheme);

    // Gauge Chart
    let gaugeChart = new Chart(ctxGauge, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [0, 100],
                backgroundColor: [colorCyan, (localStorage.getItem('theme') || 'light') === 'light' ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.05)'],
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
                x: { grid: { display: false, drawBorder: false }, ticks: { color: '#6e7681', maxRotation: 0, autoSkipPadding: 20 } },
                y: { grid: { color: 'rgba(255,255,255,0.05)', borderDash: [5, 5] }, ticks: { color: '#6e7681', stepSize: 1, precision: 0 }, min: 0 }
            }
        }
    });

    // Initial Data Load
    // Initial Data Load (Fetch 24h history instead of recent 10)
    updateChartTimeframe(24);
    // const initialData = @json($recentReadings);
    // updateLineChart(initialData.reverse());
    
    // Set initial gauge
    const initialLevel = {{ $latestReading ? $latestReading->water_level_percent : 0 }};
    updateGauge(initialLevel);
    
    // Trigger initial theme update in case logic runs before listener
    const currentTheme = localStorage.getItem('theme') || 'dark';
    window.dispatchEvent(new CustomEvent('themeChanged', { detail: currentTheme }));


    // -- Functions --
    function updateGauge(percent) {
        let color = colorSuccess;
        if(percent >= 50) color = colorWarning;
        if(percent >= 75) color = colorDanger;
        
        const currentThemeVal = document.documentElement.getAttribute('data-theme') || 'light';
        const gaugeBgColor = currentThemeVal === 'light' ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.05)';
        gaugeChart.data.datasets[0].data = [percent, 100 - percent];
        gaugeChart.data.datasets[0].backgroundColor = [color, gaugeBgColor];
        gaugeChart.update();

        // Update Visual Tank (Removed)
        // document.getElementById('waterLevelVisual').style.height = percent + '%';
        // document.getElementById('waterLevelVisual').style.background = 
        //     `linear-gradient(180deg, ${color}, ${color}88)`;
    }

    let lastReadingTime = null;

    function updateLineChart(data) {
        if(data.length > 0) {
            lastReadingTime = new Date(data[data.length - 1].created_at).getTime();
        }
        
        lineChart.data.labels = data.map(d => {
            const date = new Date(d.created_at);
            return date.getHours() + ':' + (date.getMinutes()<10?'0':'') + date.getMinutes();
        });
        lineChart.data.datasets[0].data = data.map(d => d.water_level_cm);
        lineChart.update();
    }

    function updateTimeframeLabel(label, element) {
        document.getElementById('timeframeLabel').innerText = label;
        // Update active class
        document.querySelectorAll('#timeframeDropdown + .dropdown-menu .dropdown-item').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }

    // Function to update chart timeframe from dropdown
    function updateChartTimeframe(hours) {
        // Show loading state if desired
        // fetch data
        fetch(`{{ route("api.sensor.history") }}?hours=${hours}&limit=500`)
            .then(r => r.json())
            .then(res => {
                if(res.success && res.data) {
                    // data is returned newest first, reverse for chart
                    updateLineChart(res.data.reverse());
                }
            })
            .catch(e => console.error(e));
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
                    const date = new Date(d.created_at);
                    const timeMs = date.getTime();
                    const hourMin = date.getHours() + ':' + (date.getMinutes()<10?'0':'') + date.getMinutes();
                    
                    if(!lastReadingTime || timeMs > lastReadingTime) {
                        lastReadingTime = timeMs;
                        
                        // Avoid duplicates if labels exist
                        lineChart.data.labels.push(hourMin);
                        lineChart.data.datasets[0].data.push(d.water_level_cm);
                        
                        if(lineChart.data.labels.length > 20) { // Keep last 20 points for live view
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
