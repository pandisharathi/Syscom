<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css"/>
    <style>
        :root { --sidebar-w: 260px; --brand: #4f46e5; }
        body { background: #f4f6fb; min-height: 100vh; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: var(--sidebar-w);
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,0,0,.06);
            color: #1f2937; z-index: 1035;
            box-shadow: 0 0 40px rgba(0,0,0,.06);
            display: flex; flex-direction: column;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            transition: transform .28s ease;
        }
        .sidebar-header {
            flex-shrink: 0;
            padding: 1.25rem 0 .5rem;
        }
        .sidebar-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 1.25rem;
            -webkit-overflow-scrolling: touch;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar .brand { padding: 0 1.25rem .65rem; font-weight: 700; letter-spacing: .02em; color: #4f46e5; font-size: 1.1rem; }
        .sidebar .nav-link {
            color: #4b5563; border-radius: .5rem; margin: .15rem .75rem; padding: .55rem .85rem;
            display: flex; align-items: center; gap: .65rem; transition: background .15s, color .15s;
        }
        .sidebar .nav-link:hover { background: #f3f4f6; color: #4f46e5; }
        .sidebar .nav-link.active { background: #eef2ff; color: #4f46e5; font-weight: 500; }
        .sidebar .small-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; font-weight: 600; padding: .75rem 1.25rem .35rem; }
        .main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            transition: margin-left .28s ease;
        }
        /* Desktop: sidebar visible unless collapsed */
        @media (min-width: 992px) {
            body.sidebar-collapsed .sidebar { transform: translateX(-100%); }
            body.sidebar-collapsed .main-wrap { margin-left: 0; }
            #sidebarBackdrop { display: none !important; }
        }
        /* Mobile: sidebar hidden unless open */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            body.sidebar-open .sidebar { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .sidebar-backdrop {
                display: none;
                position: fixed; inset: 0;
                background: rgba(15,23,42,.45);
                z-index: 1030;
                opacity: 0;
                transition: opacity .25s ease;
            }
            body.sidebar-open .sidebar-backdrop {
                display: block;
                opacity: 1;
            }
        }
        .topbar {
            background: #fff; border-bottom: 1px solid #e5e7eb; padding: .85rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020;
        }
        #sidebarToggle {
            width: 2.5rem; height: 2.5rem;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: .65rem;
            border: 1px solid #e5e7eb;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15,23,42,.06);
        }
        #sidebarToggle:hover { background: #f9fafb; }
        .content { padding: 1.5rem; }
        .card-soft { border: none; border-radius: 1rem; box-shadow: 0 10px 30px rgba(15,23,42,.06); }
        .stat-card { border-radius: 1rem; border: none; color: #fff; overflow: hidden; position: relative; }
        .stat-card .icon-bg { position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: .15; }
        .form-label { font-weight: 500; margin-bottom: .35rem; color: #374151; }
        .table th { font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; color: #6b7280; }
        .sidebar .nav-link .submenu-icon { margin-left: auto; transition: transform .2s; color: #9ca3af; }
        .sidebar .nav-link .submenu-icon.open { transform: rotate(90deg); }
        .sidebar-header .text-white-50 { color: #6b7280 !important; }
        .table-responsive { border-radius: .5rem; }
        .bg-soft-primary { background-color: #eef2ff; }
        @media (max-width: 768px) {
            .content { padding: 1rem; }
            .card-soft { border-radius: .75rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" aria-hidden="true"></div>

<aside class="sidebar" id="sidebar" aria-label="Sidebar navigation">
    <div class="sidebar-header">
        <div class="brand"><i class="fa-solid fa-layer-group me-2"></i>{{ config('app.name') }}</div>
        @auth
            <div class="px-3 pb-2 small text-white-50">{{ auth()->user()->name }}</div>
        @endauth
    </div>
    @auth
        <div class="sidebar-scroll">
            <nav class="nav flex-column">
                @foreach($sidebarMenus ?? [] as $menu)
                    @if(!empty($menu['children']))
                        @php
                            $hasActive = collect($menu['children'])->contains(fn($c) => request()->url() === url($c['route']));
                        @endphp
                        <div class="small-label d-flex align-items-center justify-content-between" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#submenu-{{ $menu['id'] }}" aria-expanded="{{ $hasActive ? 'true' : 'false' }}">
                            <span>{{ $menu['name'] }}</span>
                            <i class="fa-solid fa-chevron-down submenu-icon {{ $hasActive ? 'open' : '' }}" style="font-size:.6rem;opacity:.5;transition:transform .2s"></i>
                        </div>
                        <div class="collapse {{ $hasActive ? 'show' : '' }}" id="submenu-{{ $menu['id'] }}">
                            @foreach($menu['children'] as $child)
                                <a class="nav-link {{ request()->url() === url($child['route']) ? 'active' : '' }}" href="{{ $child['route'] }}">
                                    <i class="fa-solid {{ $child['icon'] ?? 'fa-circle' }}"></i> {{ $child['name'] }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a class="nav-link {{ request()->url() === url($menu['route']) ? 'active' : '' }}" href="{{ $menu['route'] }}">
                            <i class="fa-solid {{ $menu['icon'] ?? 'fa-circle' }}"></i> {{ $menu['name'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
    @endauth
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="sidebarToggle" aria-controls="sidebar" aria-expanded="true" title="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="fw-semibold text-secondary">@yield('page_title')</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            @auth
                <span class="text-muted small">{{ auth()->user()->email }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Logout</button>
                </form>
            @endauth
        </div>
    </header>

    <main class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    (function () {
        const body = document.body;
        const mqDesktop = window.matchMedia('(min-width: 992px)');
        const toggleBtn = document.getElementById('sidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');

        function isDesktop() {
            return mqDesktop.matches;
        }

        function applyAria() {
            if (!toggleBtn) return;
            if (isDesktop()) {
                const collapsed = body.classList.contains('sidebar-collapsed');
                toggleBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            } else {
                const open = body.classList.contains('sidebar-open');
                toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
        }

        function closeMobileSidebar() {
            body.classList.remove('sidebar-open');
            applyAria();
        }

        function restoreDesktopCollapsed() {
            if (!isDesktop()) return;
            try {
                if (localStorage.getItem('adminSidebarCollapsed') === '1') {
                    body.classList.add('sidebar-collapsed');
                } else {
                    body.classList.remove('sidebar-collapsed');
                }
            } catch (e) {}
            applyAria();
        }

        toggleBtn?.addEventListener('click', function () {
            if (isDesktop()) {
                body.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('adminSidebarCollapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0');
                } catch (e) {}
            } else {
                body.classList.toggle('sidebar-open');
            }
            applyAria();
        });

        backdrop?.addEventListener('click', closeMobileSidebar);

        mqDesktop.addEventListener('change', function () {
            body.classList.remove('sidebar-open');
            restoreDesktopCollapsed();
        });

        document.getElementById('sidebar')?.addEventListener('click', function (e) {
            if (isDesktop()) return;
            const link = e.target.closest('a.nav-link');
            if (link) closeMobileSidebar();
        });

        restoreDesktopCollapsed();
        if (!isDesktop()) applyAria();

        document.querySelectorAll('.small-label[data-bs-toggle="collapse"]').forEach(function(label){
            label.addEventListener('click', function(){
                const icon = this.querySelector('.submenu-icon');
                if(icon) icon.classList.toggle('open');
            });
            const targetId = label.getAttribute('data-bs-target');
            if(targetId){
                const target = document.querySelector(targetId);
                if(target && !target.classList.contains('show')){
                    const icon = label.querySelector('.submenu-icon');
                    if(icon) icon.classList.remove('open');
                }
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
