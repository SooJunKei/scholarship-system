@extends('layouts.app')
@section('title', 'Applications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">{{ $applications->total() }} records</span>
    <a href="{{ route('applications.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Application</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>Scholarship</th><th>Date Applied</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td>{{ $app->student->full_name }}</td>
                    <td>{{ $app->scholarship->scholarship_name }}</td>
                    <td>{{ $app->date_applied->format('M d, Y') }}</td>
                    <td>
                        <span class="badge bg-{{ $app->status === 'Approved' ? 'success' : ($app->status === 'Rejected' ? 'danger' : ($app->status === 'Under Review' ? 'info' : 'warning text-dark')) }}">
                            {{ $app->status }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('applications.edit', $app) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('applications.destroy', $app) }}" class="d-inline" onsubmit="return confirm('Delete this application?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $applications->links() }}</div>
@endsection
