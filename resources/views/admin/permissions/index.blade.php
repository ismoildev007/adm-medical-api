@extends('layouts.admin')
@section('title', __('permissions.title'))

@section('content')
<div class="w-full mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-4xl font-black text-indigo-900 tracking-tight">{{ __('permissions.title') }}</h1>
            <p class="text-[13px] font-medium text-slate-400 mt-2 tracking-wide">{{ __('permissions.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-3">

            <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-2xl text-indigo-700 font-bold text-[13px]">
        <span id="total-count" class="h-6 w-6 flex items-center justify-center bg-indigo-600 text-white rounded-lg text-[10px] font-black">
            {{ $permissions->total() }}
        </span>
                {{ __('permissions.total_count') }}
            </div>
            <button onclick="fetchPermissions()" id="refresh-btn" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50 transition-all active:scale-95">
                <svg id="refresh-icon" class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ __('audits.refresh') }}
            </button>

        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 p-6 lg:p-8">
        <div class="flex flex-wrap lg:flex-nowrap items-center gap-6">
            <div class="relative flex-1 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="perm-search" value="{{ request('search') }}" oninput="debouncedSearch()" placeholder="{{ __('permissions.search_placeholder') }}"
                    class="w-full rounded-2xl border-none bg-slate-50 pl-12 pr-10 py-4 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">
                @if(request('search'))
                    <button onclick="clearSearch()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-rose-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>

        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 w-16">№</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('permissions.table_name') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('permissions.table_module') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('permissions.table_status') }}</th>
                    </tr>
                </thead>
            <tbody id="permissions-tbody" class="bg-white">
                @foreach($permissions as $i => $permission)
                <tr class="hover:bg-indigo-50/30 transition-colors group">
                    <td class="px-8 py-6 text-sm font-bold text-slate-300 border-b border-slate-50">{{ $permissions->firstItem() + $i }}</td>
                    <td class="px-8 py-6 border-b border-slate-50">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-600 uppercase shadow-sm shadow-indigo-100/50">
                                {{ substr($permission->name, 0, 2) }}
                            </div>
                            <span class="text-[13px] font-black text-slate-700 tracking-tight font-mono">{{ $permission->name }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 border-b border-slate-50">
                        @php
                            $parts = explode('-', $permission->name);
                            $module = $parts[0] ?? 'general';
                        @endphp
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-500 border border-slate-200">
                            {{ $module }}
                        </span>
                    </td>
                    <td class="px-8 py-6 border-b border-slate-50">
                        <span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.1em] text-emerald-600 px-3 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ __('permissions.status_active') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Custom Pagination Component -->
    @include('components.pagination')
</div>

@push('scripts')
<script>
    let currentPage = {{ $permissions->currentPage() }};
    let searchTimeout;

    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchPermissions();
        }, 500);
    }

    function clearSearch() {
        document.getElementById('perm-search').value = '';
        currentPage = 1;
        fetchPermissions();
    }

    function goToPage(page) {
        currentPage = page;
        fetchPermissions();
    }

    function fetchPermissions() {
        const search = document.getElementById('perm-search').value;
        const tbody = document.getElementById('permissions-tbody');

        tbody.style.opacity = '0.5';

        fetch(`/${document.documentElement.lang}/admin/permissions?page=${currentPage}&search=${search}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            renderPermissions(data.data, data.from);
            renderPagination(data, 'goToPage');
            document.getElementById('total-count').textContent = data.total;
            tbody.style.opacity = '1';
        });
    }

    function renderPermissions(permissions, from) {
        const tbody = document.getElementById('permissions-tbody');
        if (permissions.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-20 text-center text-slate-400 font-medium italic">${"{{ __('common.no_data') }}"}</td></tr>`;
            return;
        }

        tbody.innerHTML = permissions.map((p, i) => {
            const module = p.name.split('-')[0] || 'general';
            return `
                <tr class="hover:bg-indigo-50/30 transition-colors group">
                    <td class="px-8 py-6 text-sm font-bold text-slate-300 border-b border-slate-50">${from + i}</td>
                    <td class="px-8 py-6 border-b border-slate-50">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-600 uppercase shadow-sm shadow-indigo-100/50">
                                ${p.name.substring(0, 2)}
                            </div>
                            <span class="text-[13px] font-black text-slate-700 tracking-tight font-mono">${p.name}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 border-b border-slate-50">
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-500 border border-slate-200">
                            ${module}
                        </span>
                    </td>
                    <td class="px-8 py-6 border-b border-slate-50">
                        <span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.1em] text-emerald-600 px-3 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            ${"{{ __('permissions.status_active') }}"}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    window.addEventListener('DOMContentLoaded', () => {
        renderPagination(@json($permissions), 'goToPage');
    });
</script>
@endpush
@endsection
