<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — FloodGuard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Custom Dashboard CSS -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

    <!-- Theme Init (prevent FOUC) -->
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>

    @stack('styles')
</head>
<body>
    <!-- Background Decoration -->
    <div class="bg-decoration"></div>

    <!-- Floating Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-water"></i>
            </div>
            <div class="brand-text">
                <h5>FloodGuard</h5>
                <small class="text-secondary">Admin Panel</small>
            </div>
        </div>

        <div class="nav flex-column">
            <span class="nav-section-title">Menu Utama</span>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="{{ route('alerts.index') }}" class="nav-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i> Peringatan
                @if(isset($unreadAlerts) && $unreadAlerts > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadAlerts }}</span>
                @endif
            </a>
            <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.*') ? 'active' : '' }}">
                <i class="fas fa-microchip"></i> Perangkat
                @if(isset($activeDevices))
                    <span class="badge bg-success rounded-pill ms-auto">{{ $activeDevices }}</span>
                @endif
            </a>
            <a href="{{ route('history.index') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i> Riwayat Data
            </a>
            
            <span class="nav-section-title">Konfigurasi</span>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Pengaturan
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px;">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div style="line-height: 1.2;">
                    <small class="d-block text-white fw-bold">{{ Auth::user()->name }}</small>
                    <small class="text-secondary" style="font-size: 10px;">Administrator</small>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-link text-secondary p-0" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar glass-panel mb-4" style="overflow: visible;">
            <div class="d-flex align-items-center">
                <h4 class="m-0 text-white">@yield('title', 'Dashboard')</h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Selector Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="themeDropdownBtn">
                        <i class="fas fa-sun me-1" id="themeIcon"></i> <span id="themeLabel">Terang</span>
                    </button>
                    <ul class="dropdown-menu dropdown-glass dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" onclick="applyTheme('light'); return false;" id="themeOptLight">
                                <i class="fas fa-sun text-cyan me-2"></i> Terang
                                <i class="fas fa-check ms-auto text-cyan" id="themeCheckLight" style="display:none;"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="applyTheme('dark'); return false;" id="themeOptDark">
                                <i class="fas fa-moon text-cyan me-2"></i> Gelap
                                <i class="fas fa-check ms-auto text-cyan" id="themeCheckDark" style="display:none;"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Status System Indicator -->
                 @if(isset($latestStatus))
                    @if($latestStatus == 'bahaya')
                         <div class="status-badge danger">
                            <i class="fas fa-exclamation-circle"></i> Bahaya
                        </div>
                    @elseif($latestStatus == 'siaga')
                         <div class="status-badge warning">
                            <i class="fas fa-exclamation-triangle"></i> Siaga
                        </div>
                    @else
                        <div class="status-badge">
                            <i class="fas fa-check-circle"></i> Aman
                        </div>
                    @endif
                @else
                    <div class="status-badge">
                        <i class="fas fa-check-circle"></i> Aman
                    </div>
                @endif
            </div>
        </header>

        <!-- Page Content -->
        @yield('content')
        
    </main>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="liveToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-info-circle me-2"></i> <span id="toastMessage">Hello, world!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
        
        // Toast function
        function showToast(message, type = 'primary') {
            const toastEl = document.getElementById('liveToast');
            const toastBody = document.getElementById('toastMessage');
            
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastBody.textContent = message;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // Theme Logic
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            const icon = document.getElementById('themeIcon');
            const label = document.getElementById('themeLabel');
            const checkLight = document.getElementById('themeCheckLight');
            const checkDark = document.getElementById('themeCheckDark');

            if(icon) {
                if(theme === 'light') {
                    icon.className = 'fas fa-sun me-1';
                    if(label) label.textContent = 'Terang';
                } else {
                    icon.className = 'fas fa-moon me-1';
                    if(label) label.textContent = 'Gelap';
                }
            }

            if(checkLight) checkLight.style.display = theme === 'light' ? 'inline' : 'none';
            if(checkDark) checkDark.style.display = theme === 'dark' ? 'inline' : 'none';
            
            // Dispatch event for charts
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: theme }));
        }

        // Init Theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        applyTheme(savedTheme);
    </script>

    {{-- Smooth Dropdown Close Animation --}}
    <script>
        document.addEventListener('hide.bs.dropdown', function(e) {
            const menu = e.target.querySelector('.dropdown-menu.dropdown-glass');
            if (menu) {
                e.preventDefault();
                menu.classList.add('dropdown-closing');
                menu.addEventListener('animationend', function handler() {
                    menu.classList.remove('dropdown-closing');
                    menu.classList.remove('show');
                    e.target.classList.remove('show');
                    const toggle = e.target.querySelector('[data-bs-toggle="dropdown"]');
                    if (toggle) {
                        toggle.classList.remove('show');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                    menu.removeEventListener('animationend', handler);
                }, { once: true });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
