<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Smart Flood Warning</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Dashboard CSS -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

    <!-- Theme Init (prevent FOUC) -->
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</head>
<body>
    <!-- Background Decoration -->
    <div class="bg-decoration">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-water"></i>
                </div>
                <h3>FloodGuard</h3>
                <p>IoT Smart Flood Warning System</p>
            </div>

            @if($errors->any())
                <div class="mb-3 p-3 bg-danger-subtle border border-danger-subtle rounded-3">
                    <div style="font-size: 13px;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label-dark" for="email">
                        <i class="fas fa-envelope me-1"></i> Email
                    </label>
                    <input type="email" id="email" name="email" class="form-control-dark w-100"
                           placeholder="admin@iotbanjir.local" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label-dark" for="password">
                        <i class="fas fa-lock me-1"></i> Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control-dark w-100"
                           placeholder="••••••••" required>
                </div>

                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="color: var(--text-secondary); font-size: 13px;">
                            Ingat Saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-glow w-100" style="padding: 14px;">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </button>
            </form>

            <div class="text-center mt-4" style="font-size: 12px; color: var(--text-muted);">
                <i class="fas fa-shield-halved me-1"></i> Sistem Monitoring Banjir IoT v1.0
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
