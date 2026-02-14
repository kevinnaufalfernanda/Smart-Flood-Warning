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
            <span style="font-size: 13px;">Dari:</span>
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
                @if(request('status') == 'aman') 🟢 Aman
                @elseif(request('status') == 'siaga') 🟡 Siaga
                @elseif(request('status') == 'bahaya') 🔴 Bahaya
                @else Semua Status
                @endif
            </button>
            <ul class="dropdown-menu dropdown-glass">
                <li><a class="dropdown-item {{ !request('status') ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', '', 'Semua Status', this)">Semua Status</a></li>
                <li><a class="dropdown-item {{ request('status') == 'aman' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'aman', '🟢 Aman', this)">🟢 Aman</a></li>
                <li><a class="dropdown-item {{ request('status') == 'siaga' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'siaga', '🟡 Siaga', this)">🟡 Siaga</a></li>
                <li><a class="dropdown-item {{ request('status') == 'bahaya' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'bahaya', '🔴 Bahaya', this)">🔴 Bahaya</a></li>
            </ul>
        </div>

        <!-- Sort Filter -->
        <input type="hidden" name="sort" id="input_sort" value="{{ request('sort', 'desc') }}">
        <div class="dropdown">
            <button class="btn btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                 {{ request('sort', 'desc') == 'desc' ? '⬇️ Terbaru' : '⬆️ Terlama' }}
            </button>
            <ul class="dropdown-menu dropdown-glass">
                <li><a class="dropdown-item {{ request('sort', 'desc') == 'desc' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('sort', 'desc', '⬇️ Terbaru', this)">⬇️ Terbaru</a></li>
                <li><a class="dropdown-item {{ request('sort') == 'asc' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('sort', 'asc', '⬆️ Terlama', this)">⬆️ Terlama</a></li>
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
            <button type="submit" class="btn btn-sm btn-cyber ms-auto">
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
                    <th width="60">#</th>
                    <th>Waktu</th>
                    <th>Perangkat</th>
                    <th>Jarak (cm)</th>
                    <th>Level Air</th>
                    <th>Status</th>
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
                    <td class="font-monospace text-secondary">{{ number_format($r->distance_cm, 1) }}</td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-cyan">{{ number_format($r->water_level_cm, 1) }} cm</span>
                            <div class="progress mt-1" style="height: 4px; width: 80px; background: rgba(255,255,255,0.1);">
                                <div class="progress-bar {{ $r->status === 'bahaya' ? 'bg-danger' : ($r->status === 'siaga' ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" style="width: {{ $r->water_level_percent }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
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
<div class="d-flex justify-content-center mt-4">
    {{ $readings->withQueryString()->links('pagination::bootstrap-5') }}
</div>
@endsection

@push('scripts')
<script>
    function setFilter(name, value, label, el) {
        // Update hidden input
        document.getElementById('input_' + name).value = value;
        // Update button label
        const btn = el.closest('.dropdown').querySelector('.dropdown-toggle');
        if (btn) {
            // Keep icon if exists
            const icon = btn.querySelector('i');
            const iconHtml = icon ? icon.outerHTML + ' ' : '';
            btn.innerHTML = iconHtml + label + ' <span class="dropdown-toggle-arrow"></span>';
        }
        // Update active state
        el.closest('ul').querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
        el.classList.add('active');
    }
</script>
@endpush
