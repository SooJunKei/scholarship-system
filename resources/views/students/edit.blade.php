@extends('layouts.app')
@section('title', 'Edit Student')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:900px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf @method('PUT')
            @include('students._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Student</button>
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
