@extends('layouts.dashboard')

@section('title', 'Riwayat Data')

@section('content')
<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Total Data</div>
                <h3>{{ number_format($totalReadings) }}</h3>
            </div>
            <div class="stat-icon text-purple">
                <i class="fas fa-database"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Rata-rata Level</div>
                <h3>{{ number_format($avgWaterLevel, 1) }} <small class="text-secondary fs-6">cm</small></h3>
            </div>
            <div class="stat-icon text-cyan">
                <i class="fas fa-water"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Level Tertinggi</div>
                <h3>{{ number_format($maxWaterLevel, 1) }} <small class="text-secondary fs-6">cm</small></h3>
            </div>
            <div class="stat-icon text-danger">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="glass-panel glass-panel-filter mb-4 p-3">
    <form method="GET" id="historyFilterForm" class="d-flex flex-wrap align-items-center gap-3">
        <div class="d-flex align-items-center gap-2 text-secondary">
            <span style="font-size: 13px;">Tanggal:</span>
            <input type="date" name="start_date" class="form-control-glass py-1" value="{{ request('start_date') }}" style="width: 140px; font-size: 13px;">
        </div>
        
        <!-- Device Filter -->
        <div class="d-flex align-items-center gap-2 text-secondary">
            <span style="font-size: 13px;">Perangkat:</span>
            <input type="hidden" name="device_id" id="input_device_id" value="{{ request('device_id') }}">
            <div class="dropdown">
                <button class="btn btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(request('device_id'))
                        {{ $devices->firstWhere('id', request('device_id'))?->name ?? 'Semua Perangkat' }}
                    @else
                        Semua Perangkat
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-glass">
                    <li><a class="dropdown-item {{ !request('device_id') ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('device_id', '', 'Semua Perangkat', this)">Semua Perangkat</a></li>
                    @foreach($devices as $device)
                    <li><a class="dropdown-item {{ request('device_id') == $device->id ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('device_id', '{{ $device->id }}', '{{ $device->name }}', this)">{{ $device->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Status Filter -->
        <input type="hidden" name="status" id="input_status" value="{{ request('status') }}">
        <div class="dropdown">
            <button class="btn btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                @if(request('status') == 'aman') <i class="fas fa-check-circle me-1"></i> Aman
                @elseif(request('status') == 'siaga') <i class="fas fa-exclamation-circle me-1"></i> Siaga
                @elseif(request('status') == 'bahaya') <i class="fas fa-exclamation-triangle me-1"></i> Bahaya
                @else Semua Status
                @endif
            </button>
            <ul class="dropdown-menu dropdown-glass">
                <li><a class="dropdown-item {{ !request('status') ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', '', 'Semua Status', this)">Semua Status</a></li>
                <li><a class="dropdown-item {{ request('status') == 'aman' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'aman', 'Aman', this)"><i class="fas fa-check-circle me-1"></i> Aman</a></li>
                <li><a class="dropdown-item {{ request('status') == 'siaga' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'siaga', 'Siaga', this)"><i class="fas fa-exclamation-circle me-1"></i> Siaga</a></li>
                <li><a class="dropdown-item {{ request('status') == 'bahaya' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'bahaya', 'Bahaya', this)"><i class="fas fa-exclamation-triangle me-1"></i> Bahaya</a></li>
            </ul>
        </div>

        <!-- Sort Filter -->
        <input type="hidden" name="sort" id="input_sort" value="{{ request('sort', 'desc') }}">
        <div class="dropdown">
            <button class="btn btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                 @if(request('sort', 'desc') == 'desc') <i class="fas fa-arrow-down me-1"></i> Terbaru @else <i class="fas fa-arrow-up me-1"></i> Terlama @endif
            </button>
            <ul class="dropdown-menu dropdown-glass">
                <li><a class="dropdown-item {{ request('sort', 'desc') == 'desc' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('sort', 'desc', 'Terbaru', this)"><i class="fas fa-arrow-down me-1"></i> Terbaru</a></li>
                <li><a class="dropdown-item {{ request('sort') == 'asc' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('sort', 'asc', 'Terlama', this)"><i class="fas fa-arrow-up me-1"></i> Terlama</a></li>
            </ul>
        </div>
        
        @if(request()->hasAny(['start_date', 'end_date', 'status', 'sort']))
            <a href="{{ route('history.index') }}" class="btn btn-sm btn-outline-secondary border-0 ms-auto">
                <i class="fas fa-times me-1"></i> Reset
            </a>
            <button type="submit" class="btn btn-sm btn-cyber">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
        @else
            <button type="submit" id="filterBtn" class="btn btn-sm btn-cyber ms-auto" style="display: none;">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
        @endif
    </form>
</div>

<!-- Data Table -->
<div class="glass-panel overflow-hidden">
    <div class="px-4 py-3 border-bottom border-subtle d-flex justify-content-between align-items-center">
        <h6 class="m-0 text-white"><i class="fas fa-table me-2 text-primary"></i>Log Pembacaan Sensor</h6>
        <span class="badge bg-subtle text-secondary fw-normal">Total: {{ $readings->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table-glass mb-0">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Waktu</th>
                    <th>Perangkat</th>
                    <th class="text-center">Jarak (cm)</th>
                    <th class="text-center">Level Air</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readings as $i => $r)
                <tr>
                    <td class="text-secondary text-center">{{ $readings->firstItem() + $i }}</td>
                    <td style="white-space: nowrap;">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-white" style="font-size: 13px;">{{ $r->created_at->format('d M Y') }}</span>
                            <span class="text-cyan fw-bold" style="font-size: 12px;">
                                <i class="far fa-clock me-1" style="font-size: 11px;"></i>{{ $r->created_at->format('H:i:s') }}
                            </span>
                        </div>
                    </td>
                    <td>{{ $r->device->name ?? 'Unknown' }}</td>
                    <td class="font-monospace text-secondary text-center">{{ number_format($r->distance_cm, 1) }}</td>
                    <td class="text-center">
                        <div class="d-inline-flex flex-column align-items-center">
                            <span class="fw-bold text-cyan">{{ number_format($r->water_level_cm, 1) }} cm</span>
                            <div class="progress mt-1" style="height: 4px; width: 80px; background: rgba(255,255,255,0.1);">
                                <div class="progress-bar {{ $r->status === 'bahaya' ? 'bg-danger' : ($r->status === 'siaga' ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" style="width: {{ $r->water_level_percent }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @if($r->status === 'aman') <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">AMAN</span>
                        @elseif($r->status === 'siaga') <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">SIAGA</span>
                        @else <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">BAHAYA</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                       <i class="fas fa-database mb-3 text-secondary" style="font-size: 32px; opacity: 0.5;"></i>
                       <p class="text-secondary m-0">Tidak ada data ditemukan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($readings->hasPages())
<div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-3">
    <div style="min-width: 180px;"></div>
    <div class="d-flex justify-content-center">
        {{ $readings->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    <div class="text-end" style="min-width: 180px;">
        <span class="text-secondary" style="font-size: 13px;">
            Showing {{ $readings->firstItem() }} to {{ $readings->lastItem() }} of {{ $readings->total() }} results
        </span>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    function setFilter(name, value, label, el) {
        // Update hidden input
        document.getElementById('input_' + name).value = value;
        // Update button label
        const btn = el.closest('.dropdown').querySelector('.dropdown-toggle');
        if (btn) {
            // Get icon from the clicked dropdown item
            const clickedIcon = el.querySelector('i');
            const iconHtml = clickedIcon ? clickedIcon.outerHTML + ' ' : '';
            btn.innerHTML = iconHtml + label;
        }
        // Update active state
        el.closest('ul').querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
        el.classList.add('active');

        // Show/hide filter button
        toggleFilterButton();
    }

    function toggleFilterButton() {
        const filterBtn = document.getElementById('filterBtn');
        if (!filterBtn) return;

        const startDate = document.querySelector('input[name="start_date"]')?.value || '';
        const deviceId = document.getElementById('input_device_id')?.value || '';
        const status = document.getElementById('input_status')?.value || '';
        const sort = document.getElementById('input_sort')?.value || '';

        if (startDate || deviceId || status || (sort && sort !== 'desc')) {
            filterBtn.style.display = '';
        } else {
            filterBtn.style.display = 'none';
        }
    }

    // Also listen for date input changes
    document.querySelector('input[name="start_date"]')?.addEventListener('change', toggleFilterButton);
</script>
@endpush
