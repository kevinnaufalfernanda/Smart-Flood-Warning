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
<div class="glass-panel mb-4 p-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <form method="GET" class="d-flex gap-2">
            <select name="type" class="form-control-glass py-1" style="width: 140px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="danger" {{ request('type') === 'danger' ? 'selected' : '' }}>🚨 Bahaya</option>
                <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>⚠️ Siaga</option>
                <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>ℹ️ Info</option>
            </select>
            <select name="status" class="form-control-glass py-1" style="width: 140px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
            </select>
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
                    <th width="50">#</th>
                    <th>Peringatan</th>
                    <th>Level Air</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th width="80" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr style="{{ !$alert->is_read ? 'background: rgba(34, 211, 238, 0.05);' : '' }}">
                    <td>
                        <div class="d-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 32px; height: 32px; background: rgba(255,255,255,0.05); color: var(--text-secondary);">
                            @if($alert->type === 'danger') <i class="fas fa-exclamation-triangle text-danger"></i>
                            @elseif($alert->type === 'warning') <i class="fas fa-exclamation-circle text-warning"></i>
                            @else <i class="fas fa-info-circle text-cyan"></i>
                            @endif
                        </div>
                    </td>
                    <td>
                        <strong class="d-block text-white" style="font-size: 14px;">{{ $alert->title }}</strong>
                        <small class="text-secondary">{{ Str::limit($alert->message, 80) }}</small>
                    </td>
                    <td>
                        @if($alert->water_level)
                            <span class="fw-bold text-cyan">{{ number_format($alert->water_level, 1) }} cm</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="font-size: 13px;">
                        <span class="d-block text-white">{{ $alert->created_at->format('d M Y') }}</span>
                        <span class="text-muted">{{ $alert->created_at->format('H:i') }}</span>
                    </td>
                    <td>
                        @if($alert->is_read)
                            <span class="badge bg-secondary bg-opacity-25 text-secondary fw-normal">Dibaca</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">BARU</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if(!$alert->is_read)
                        <form method="POST" action="{{ route('alerts.read', $alert->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-success p-0" title="Tandai Dibaca">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
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
