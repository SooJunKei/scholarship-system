@extends('layouts.app')
@section('title', $student->full_name)

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Student Information</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted w-40">Student No.</th><td>{{ $student->student_number }}</td></tr>
                    <tr><th class="text-muted">Full Name</th><td>{{ $student->full_name }}</td></tr>
                    <tr><th class="text-muted">Gender</th><td>{{ $student->gender }}</td></tr>
                    <tr><th class="text-muted">Date of Birth</th><td>{{ $student->date_of_birth }}</td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ $student->email }}</td></tr>
                    <tr><th class="text-muted">Phone</th><td>{{ $student->phone }}</td></tr>
                    <tr><th class="text-muted">Course</th><td>{{ $student->course }}</td></tr>
                    <tr><th class="text-muted">Year Level</th><td>{{ $student->year_level }}</td></tr>
                    <tr><th class="text-muted">Department</th><td>{{ $student->department }}</td></tr>
                    <tr><th class="text-muted">Address</th><td>{{ $student->address }}</td></tr>
                </table>
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-primary">Edit</a>
                    <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Applications</h6>
                @forelse($student->applications as $app)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <div class="fw-semibold">{{ $app->scholarship->scholarship_name }}</div>
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
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Academic Records</h6>
                @forelse($student->academicRecords as $rec)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $rec->semester }} — {{ $rec->academic_year }}</span>
                    <span class="fw-semibold">GPA: {{ $rec->gpa }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0">No academic records.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
