@extends('layouts.dashboard')

@section('title', 'Peringatan')

@section('content')
<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Belum Dibaca</div>
                <h3 class="{{ $totalUnread > 0 ? 'text-danger' : '' }}">{{ $totalUnread }}</h3>
            </div>
            <div class="stat-icon text-cyan">
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Total Bahaya</div>
                <h3>{{ $dangerCount }}</h3>
            </div>
            <div class="stat-icon text-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-panel stat-item">
            <div class="stat-content">
                <div class="stat-label">Total Siaga</div>
                <h3>{{ $warningCount }}</h3>
            </div>
            <div class="stat-icon text-warning">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Actions -->
<div class="glass-panel glass-panel-filter mb-4 p-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <form method="GET" id="alertsFilterForm" class="d-flex flex-wrap align-items-center gap-3 flex-grow-1">
            <!-- Type Filter -->
            <input type="hidden" name="type" id="input_type" value="{{ request('type') }}">
            <div class="dropdown">
                <button class="btn btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 140px;">
                    @if(request('type') == 'danger') <i class="fas fa-exclamation-triangle me-1"></i> Bahaya
                    @elseif(request('type') == 'warning') <i class="fas fa-exclamation-circle me-1"></i> Siaga
                    @elseif(request('type') == 'info') <i class="fas fa-info-circle me-1"></i> Info
                    @else Semua Tipe
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-glass">
                    <li><a class="dropdown-item {{ !request('type') ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('type', '', 'Semua Tipe', this)">Semua Tipe</a></li>
                    <li><a class="dropdown-item {{ request('type') == 'danger' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('type', 'danger', 'Bahaya', this)"><i class="fas fa-exclamation-triangle me-1"></i> Bahaya</a></li>
                    <li><a class="dropdown-item {{ request('type') == 'warning' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('type', 'warning', 'Siaga', this)"><i class="fas fa-exclamation-circle me-1"></i> Siaga</a></li>
                    <li><a class="dropdown-item {{ request('type') == 'info' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('type', 'info', 'Info', this)"><i class="fas fa-info-circle me-1"></i> Info</a></li>
                </ul>
            </div>

            <!-- Status Filter -->
            <input type="hidden" name="status" id="input_status" value="{{ request('status') }}">
            <div class="dropdown">
                <button class="btn btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 140px;">
                    @if(request('status') == 'unread') <i class="fas fa-envelope me-1"></i> Belum Dibaca
                    @elseif(request('status') == 'read') <i class="fas fa-envelope-open me-1"></i> Sudah Dibaca
                    @else Semua Status
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-glass">
                    <li><a class="dropdown-item {{ !request('status') ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', '', 'Semua Status', this)">Semua Status</a></li>
                    <li><a class="dropdown-item {{ request('status') == 'unread' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'unread', 'Belum Dibaca', this)">Belum Dibaca</a></li>
                    <li><a class="dropdown-item {{ request('status') == 'read' ? 'active' : '' }}" href="#" onclick="event.preventDefault(); setFilter('status', 'read', 'Sudah Dibaca', this)">Sudah Dibaca</a></li>
                </ul>
            </div>

            <!-- Filter & Reset Buttons -->
            @if(request()->hasAny(['type', 'status']))
                <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-outline-secondary border-0 ms-auto">
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

        @if($totalUnread > 0)
        <form method="POST" action="{{ route('alerts.readAll') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-cyber">
                <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Alerts Table -->
<div class="glass-panel overflow-hidden">
    <div class="table-responsive">
        <table class="table-glass mb-0">
            <thead>
                <tr>
                    <th width="80" class="text-center" style="padding-left: 24px;">Tipe</th>
                    <th>Peringatan</th>
                    <th class="text-center">Level Air</th>
                    <th class="text-center">Waktu</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr style="{{ !$alert->is_read ? 'background: rgba(34, 211, 238, 0.05);' : '' }} cursor: pointer;" 
                    onclick="if(!event.target.closest('button') && !event.target.closest('a')) window.location='{{ route('alerts.show', $alert->id) }}'"
                    class="alert-row-hover">
                    <td class="text-center" style="padding-left: 24px;">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto" 
                             style="width: 32px; height: 32px; background: var(--bg-subtle); color: var(--text-secondary);">
                            @if($alert->type === 'danger') <i class="fas fa-exclamation-triangle text-danger"></i>
                            @elseif($alert->type === 'warning') <i class="fas fa-exclamation-circle text-warning"></i>
                            @else <i class="fas fa-info-circle text-cyan"></i>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-between">
                            <strong class="d-block" style="font-size: 14px;">
                                <span class="text-white text-decoration-none hover-underline">
                                    {{ $alert->title }}
                                </span>
                            </strong>
                            <small class="text-secondary ms-2 d-md-none">{{ $alert->created_at->format('H:i') }}</small>
                        </div>
                        <small class="text-secondary d-block">{{ Str::limit($alert->message, 80) }}</small>
                    </td>
                    <td class="text-center">
                        @if($alert->water_level)
                            <span class="fw-bold text-cyan">{{ number_format($alert->water_level, 1) }} cm</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 13px;">
                        <span class="d-block text-white fw-bold">{{ $alert->created_at->format('d M Y') }}</span>
                        <span class="text-secondary opacity-75" style="font-size: 12px;">{{ $alert->created_at->format('H:i') }} WIB</span>
                    </td>
                    <td class="text-center">
                        @if($alert->is_read)
                            <span class="badge bg-secondary bg-opacity-25 text-secondary fw-normal">Dibaca</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">BARU</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="fas fa-check-circle mb-3 text-success" style="font-size: 48px; opacity: 0.5;"></i>
                        <h5 class="text-white">Tidak Ada Peringatan</h5>
                        <p class="text-secondary">Sistem berjalan normal.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $alerts->withQueryString()->links('pagination::bootstrap-5') }}
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
            // Get icon from the clicked dropdown item
            const clickedIcon = el.querySelector('i');
            const iconHtml = clickedIcon ? clickedIcon.outerHTML + ' ' : '';
            btn.innerHTML = iconHtml + label;
        }
        // Update active state
        el.closest('ul').querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
        el.classList.add('active');

        // Show/hide filter button based on whether any filter is selected
        toggleFilterButton();
    }

    function toggleFilterButton() {
        const filterBtn = document.getElementById('filterBtn');
        if (!filterBtn) return; // Already has active filters (server-side rendered)
        
        const typeVal = document.getElementById('input_type').value;
        const statusVal = document.getElementById('input_status').value;
        
        if (typeVal || statusVal) {
            filterBtn.style.display = '';
        } else {
            filterBtn.style.display = 'none';
        }
    }
</script>
@endpush
