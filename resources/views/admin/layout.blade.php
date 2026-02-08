<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Kiosk Dashboard Admin</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        /* Layout */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1a1a2e;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-header span {
            font-size: 0.8rem;
            opacity: 0.7;
            display: block;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.4);
            margin-top: 0.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: #ffd369;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .sidebar-footer a:hover {
            color: #fff;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Buttons */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #1a1a2e;
            color: white;
        }

        .btn-primary:hover {
            background: #2d2d4a;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Form */
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.3rem;
            color: #666;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1a1a2e;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: 600;
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background: #fafafa;
        }

        .badge {
            padding: 0.25rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-national { background: #e3f2fd; color: #1565c0; }
        .badge-observance { background: #f3e5f5; color: #7b1fa2; }
        .badge-custom { background: #fff3e0; color: #ef6c00; }
        .badge-api { background: #e8f5e9; color: #2e7d32; }
        .badge-manual { background: #fce4ec; color: #c2185b; }
        .badge-time {
            background: #e3f2fd;
            color: #1565c0;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
        }

        .row-hidden {
            opacity: 0.5;
            background: #f9f9f9;
        }

        .row-hidden td {
            text-decoration: line-through;
            text-decoration-color: #ccc;
        }

        .row-hidden .actions,
        .row-hidden .badge {
            text-decoration: none;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Year filter */
        .year-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .year-filter select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            opacity: 0;
            transform: translateY(1rem);
            transition: all 0.3s;
            z-index: 1000;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Mobile menu toggle */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #1a1a2e;
            color: #fff;
            padding: 0 1rem;
            align-items: center;
            justify-content: space-between;
            z-index: 1001;
        }

        .mobile-header h2 {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .mobile-header {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1000;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding-top: 80px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .page-header h1 {
                font-size: 1.4rem;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 80px 1rem 1rem;
            }

            .card {
                padding: 1rem;
                border-radius: 0.75rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0.75rem;
            }

            .form-row .btn {
                width: 100%;
                justify-content: center;
            }

            th, td {
                padding: 0.6rem 0.5rem;
                font-size: 0.85rem;
            }

            .btn-sm {
                padding: 0.35rem 0.6rem;
                font-size: 0.75rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.2rem 0.5rem;
            }

            .toast {
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.2rem;
            }

            .actions {
                flex-direction: column;
                gap: 0.25rem;
            }

            table {
                min-width: 500px;
            }
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <h2><i class="fa-solid fa-gauge"></i> Kiosk Admin</h2>
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-gauge"></i> Kiosk Admin</h2>
                <span>Dashboard Management</span>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">Management</div>
                <a href="/admin/prayer" class="nav-item {{ request()->is('admin/prayer') ? 'active' : '' }}">
                    <i class="fa-solid fa-mosque"></i>
                    Prayer Times
                </a>
                <a href="/admin/weather" class="nav-item {{ request()->is('admin/weather') ? 'active' : '' }}">
                    <i class="fa-solid fa-cloud-sun"></i>
                    Weather
                </a>
                <a href="/admin/holidays" class="nav-item {{ request()->is('admin/holidays') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    Holidays
                </a>
                <a href="/admin/backgrounds" class="nav-item {{ request()->is('admin/backgrounds') ? 'active' : '' }}">
                    <i class="fa-solid fa-images"></i>
                    Backgrounds
                </a>
                <a href="/admin/users" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    User management
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="/">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: rgba(255,255,255,0.7); cursor: pointer; font-size: 0.9rem; padding: 0; display: flex; align-items: center; gap: 0.5rem; font-family: inherit;">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>@yield('page-icon') @yield('page-title')</h1>
                <div class="header-actions">
                    @yield('header-actions')
                </div>
            </div>

            @yield('content')
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} show`;

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        // Close sidebar when clicking a nav item on mobile
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
