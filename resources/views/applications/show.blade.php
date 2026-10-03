@extends('layouts.app')
@section('title', 'Application Details')

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Application Info</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted">Student</th><td>{{ $application->student->full_name }}</td></tr>
                    <tr><th class="text-muted">Scholarship</th><td>{{ $application->scholarship->scholarship_name }}</td></tr>
                    <tr><th class="text-muted">Date Applied</th><td>{{ $application->date_applied->format('M d, Y h:i A') }}</td></tr>
                    <tr><th class="text-muted">Status</th>
                        <td><span class="badge bg-{{ $application->status === 'Approved' ? 'success' : ($application->status === 'Rejected' ? 'danger' : ($application->status === 'Under Review' ? 'info' : 'warning text-dark')) }}">
                            {{ $application->status }}</span></td>
                    </tr>
                </table>
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('applications.edit', $application) }}" class="btn btn-sm btn-primary">Edit</a>
                    <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Submitted Requirements</h6>
                @forelse($application->submittedRequirements as $sr)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $sr->requirement->requirement_name }}</span>
                    <span class="badge bg-{{ $sr->status === 'Verified' ? 'success' : ($sr->status === 'Rejected' ? 'danger' : ($sr->status === 'Submitted' ? 'info' : 'warning text-dark')) }}">
                        {{ $sr->status }}
                    </span>
                </div>
                @empty
                <p class="text-muted small mb-0">No requirements submitted.</p>
                @endforelse
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Evaluations</h6>
                @forelse($application->evaluations as $ev)
                <div class="border-bottom py-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">{{ $ev->evaluator->name }}</span>
                        <span class="badge bg-primary">Score: {{ $ev->score }}</span>
                    </div>
                    <small class="text-muted">{{ $ev->remarks }}</small>
                </div>
                @empty
                <p class="text-muted small mb-0">No evaluations yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
