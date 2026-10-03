@extends('layouts.app')
@section('title', 'Add Requirement')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('requirements.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Requirement Name</label>
                <input type="text" name="requirement_name" class="form-control" value="{{ old('requirement_name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('requirements.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
