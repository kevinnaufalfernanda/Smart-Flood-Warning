@extends('layouts.dashboard')

@section('title', 'Detail Peringatan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-outline-secondary me-3 border-subtle bg-subtle text-primary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="m-0 fw-bold text-primary">Detail Peringatan</h4>
            </div>

            <div class="glass-panel p-4 position-relative overflow-hidden">
                <!-- Status Badge -->
                <div class="position-absolute top-0 end-0 m-4">
                    @if($alert->type === 'danger')
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">BAHAYA</span>
                    @elseif($alert->type === 'warning')
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">SIAGA</span>
                    @else
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">INFO</span>
                    @endif
                </div>

                <!-- Icon -->
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-subtle text-secondary" 
                         style="width: 64px; height: 64px; font-size: 24px;">
                        @if($alert->type === 'danger') <i class="fas fa-exclamation-triangle text-danger"></i>
                        @elseif($alert->type === 'warning') <i class="fas fa-exclamation-circle text-warning"></i>
                        @else <i class="fas fa-info-circle text-cyan"></i>
                        @endif
                    </div>
                </div>

                <!-- Content -->
                <h3 class="text-primary fw-bold mb-3">{{ $alert->title }}</h3>
                <p class="text-secondary fs-5 mb-4" style="line-height: 1.6;">{{ $alert->message }}</p>

                <hr class="border-secondary opacity-25 my-4">

                <!-- Meta Data -->
                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <small class="text-secondary d-block mb-1">Waktu Kejadian</small>
                        <span class="text-primary fw-medium">
                            <i class="far fa-clock me-2 text-cyan"></i>{{ $alert->created_at->format('H:i') }}
                        </span>
                        <div class="text-secondary opacity-75" style="font-size: 13px; margin-left: 24px;">{{ $alert->created_at->format('d M Y') }}</div>
                    </div>
                    @if($alert->water_level)
                    <div class="col-6 col-md-3">
                        <small class="text-secondary d-block mb-1">Ketinggian Air</small>
                        <span class="text-primary fw-bold fs-5">{{number_format($alert->water_level, 1) }} cm</span>
                    </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <small class="text-secondary d-block mb-1">Status Baca</small>
                        <span class="text-success fw-medium"><i class="fas fa-check-double me-1"></i> Dibaca</span>
                        <div class="text-secondary opacity-75" style="font-size: 12px;">{{ $alert->updated_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
