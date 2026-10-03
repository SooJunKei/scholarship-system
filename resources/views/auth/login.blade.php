<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Scholarship System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; font-family: 'Segoe UI', sans-serif; background: #f0f4f8; }

        .auth-panel {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left branding panel */
        .brand-side {
            flex: 1;
            background: linear-gradient(145deg, #0d2d52 0%, #1a4f8a 60%, #1e6fbf 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .brand-side::before {
            content: '';
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            top: -100px; left: -100px;
        }
        .brand-side::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            bottom: -80px; right: -80px;
        }
        .brand-side .brand-icon {
            font-size: 4rem;
            margin-bottom: 1.25rem;
            position: relative; z-index: 1;
        }
        .brand-side h1 {
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: .5rem;
            position: relative; z-index: 1;
            text-align: center;
        }
        .brand-side p {
            font-size: .95rem;
            opacity: .8;
            text-align: center;
            max-width: 280px;
            line-height: 1.6;
            position: relative; z-index: 1;
        }
        .brand-features {
            margin-top: 2.5rem;
            list-style: none;
            padding: 0;
            position: relative; z-index: 1;
        }
        .brand-features li {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .875rem;
            opacity: .85;
            margin-bottom: .75rem;
        }
        .brand-features li i { font-size: 1rem; color: #7dd3fc; }

        /* Right form panel */
        .form-side {
            width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 3.5rem;
            background: #fff;
        }
        .form-side .form-header { margin-bottom: 2rem; }
        .form-side .form-header h2 { font-size: 1.6rem; font-weight: 700; color: #0d2d52; margin-bottom: .25rem; }
        .form-side .form-header p { color: #6b7280; font-size: .9rem; }

        .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: .65rem .9rem;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: #1a4f8a;
            box-shadow: 0 0 0 3px rgba(26,79,138,.12);
        }
        .form-label { font-size: .85rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }

        .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            color: #9ca3af;
            border-radius: 8px 0 0 8px;
        }
        .input-group .form-control { border-left: none; border-radius: 0 8px 8px 0; }
        .input-group .form-control:focus { border-color: #1a4f8a; box-shadow: none; }
        .input-group:focus-within .input-group-text { border-color: #1a4f8a; }

        .btn-primary-custom {
            background: linear-gradient(135deg, #1a4f8a, #1e6fbf);
            border: none;
            border-radius: 8px;
            padding: .7rem;
            font-size: .95rem;
            font-weight: 600;
            color: #fff;
            width: 100%;
            transition: opacity .2s, transform .1s;
        }
        .btn-primary-custom:hover { opacity: .92; transform: translateY(-1px); color: #fff; }
        .btn-primary-custom:active { transform: translateY(0); }

        .divider { display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0; color: #9ca3af; font-size: .8rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }

        .auth-link { color: #1a4f8a; font-weight: 600; text-decoration: none; }
        .auth-link:hover { text-decoration: underline; }

        .toggle-password { cursor: pointer; border: 1.5px solid #e2e8f0; border-left: none; background: #f8fafc; border-radius: 0 8px 8px 0; padding: 0 .75rem; color: #9ca3af; }
        .toggle-password:hover { color: #1a4f8a; }

        @media (max-width: 768px) {
            .brand-side { display: none; }
            .form-side { width: 100%; padding: 2rem 1.5rem; }
        }

        /* Page load animation */
        .form-side {
            animation: slideInRight .45s cubic-bezier(.22,.61,.36,1) both;
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(28px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .brand-side {
            animation: fadeIn .5s ease both;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* Brand icon float */
        .brand-icon { animation: float 3.5s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }

        /* Top progress bar */
        #progress-bar {
            position: fixed; top: 0; left: 0; height: 3px; width: 0;
            background: linear-gradient(90deg, #7dd3fc, #1e6fbf);
            z-index: 9999; transition: width .4s ease;
            border-radius: 0 2px 2px 0;
        }

        /* Button loading state */
        .btn-primary-custom.loading {
            opacity: .8; pointer-events: none;
        }
        .btn-primary-custom .spinner {
            display: none; width: 4px; height: 4px;
            background: #fff;
            border-radius: 50%;
            margin: 0 2px;
            animation: pulse 1.4s ease-in-out infinite;
            vertical-align: middle;
        }
        .btn-primary-custom.loading .spinner { display: inline-block; }
        .btn-primary-custom.loading .btn-icon { display: none; }
        @keyframes pulse { 
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1); }
        }

        /* Input focus ring ripple */
        .input-group:focus-within .input-group-text {
            border-color: #1a4f8a;
            transition: border-color .2s;
        }
    </style>
</head>
<body>
<div id="progress-bar"></div>
<div class="auth-panel">

    {{-- Left branding --}}
    <div class="brand-side">
        <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <h1>Scholarship Management System</h1>
        <p>Empowering students through accessible and transparent scholarship opportunities.</p>
        <ul class="brand-features">
            <li><i class="bi bi-check-circle-fill"></i> Manage scholarship programs</li>
            <li><i class="bi bi-check-circle-fill"></i> Track student applications</li>
            <li><i class="bi bi-check-circle-fill"></i> Evaluate & award scholarships</li>
            <li><i class="bi bi-check-circle-fill"></i> Monitor academic records</li>
        </ul>
    </div>

    {{-- Right form --}}
    <div class="form-side">
        <div class="form-header">
            <h2>Welcome back</h2>
            <p>Sign in to your account to continue</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.875rem; border-radius:8px;">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:.875rem;">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom" id="submit-btn">
                <span class="spinner me-2"></span>
                <i class="bi bi-box-arrow-in-right me-2 btn-icon"></i>
                <span class="btn-label">Sign In</span>
            </button>
        </form>

        <div class="divider">or</div>

        <p class="text-center mb-0" style="font-size:.875rem; color:#6b7280;">
            Don't have an account?
            <a href="{{ route('register') }}" class="auth-link">Create one</a>
        </p>
    </div>

</div>

<script>
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // Form submit loading state
    document.querySelector('form').addEventListener('submit', function () {
        const btn = document.getElementById('submit-btn');
        btn.classList.add('loading');
        btn.querySelector('.btn-label').textContent = 'Signing in…';
        startProgress();
    });

    // Progress bar on link navigation
    document.querySelectorAll('a[href]').forEach(a => {
        a.addEventListener('click', startProgress);
    });

    function startProgress() {
        const bar = document.getElementById('progress-bar');
        bar.style.width = '70%';
        setTimeout(() => bar.style.width = '100%', 400);
        setTimeout(() => bar.style.opacity = '0', 700);
    }
</script>
</body>
</html>
