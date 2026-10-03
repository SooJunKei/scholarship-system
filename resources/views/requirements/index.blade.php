@extends('layouts.app')
@section('title', 'Requirements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">{{ $requirements->total() }} records</span>
    <a href="{{ route('requirements.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Requirement</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Description</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($requirements as $req)
                <tr>
                    <td>{{ $req->requirement_name }}</td>
                    <td>{{ Str::limit($req->description, 80) }}</td>
                    <td class="text-end">
                        <a href="{{ route('requirements.edit', $req) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('requirements.destroy', $req) }}" class="d-inline" onsubmit="return confirm('Delete this requirement?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No requirements found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $requirements->links() }}</div>
@endsection
