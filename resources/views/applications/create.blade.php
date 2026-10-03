@extends('layouts.app')
@section('title', 'New Application')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:700px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('applications.store') }}">
            @csrf
            @include('applications._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
