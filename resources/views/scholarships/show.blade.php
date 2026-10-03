@extends('layouts.app')
@section('title', $scholarship->scholarship_name)

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Scholarship Details</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted">Name</th><td>{{ $scholarship->scholarship_name }}</td></tr>
                    <tr><th class="text-muted">Sponsor</th><td>{{ $scholarship->sponsor }}</td></tr>
                    <tr><th class="text-muted">Amount</th><td>₱{{ number_format($scholarship->amount, 2) }}</td></tr>
                    <tr><th class="text-muted">Deadline</th><td>{{ $scholarship->application_deadline->format('M d, Y') }}</td></tr>
                    <tr><th class="text-muted">Description</th><td>{{ $scholarship->description }}</td></tr>
                </table>
                @if($scholarship->requirements->count())
                <div class="mt-3">
                    <strong class="small">Requirements:</strong>
                    <div class="mt-1">
                        @foreach($scholarship->requirements as $req)
                        <span class="badge bg-secondary me-1">{{ $req->requirement_name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('scholarships.edit', $scholarship) }}" class="btn btn-sm btn-primary">Edit</a>
                    <a href="{{ route('scholarships.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Applications</h6>
                @forelse($scholarship->applications as $app)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <div class="fw-semibold">{{ $app->student->full_name }}</div>
                        <small class="text-muted">{{ $app->date_applied->format('M d, Y') }}</small>
                    </div>
                    <span class="badge bg-{{ $app->status === 'Approved' ? 'success' : ($app->status === 'Rejected' ? 'danger' : ($app->status === 'Under Review' ? 'info' : 'warning text-dark')) }}">
                        {{ $app->status }}
                    </span>
                </div>
                @empty
                <p class="text-muted small mb-0">No applications yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
