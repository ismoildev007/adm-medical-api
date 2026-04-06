@extends('layouts.admin')
@section('title', __('users.title'))

@section('content')
<div class="max-w-full mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-4xl font-black text-indigo-900 tracking-tight">{{ __('users.title') }}</h1>
            <p class="text-[13px] font-medium text-slate-400 mt-2 tracking-wide">{{ __('users.subtitle') }}</p>
        </div>
        @can('admin-users-store')
        <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-7 py-4 text-sm font-black text-white hover:bg-indigo-700 transition-all active:scale-95 shadow-xl shadow-indigo-600/20">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            {{ __('users.add_new') }}
        </button>
        @endcan
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 p-6 lg:p-8">
        <form action="{{ route('admin.users.index') }}" method="GET" id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
            <!-- Search -->
            <div class="space-y-2 lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">QIDIRUV</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}" oninput="debouncedSearch()" placeholder="Ism yoki username ..." autocomplete="off"
                        class="w-full rounded-2xl bg-slate-50 border-none pl-12 pr-10 py-3.5 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">

                    @if(request('search'))
                        <button type="button" onclick="clearSearch()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-rose-500 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Role Filter (Searchable) -->
            <div class="space-y-2 relative" id="role-filter-container">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">ROL BO'YICHA</label>
                <div class="relative">
                    <div id="role-select-trigger" class="w-full rounded-2xl bg-slate-50 border-none px-4 py-3.5 text-sm font-bold text-slate-700 cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all">
                        <span id="role-selected-text" class="truncate">{{ request('role') ?: 'Barcha rollar' }}</span>
                        <div class="flex items-center gap-2">
                            @if(request('role'))
                                <button type="button" onclick="clearRole(event)" class="text-slate-300 hover:text-rose-500 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                            <svg class="h-4 w-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <!-- Dropdown Content -->
                    <div id="role-dropdown" class="absolute left-0 right-0 mt-3 bg-white rounded-[1.5rem] shadow-2xl border border-slate-100 p-3 z-[60] hidden overflow-hidden scale-95 opacity-0 transition-all duration-200 origin-top">
                        <div class="relative mb-3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" id="role-search-input" placeholder="Rolni qidiring..." autocomplete="off"
                                class="w-full rounded-xl bg-slate-50 border-none pl-10 pr-4 py-2.5 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1" id="role-options-list">
                            <div class="role-option px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt {{ !request('role') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                                onclick="selectRole('')">
                                Barcha rollar
                                @if(!request('role'))
                                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            @foreach($roles as $role)
                                <div class="role-option px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt {{ request('role') == $role->name ? 'bg-indigo-50 text-indigo-700' : '' }}"
                                    onclick="selectRole('{{ $role->name }}')">
                                    {{ $role->name }}
                                    @if(request('role') == $role->name)
                                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <input type="hidden" name="role" id="hidden-role-input" value="{{ request('role') }}">
            </div>

            <!-- Permission Filter (Searchable) -->
            <div class="space-y-2 relative" id="permission-filter-container">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">HUQUQ BO'YICHA</label>
                <div class="relative">
                    <div id="permission-select-trigger" class="w-full rounded-2xl bg-slate-50 border-none px-4 py-3.5 text-sm font-bold text-slate-700 cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all">
                        <span id="permission-selected-text" class="truncate">{{ request('permission') ?: 'Hamma huquqlar' }}</span>
                        <div class="flex items-center gap-2">
                            @if(request('permission'))
                                <button type="button" onclick="clearPermission(event)" class="text-slate-300 hover:text-rose-500 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                            <svg class="h-4 w-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <!-- Dropdown Content -->
                    <div id="permission-dropdown" class="absolute left-0 right-0 mt-3 bg-white rounded-[1.5rem] shadow-2xl border border-slate-100 p-3 z-[60] hidden overflow-hidden scale-95 opacity-0 transition-all duration-200 origin-top">
                        <div class="relative mb-3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" id="permission-search-input" placeholder="Huquqni qidiring..." autocomplete="off"
                                class="w-full rounded-xl bg-slate-50 border-none pl-10 pr-4 py-2.5 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1" id="permission-options-list">
                            <div class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt {{ !request('permission') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                                onclick="selectPermission('')">
                                Barcha huquqlar
                                @if(!request('permission'))
                                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            @foreach($permissions as $permission)
                                <div class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt {{ request('permission') == $permission->name ? 'bg-indigo-50 text-indigo-700' : '' }}"
                                    onclick="selectPermission('{{ $permission->name }}')">
                                    {{ $permission->name }}
                                    @if(request('permission') == $permission->name)
                                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <input type="hidden" name="permission" id="hidden-permission-input" value="{{ request('permission') }}">
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 w-16">№</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('users.table_user') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('users.table_username') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('users.table_roles') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Loyihalar</th>
                        <th class="px-8 py-5 border-b border-slate-100"></th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($users as $i => $user)
                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-8 py-6 text-sm font-bold text-slate-300 border-b border-slate-50">{{ $i + 1 }}</td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-[1.25rem] bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[15px] font-black text-indigo-600 flex-shrink-0 shadow-sm shadow-indigo-100/50">
                                    {{ strtoupper(substr($user->firstname ?? $user->username, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[15px] font-black text-slate-800 leading-tight truncate">{{ $user->firstname }} {{ $user->lastname }}</div>
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID: {{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-slate-600 font-mono text-[11px] font-black border border-slate-100">
                                {{ $user->username }}
                            </span>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="flex flex-wrap gap-2">
                                @forelse($user->roles as $role)
                                <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest border
                                    {{ $role->name === 'superadmin' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-[11px] text-slate-300 font-bold uppercase tracking-wider italic">{{ __('users.not_assigned') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="flex flex-wrap gap-2 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                @forelse($user->project_permission ?? [] as $proj)
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">{{ $proj }}</span>
                                @empty
                                    <span class="italic text-slate-200">Hammasi (Superadmin bo'lsa)</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                <!-- Change role & user edit -->
                                @can('admin-users-role')
                                <button onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->firstname) }}', '{{ addslashes($user->lastname) }}', '{{ addslashes($user->username) }}', {{ $user->roles->pluck('name') }}, {{ json_encode($user->project_permission ?? []) }})"
                                    title="Tahrirlash"
                                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-white text-slate-400 hover:text-indigo-600 border border-slate-200 hover:border-indigo-100 hover:bg-indigo-50 transition-all active:scale-95 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </button>
                                @endcan

                                <!-- Delete -->
                                @can('admin-users-destroy')
                                    @if($user->username !== 'superadmin')
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('common.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="{{ __('common.delete') }}"
                                            class="h-10 w-10 flex items-center justify-center rounded-xl bg-white text-slate-400 hover:text-rose-600 border border-slate-200 hover:border-rose-100 hover:bg-rose-50 transition-all active:scale-95 shadow-sm">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-8 py-20 text-center text-slate-300 font-bold uppercase tracking-[0.2em] italic">{{ __('common.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('modals')
{{-- ─── Add User Modal ─── --}}
<div id="add-user-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 lg:p-8 transform transition-all border border-slate-200 m-4 sm:m-0">
            <div class="flex items-center justify-between mb-8">
                <div class="min-w-0">
                    <h2 class="text-xl lg:text-2xl font-bold text-slate-900 tracking-tight">{{ __('users.modal_title') }}</h2>
                    <p class="text-[11px] lg:text-sm font-medium text-slate-500 mt-1 truncate">{{ __('users.modal_subtitle') }}</p>
                </div>
                <button onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100 transition-colors flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 lg:space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.firstname') }} *</label>
                        <input type="text" name="firstname" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.lastname') }} *</label>
                        <input type="text" name="lastname" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.table_username') }} *</label>
                    <input type="text" name="username" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.password') }} *</label>
                    <input type="password" name="password" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                </div>
                {{-- Roles selection --}}
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.table_roles') }} *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-1 custom-scrollbar">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-700 uppercase tracking-wider">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Project Permissions --}}
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">Loyiha ruxsatlari</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-1 custom-scrollbar">
                        @foreach($allProjects as $proj)
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <input type="checkbox" name="projects[]" value="{{ $proj }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-700 uppercase tracking-wider">{{ $proj }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 sm:gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')"
                        class="flex-1 rounded-2xl bg-white border border-slate-200 py-3 sm:py-4 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-[0.98]">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 rounded-2xl bg-indigo-600 py-3 sm:py-4 text-sm font-bold text-white hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 active:scale-[0.98]">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Edit User Modal ─── --}}
<div id="role-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeRoleModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 lg:p-8 transform transition-all border border-slate-200 m-4 sm:m-0">
            <h2 class="text-xl font-bold text-slate-900 tracking-tight mb-6">Foydalanuvchini Tahrirlash</h2>
            <form method="POST" id="edit-user-form" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.firstname') }} *</label>
                        <input type="text" name="firstname" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.lastname') }} *</label>
                        <input type="text" name="lastname" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.table_username') }} *</label>
                    <input type="text" name="username" required class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">Yangi Parol (ixtiyoriy)</label>
                    <input type="password" name="password" placeholder="O'zgartirish uchun yozing..." class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('users.table_roles') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-1 custom-scrollbar">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" data-role-name="{{ $role->name }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-700 uppercase tracking-wider">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">Loyiha ruxsatlari</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-1 custom-scrollbar">
                        @foreach($allProjects as $proj)
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <input type="checkbox" name="projects[]" value="{{ $proj }}" data-project-name="{{ $proj }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-700 uppercase tracking-wider">{{ $proj }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeRoleModal()" class="flex-1 rounded-2xl bg-white border border-slate-200 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-[0.98]">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 rounded-2xl bg-indigo-600 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98]">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function initSearchableDropdown(config) {
        const { triggerId, dropdownId, searchInputId, optionsClass, onSelect } = config;
        const trigger = document.getElementById(triggerId);
        const dropdown = document.getElementById(dropdownId);
        const searchInput = document.getElementById(searchInputId);

        if (!trigger || !dropdown) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !dropdown.classList.contains('hidden');
            if (isOpen) {
                closeDropdown(dropdown);
            } else {
                document.querySelectorAll('[id$="-dropdown"]').forEach(d => {
                    if (d !== dropdown) {
                        d.classList.add('hidden', 'scale-95', 'opacity-0');
                        d.classList.remove('scale-100', 'opacity-100');
                    }
                });
                openDropdown(dropdown, searchInput);
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll(`.${optionsClass}`).forEach(opt => {
                    const txt = opt.textContent.toLowerCase();
                    opt.style.display = txt.includes(query) ? 'flex' : 'none';
                });
            });
        }

        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                closeDropdown(dropdown);
            }
        });
    }

    function openDropdown(dropdown, searchInput) {
        dropdown.classList.remove('hidden');
        setTimeout(() => {
            dropdown.classList.remove('scale-95', 'opacity-0');
            dropdown.classList.add('scale-100', 'opacity-100');
            if (searchInput) searchInput.focus();
        }, 10);
    }

    function closeDropdown(dropdown) {
        dropdown.classList.add('scale-95', 'opacity-0');
        dropdown.classList.remove('scale-100', 'opacity-100');
        setTimeout(() => dropdown.classList.add('hidden'), 200);
    }

    initSearchableDropdown({
        triggerId: 'role-select-trigger',
        dropdownId: 'role-dropdown',
        searchInputId: 'role-search-input',
        optionsClass: 'role-option'
    });

    initSearchableDropdown({
        triggerId: 'permission-select-trigger',
        dropdownId: 'permission-dropdown',
        searchInputId: 'permission-search-input',
        optionsClass: 'permission-option'
    });

    const filterForm = document.getElementById('filter-form');
    const searchInputMain = document.getElementById('search-input');
    const hiddenPermissionInput = document.getElementById('hidden-permission-input');
    const hiddenRoleInput = document.getElementById('hidden-role-input');

    function selectPermission(val) {
        hiddenPermissionInput.value = val;
        filterForm.submit();
    }

    function clearPermission(e) {
        e.stopPropagation();
        selectPermission('');
    }

    function selectRole(val) {
        hiddenRoleInput.value = val;
        filterForm.submit();
    }

    function clearRole(e) {
        e.stopPropagation();
        selectRole('');
    }

    let searchTimeout;
    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 600);
    }

    function clearSearch() {
        searchInputMain.value = '';
        filterForm.submit();
    }

    function openEditModal(userId, userFirstname, userLastname, userUsername, currentRoles, currentProjects) {
        const formMod = document.getElementById('edit-user-form');
        formMod.action = `/${document.documentElement.lang}/admin/users/${userId}/role`;

        const fnameInput = formMod.querySelector('input[name="firstname"]');
        const lnameInput = formMod.querySelector('input[name="lastname"]');
        const unameInput = formMod.querySelector('input[name="username"]');
        
        fnameInput.value = userFirstname;
        lnameInput.value = userLastname;
        unameInput.value = userUsername;
        formMod.querySelector('input[name="password"]').value = '';

        const checkboxes = formMod.querySelectorAll('input[name="roles[]"]');
        checkboxes.forEach(cb => {
            cb.checked = currentRoles.includes(cb.getAttribute('data-role-name'));
        });

        const projectCheckboxes = formMod.querySelectorAll('input[name="projects[]"]');
        projectCheckboxes.forEach(cb => {
            cb.checked = currentProjects.includes(cb.getAttribute('data-project-name'));
        });
        
        // Superadmin protections
        const isSuperadmin = userUsername === 'superadmin';
        fnameInput.readOnly = isSuperadmin;
        lnameInput.readOnly = isSuperadmin;
        unameInput.readOnly = isSuperadmin;
        
        checkboxes.forEach(cb => {
            cb.disabled = isSuperadmin;
        });
        projectCheckboxes.forEach(cb => {
            cb.disabled = isSuperadmin;
        });

        document.getElementById('role-modal').classList.remove('hidden');
    }

    function closeRoleModal() {
        document.getElementById('role-modal').classList.add('hidden');
    }

    window.addEventListener('load', () => {
        if (new URLSearchParams(window.location.search).has('search')) {
            const val = searchInputMain.value;
            searchInputMain.focus();
            searchInputMain.setSelectionRange(val.length, val.length);
        }
    });
</script>
@endpush
@endsection
