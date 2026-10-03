@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people-fill text-primary fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $totalStudents }}</div>
                    <div class="text-muted small">Total Students</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-award-fill text-success fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $totalScholarships }}</div>
                    <div class="text-muted small">Scholarships</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-file-earmark-text-fill text-info fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $totalApplications }}</div>
                    <div class="text-muted small">Total Applications</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $pendingApplications }}</div>
                    <div class="text-muted small">Pending Applications</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3">
        <i class="bi bi-activity text-primary"></i>
        <h6 class="mb-0 fw-semibold">Recent Activity</h6>
    </div>
    <div class="card-body p-0">
        @forelse($activityLogs as $log)
            @php
                $meta = [
                    'ApplicationSubmitted'    => ['icon' => 'bi-file-earmark-plus',  'color' => 'text-primary',  'bg' => 'bg-primary'],
                    'ApplicationStatusChanged'=> ['icon' => 'bi-arrow-left-right',   'color' => 'text-warning',  'bg' => 'bg-warning'],
                    'StudentRegistered'       => ['icon' => 'bi-person-plus',         'color' => 'text-success',  'bg' => 'bg-success'],
                    'ScholarshipCreated'      => ['icon' => 'bi-award',               'color' => 'text-info',     'bg' => 'bg-info'],
                ][$log->event] ?? ['icon' => 'bi-bell', 'color' => 'text-secondary', 'bg' => 'bg-secondary'];
            @endphp
            <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom">
                <div class="rounded-circle {{ $meta['bg'] }} bg-opacity-10 p-2 mt-1" style="min-width:36px; text-align:center">
                    <i class="bi {{ $meta['icon'] }} {{ $meta['color'] }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="small">{{ $log->description }}</div>
                    <div class="text-muted" style="font-size:.75rem">
                        {{ $log->created_at->diffForHumans() }}
                        @if($log->user) &mdash; {{ $log->user->name }} @endif
                    </div>
                </div>
                <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.7rem; white-space:nowrap">
                    {{ str($log->event)->headline() }}
                </span>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No activity yet.
            </div>
        @endforelse
    </div>
</div>
@endsection
