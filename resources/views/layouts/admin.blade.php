<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADM GLOBAL | @yield('title', 'Admin Panel')</title>
    <link rel="icon" href="https://edo.adm.uz/favicon.png" type="image/png" />
    <link rel="alternate icon" href="https://edo.adm.uz/favicon.png" type="image/png" sizes="16x16" />
    <link rel="apple-touch-icon" href="https://edo.adm.uz/favicon.png" sizes="180x180" />
    <link rel="mask-icon" href="https://edo.adm.uz/favicon.png" color="#FFFFFF" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .sidebar-link{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 12px;
            border-radius:10px;
            font-size:14px;
            font-weight:500;
            color:#475569;
            transition:0.2s;
        }

        .sidebar-link:hover{
            background:#f1f5f9;
            color:#0f172a;
        }

        .sidebar-link.active{
            background:#eef2ff;
            color:#4f46e5;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

<!-- ─── Mobile Sidebar Overlay ─── -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity"></div>

<div class="flex min-h-screen bg-slate-50">

    <!-- ─── Sidebar ─── -->
    <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-full transition-transform -translate-x-full lg:translate-x-0 bg-white border-r border-slate-200 flex flex-col py-6 px-4 shadow-sm lg:shadow-none">
        <!-- Brand -->
        <div class="flex items-center justify-between mb-10 px-2">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl flex-shrink-0">
                    <img src="https://edo.adm.uz/assets/logo3.18831604.png" alt="ADM GLOBAL">
                </div>
                <div>
                    <div class="text-base font-bold text-slate-900 tracking-tight">ADM GLOBAL</div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">Audit Panel</div>
                </div>
            </div>
            <!-- Close btn mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex flex-col gap-1.5 flex-1 overflow-y-auto">
            <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('sidebar.main') }}</p>
            <a href="{{ route('web.dashboard') }}" class="sidebar-link {{ request()->routeIs('web.dashboard') ? 'active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ __('sidebar.audit_logs') }}
            </a>
            <a href="{{ route('web.charts') }}" class="sidebar-link {{ request()->routeIs('web.charts') ? 'active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Statistika
            </a>
            @php
                $canViewUsers = auth()->user()->hasRole('superadmin') || auth()->user()->hasPermission('admin-users-index');
                $canViewRoles = auth()->user()->hasRole('superadmin') || auth()->user()->hasPermission('admin-roles-index');
                $canViewPerms = auth()->user()->hasRole('superadmin') || auth()->user()->hasPermission('admin-permissions-index');
            @endphp

            @if($canViewUsers || $canViewRoles || $canViewPerms)
            <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-6">{{ __('sidebar.management') }}</p>

            @if($canViewUsers)
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                {{ __('sidebar.users') }}
            </a>
            @endif

            @if($canViewRoles)
            <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                {{ __('sidebar.roles_permissions') }}
            </a>
            @endif

            @if($canViewPerms)
            <a href="{{ route('admin.permissions.index') }}" class="sidebar-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                {{ __('sidebar.permissions_list') }}
            </a>
            @endif
            @endif
        </nav>

        <!-- User profile -->
        <div class="mt-auto pt-6 border-t border-slate-100">
            <div class="flex items-center gap-3 px-2">
                <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-sm font-bold text-indigo-600 border border-slate-200">
                    {{ strtoupper(substr(auth()->user()->firstname ?? auth()->user()->username, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->firstname ?? auth()->user()->username }}</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
                        {{ auth()->user()->hasRole('superadmin') ? 'Super Admin' : (auth()->user()->roles()->first()->name ?? 'Administrator') }}
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg hover:bg-rose-50" title="{{ __('common.logout') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ─── Main Content ─── -->
    <div class="flex-1 flex flex-col min-h-screen lg:ml-64 transition-all">
        <!-- Top bar -->
        <header class="bg-white border-b border-slate-200 px-4 lg:px-8 py-3 lg:py-4 flex items-center justify-between sticky top-0 z-20 shadow-sm">
            <div class="flex items-center gap-4 lg:gap-6">
                <!-- Mobile Toggle -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- Dashboard label -->
                <div>
                    @if(!auth()->user()->hasRole('superadmin'))
                    <div class="flex items-center gap-2">
                        <span class="text-base lg:text-lg font-bold text-slate-900 tracking-tight">Audit Panel</span>
                    </div>
                    @else
                    <h1 class="text-[10px] lg:text-sm font-bold text-slate-500 uppercase tracking-widest truncate max-w-[150px] lg:max-w-none">{{ __('sidebar.admin_panel') }}</h1>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 lg:gap-4">
                <!-- Language Switcher -->
                <div class="flex items-center bg-slate-100 p-0.5 lg:p-1 rounded-xl border border-slate-200">
                    @foreach(['uz' => 'UZ', 'ru' => 'RU', 'en' => 'EN'] as $code => $label)
                        <a href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['locale' => $code])) }}"
                           class="px-2 lg:px-3 py-1 lg:py-1.5 text-[10px] lg:text-[11px] font-bold rounded-lg transition-all {{ app()->getLocale() === $code ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 px-4 lg:px-10 2xl:px-20 py-8">
            <div class="w-full">
                @if(session('success'))
                <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 lg:px-5 lg:py-4 text-sm text-emerald-800 flex items-center gap-3 shadow-sm">
                    <div class="h-8 w-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-bold">{{ __('common.success') }}</p>
                        <p class="opacity-90">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 rounded-xl bg-rose-50 border border-rose-100 px-4 py-3 lg:px-5 lg:py-4 text-sm text-rose-800 flex items-center gap-3 shadow-sm">
                    <div class="h-8 w-8 rounded-lg bg-rose-500 flex items-center justify-center text-white flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="font-bold">{{ __('common.error') }}</p>
                        <p class="opacity-90">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('modals')

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
        }
    }
</script>

@stack('scripts')
</body>
</html>
