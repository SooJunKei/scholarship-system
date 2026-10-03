@extends('layouts.app')
@section('title', 'Scholarships')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">{{ $scholarships->total() }} records</span>
    <a href="{{ route('scholarships.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Scholarship</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Sponsor</th><th>Amount</th><th>Deadline</th><th>Applications</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($scholarships as $s)
                <tr>
                    <td>{{ $s->scholarship_name }}</td>
                    <td>{{ $s->sponsor }}</td>
                    <td>₱{{ number_format($s->amount, 2) }}</td>
                    <td>{{ $s->application_deadline->format('M d, Y') }}</td>
                    <td>{{ $s->applications_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('scholarships.show', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('scholarships.edit', $s) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('scholarships.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete this scholarship?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No scholarships found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $scholarships->links() }}</div>
@endsection
