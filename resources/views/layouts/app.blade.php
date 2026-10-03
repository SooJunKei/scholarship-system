<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Scholarship System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a3c5e; width: 240px; position: fixed; top: 0; left: 0; }
        .sidebar .brand { padding: 1.25rem 1rem; color: #fff; font-size: 1.1rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar .nav-link { color: rgba(255,255,255,.75); padding: .6rem 1rem; border-radius: 6px; margin: 2px 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
        .sidebar .nav-link i { width: 20px; }
        .main-content { margin-left: 240px; padding: 1.5rem; }
        .topbar { background: #fff; border-bottom: 1px solid #dee2e6; padding: .75rem 1.5rem; margin: -1.5rem -1.5rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .badge-status-Pending { background: #ffc107; color: #000; }
        .badge-status-Under.Review { background: #0dcaf0; }
        .badge-status-Approved { background: #198754; }
        .badge-status-Rejected { background: #dc3545; }
    </style>
</head>
<body>
<div class="sidebar d-flex flex-column">
    <div class="brand"><i class="bi bi-mortarboard-fill me-2"></i>Scholarship System</div>
    <nav class="nav flex-column mt-2 flex-grow-1">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
            <i class="bi bi-people me-2"></i>Students
        </a>
        <a href="{{ route('scholarships.index') }}" class="nav-link {{ request()->routeIs('scholarships.*') ? 'active' : '' }}">
            <i class="bi bi-award me-2"></i>Scholarships
        </a>
        <a href="{{ route('applications.index') }}" class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text me-2"></i>Applications
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('requirements.index') }}" class="nav-link {{ request()->routeIs('requirements.*') ? 'active' : '' }}">
            <i class="bi bi-list-check me-2"></i>Requirements
        </a>
        @endif
    </nav>
    <div class="p-3 border-top border-secondary">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-left me-1"></i>Logout</button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h5 class="mb-0 fw-semibold">@yield('title', 'Dashboard')</h5>
        <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
            <span class="badge bg-secondary ms-1">{{ ucfirst(auth()->user()->role) }}</span>
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
