@extends('layouts.admin')
@section('title', __('roles.title'))

@section('content')
<div class="w-full mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-4xl font-black text-indigo-900 tracking-tight">{{ __('roles.title') }}</h1>
            <p class="text-[13px] font-medium text-slate-400 mt-2 tracking-wide">{{ __('roles.subtitle') }}</p>
        </div>
        @can('admin-roles-store')
        <button onclick="openAddRoleModal()"
            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-7 py-4 text-sm font-black text-white hover:bg-indigo-700 transition-all active:scale-95 shadow-xl shadow-indigo-600/20">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            {{ __('roles.add_new') }}
        </button>
        @endcan
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('admin.roles.index') }}" method="GET" id="filter-form">
        <input type="hidden" name="permission" id="permission-input" value="{{ request('permission') }}">
        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 p-6 lg:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 items-center gap-6">
                <!-- Text Search -->
                <div class="relative group col-span-1 lg:col-span-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" id="role-search" value="{{ request('search') }}" placeholder="{{ __('roles.search_placeholder') }}"
                        class="w-full rounded-2xl border-none bg-slate-50 pl-12 pr-4 py-4 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">
                </div>

                <!-- Permission Filter (Searchable) -->
                <div class="relative group z-20" id="permission-filter-container">
                    <div id="permission-select-trigger" class="w-full rounded-2xl bg-slate-50 border-none px-4 py-4 text-sm font-bold text-slate-700 cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all">
                        <span id="permission-selected-text" class="truncate">{{ request('permission') ?: 'Hamma huquqlar' }}</span>
                        <div class="flex items-center gap-2">
                            @if(request('permission'))
                                <button type="button" onclick="clearPermission(event)" class="text-slate-300 hover:text-rose-500 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <!-- Dropdown Content -->
                    <div id="permission-dropdown" class="absolute left-0 right-0 mt-3 bg-white rounded-[1.5rem] shadow-2xl border border-slate-100 p-3 z-50 hidden overflow-hidden scale-95 opacity-0 transition-all duration-200 origin-top">
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
                                <div class="permission-option px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt {{ request('permission') == $permission->name ? 'bg-indigo-50 text-indigo-700' : '' }}"
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

                <!-- Type Filter Buttons -->
                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-[1.25rem] border border-slate-100 justify-self-end">
                    <button type="button" onclick="filterTable('all')" id="btn-all" class="filter-btn active px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        {{ __('roles.filter_all') }}
                    </button>
                    <button type="button" onclick="filterTable('system')" id="btn-system" class="filter-btn px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        {{ __('roles.filter_system') }}
                    </button>
                    <button type="button" onclick="filterTable('user')" id="btn-user" class="filter-btn px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        {{ __('roles.filter_user') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Table Card -->
    <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 w-16">№</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('roles.table_name') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('roles.table_type') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('roles.table_permissions') }}</th>
                        <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('roles.table_description') }}</th>
                        <th class="px-8 py-5 border-b border-slate-100"></th>
                    </tr>
                </thead>
                <tbody class="bg-white" id="roles-tbody">
                    @forelse($roles as $i => $role)
                    <tr class="hover:bg-indigo-50/30 transition-colors group role-row" data-type="{{ $role->type === 0 ? 'system' : 'user' }}">
                        <td class="px-8 py-6 text-sm font-bold text-slate-300 border-b border-slate-50">{{ $i + 1 }}</td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="text-[15px] font-black text-slate-800 leading-tight">{{ $role->name }}</div>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest
                                {{ $role->type === 0 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                 {{ $role->type === 0 ? __('roles.filter_system') : __('roles.filter_user') }}
                            </span>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 text-[11px] font-black border border-indigo-100 shadow-sm shadow-indigo-100/30">
                                    {{ $role->permissions_count }} {{ __('roles.permissions_count') }}
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                             <p class="text-[12px] font-bold text-slate-400 italic line-clamp-1 max-w-xs">{{ $role->description ?? __('roles.no_description') }}</p>
                        </td>
                        <td class="px-8 py-6 border-b border-slate-50">
                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all translate-x-3 group-hover:translate-x-0">
                                @if($role->name !== 'superadmin')
                                    <!-- Sync Permissions -->
                                    @can('admin-roles-sync')
                                    <button onclick="openSyncModal('{{ $role->name }}')"
                                        title="{{ __('roles.sync_perms') }}"
                                        class="h-10 w-10 flex items-center justify-center rounded-xl bg-white text-slate-400 hover:text-indigo-600 border border-slate-200 hover:border-indigo-100 hover:bg-indigo-50 transition-all active:scale-95 shadow-sm">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    </button>
                                    @endcan
    
                                    <!-- Edit -->
                                    @can('admin-roles-update')
                                    <button onclick="editRole('{{ $role->name }}', '{{ $role->description }}', {{ $role->type }})"
                                        title="{{ __('common.edit') }}"
                                        class="h-10 w-10 flex items-center justify-center rounded-xl bg-white text-slate-400 hover:text-sky-600 border border-slate-200 hover:border-sky-100 hover:bg-sky-50 transition-all active:scale-95 shadow-sm">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @endcan
    
                                    <!-- Delete -->
                                    @if($role->type !== 0)
                                    @can('admin-roles-destroy')
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('{{ __('common.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="{{ __('common.delete') }}"
                                            class="h-10 w-10 flex items-center justify-center rounded-xl bg-white text-slate-400 hover:text-rose-600 border border-slate-200 hover:border-rose-100 hover:bg-rose-50 transition-all active:scale-95 shadow-sm">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endcan
                                    @endif
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 italic uppercase tracking-widest">{{ __('roles.protected', ['default' => 'HIMOYA QILINGAN']) }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-8 py-20 text-center text-slate-300 font-bold uppercase tracking-[0.2em] italic">{{ __('common.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('modals')
{{-- ─── Sync Permissions Modal (Dual Pane) ─── --}}
<div id="sync-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('sync-modal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-5xl p-0 transform transition-all border border-slate-200 overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-8 border-b border-slate-100">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight" id="sync-role-title">Role sync permission:</h2>
                </div>
                <button onclick="document.getElementById('sync-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100 transition-colors border border-slate-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-[1fr,60px,1fr] gap-6">
                    <!-- Left Pane: Available Permissions -->
                    <div class="flex flex-col border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                        <!-- Search Header -->
                        <div class="p-3 bg-slate-50 border-b border-slate-200">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" id="all-perms-search" onkeyup="filterPane('all-perms-list', this.value)" placeholder="{{ __('common.search') }}" class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                        </div>
                        <!-- Section Title -->
                        <div class="px-6 py-3 border-b border-slate-100 bg-white">
                            <h3 class="text-sm font-bold text-slate-800">Permissions</h3>
                        </div>
                        <!-- Scrollable Body -->
                        <div class="h-[400px] overflow-y-auto bg-white" id="all-perms-list">
                            <!-- JS filled -->
                        </div>
                    </div>

                    <!-- Middle Controls -->
                    <div class="flex flex-col items-center justify-center gap-4">
                        <button onclick="moveAllVisible('all-perms-list', 'assigned-perms-list')" class="text-emerald-500 hover:text-emerald-700 transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11 19l-1.41-1.41L15.17 12 9.59 6.41 11 5l7 7-7 7zM5 19l-1.41-1.41L9.17 12 3.59 6.41 5 5l7 7-7 7z"/>
                            </svg>
                        </button>
                        <button onclick="moveAllVisible('assigned-perms-list', 'all-perms-list')" class="text-emerald-500 hover:text-emerald-700 transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 19l1.41-1.41L8.83 12l5.58-5.59L13 5l-7 7 7 7zM19 19l1.41-1.41L14.83 12l5.58-5.59L19 5l-7 7 7 7z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Right Pane: Assigned Permissions -->
                    <div class="flex flex-col border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                         <!-- Search Header -->
                         <div class="p-3 bg-slate-50 border-b border-slate-200">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" id="assigned-perms-search" onkeyup="filterPane('assigned-perms-list', this.value)" placeholder="{{ __('common.search') }}" class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                        </div>
                        <!-- Section Title -->
                        <div class="px-6 py-3 border-b border-slate-100 bg-white">
                            <h3 class="text-sm font-bold text-slate-800 text-center">Role permissions</h3>
                        </div>
                        <!-- Scrollable Body -->
                        <div class="h-[400px] overflow-y-auto bg-white" id="assigned-perms-list">
                            <!-- JS filled -->
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-end gap-3 mt-8">
                    <button onclick="document.getElementById('sync-modal').classList.add('hidden')" class="px-8 py-3 rounded-xl bg-white border-none text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">
                        {{ __('common.cancel') }}
                    </button>
                    <button onclick="savePermissions()" class="px-10 py-3 rounded-xl bg-emerald-500 text-white text-sm font-bold hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20">
                        {{ __('common.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Add Role Modal ─── --}}
<div id="add-role-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 transform transition-all border border-slate-200">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight" id="add-role-title">{{ __('roles.add_new') }}</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1">{{ __('roles.add_subtitle') }}</p>
                </div>
                <button onclick="document.getElementById('add-role-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.roles.store') }}" id="role-form" class="space-y-6">
                @csrf
                <input type="hidden" name="_method" id="role-method" value="POST">
                 <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('roles.role_name') }} *</label>
                    <input type="text" name="name" id="role-name-input" required placeholder="{{ __('roles.role_name') }}..." class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('roles.table_type') }}</label>
                    <div class="relative">
                        <select name="type" id="role-type-input" class="w-full appearance-none rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm font-medium">
                            <option value="1">{{ __('roles.filter_user') }}</option>
                            <option value="0">{{ __('roles.filter_system') }} ({{ __('roles.protected') }})</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('roles.table_description') }}</label>
                    <textarea name="description" id="role-desc-input" rows="3" placeholder="{{ __('roles.description_placeholder') }}..." class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all"></textarea>
                </div>
                 <div class="flex gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('add-role-modal').classList.add('hidden')" class="flex-1 rounded-2xl bg-white border border-slate-200 py-4 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-[0.98]">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 rounded-2xl bg-indigo-600 py-4 text-sm font-bold text-white hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 active:scale-[0.98]">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<style>
    .perm-item-new {
        @apply flex items-center justify-between px-6 py-3 border-b border-slate-100 hover:bg-indigo-50/50 transition-colors;
    }
    .perm-item-new:last-child { border-bottom: none; }
    .perm-name { @apply text-xs font-medium text-slate-500; }

    /* Custom scrollbar for panes */
    #all-perms-list::-webkit-scrollbar,
    #assigned-perms-list::-webkit-scrollbar { width: 4px; }
    #all-perms-list::-webkit-scrollbar-track,
    #assigned-perms-list::-webkit-scrollbar-track { background: transparent; }
    #all-perms-list::-webkit-scrollbar-thumb,
    #assigned-perms-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .filter-btn.active {
        background: white;
        color: #4f46e5;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        border: 1px solid #e2e8f0;
    }
    .filter-btn:not(.active) {
        color: #64748b;
    }
    .filter-btn:not(.active):hover {
        color: #334155;
    }
</style>
<script>
    const permTrigger = document.getElementById('permission-select-trigger');
    const permDropdown = document.getElementById('permission-dropdown');
    const permSearchInput = document.getElementById('permission-search-input');
    const permInput = document.getElementById('permission-input');
    const filterForm = document.getElementById('filter-form');

    if (permTrigger) {
        permTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = permDropdown.classList.contains('hidden');
            if (isHidden) {
                permDropdown.classList.remove('hidden');
                setTimeout(() => {
                    permDropdown.classList.remove('scale-95', 'opacity-0');
                    permSearchInput.focus();
                }, 10);
            } else {
                closePermDropdown();
            }
        });
    }

    function closePermDropdown() {
        if (!permDropdown) return;
        permDropdown.classList.add('scale-95', 'opacity-0');
        setTimeout(() => permDropdown.classList.add('hidden'), 200);
    }

    document.addEventListener('click', (e) => {
        if (permDropdown && !permDropdown.contains(e.target) && !permTrigger.contains(e.target)) {
            closePermDropdown();
        }
    });

    if (permSearchInput) {
        permSearchInput.addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.permission-option').forEach(opt => {
                const text = opt.innerText.toLowerCase();
                opt.style.display = text.includes(val) ? 'flex' : 'none';
            });
        });
    }

    function selectPermission(val) {
        permInput.value = val;
        filterForm.submit();
    }

    function clearPermission(e) {
        e.stopPropagation();
        selectPermission('');
    }

    let searchTimeout;
    const roleSearchInput = document.getElementById('role-search');
    if (roleSearchInput) {
        roleSearchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    }

    let currentSyncRole = '';

    function filterPane(listId, val) {
        const items = document.querySelectorAll(`#${listId} .perm-item-new`);
        val = val.toLowerCase();
        items.forEach(item => {
            const text = item.querySelector('.perm-name').innerText.toLowerCase();
            item.style.display = text.includes(val) ? 'flex' : 'none';
        });
    }

    function moveAllVisible(fromId, toId) {
        const items = document.querySelectorAll(`#${fromId} .perm-item-new`);
        items.forEach(el => {
            if (el.style.display !== 'none') {
                 // Simulate click on the action button
                 el.querySelector('button').click();
            }
        });
    }

    function createPermElement(p, isAssigned) {
        const div = document.createElement('div');
        div.className = 'perm-item-new group';
        div.dataset.value = p;

        const updateContent = (assigned) => {
            if (assigned) {
                div.innerHTML = `
                    <div class="flex items-center gap-3 truncate">
                        <button class="mt-5 text-rose-400 hover:text-rose-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        <span class="mt-5 perm-name">${p}</span>
                    </div>
                `;
            } else {
                div.innerHTML = `
                    <div class="flex items-center justify-between gap-3 w-full">
                        <span class="mt-5 perm-name truncate">${p}</span>
                        <button class="mt-5 text-emerald-400 hover:text-emerald-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                `;
            }

            div.querySelector('button').onclick = (e) => {
                e.stopPropagation();
                const currentPane = div.parentElement.id;
                const targetPane = currentPane === 'all-perms-list' ? 'assigned-perms-list' : 'all-perms-list';
                const isNowAssigned = targetPane === 'assigned-perms-list';

                updateContent(isNowAssigned);
                document.getElementById(targetPane).appendChild(div);
            };
        };

        updateContent(isAssigned);
        return div;
    }

    async function openSyncModal(roleName) {
        currentSyncRole = roleName;
        document.getElementById('sync-role-title').innerText = `Role sync permission: ${roleName}`;
        const modal = document.getElementById('sync-modal');
        const allPane = document.getElementById('all-perms-list');
        const assignedPane = document.getElementById('assigned-perms-list');

        allPane.innerHTML = '<div class="p-8 text-center"><div class="animate-spin h-6 w-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto"></div></div>';
        assignedPane.innerHTML = '';
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`/${document.documentElement.lang}/admin/roles/${roleName}/permissions`);
            const data = await res.json();

            allPane.innerHTML = '';
            const assignedSet = new Set(data.assigned);

            data.all.forEach(p => {
                const isAssigned = assignedSet.has(p);
                const el = createPermElement(p, isAssigned);
                if (isAssigned) {
                    assignedPane.appendChild(el);
                } else {
                    allPane.appendChild(el);
                }
            });
        } catch (e) {
            allPane.innerHTML = '<div class="text-rose-500 p-4 text-xs font-bold text-center">{{ __("common.error") }}</div>';
        }
    }

    async function savePermissions() {
        const perms = Array.from(document.querySelectorAll('#assigned-perms-list .perm-item-new')).map(el => el.dataset.value);
        try {
            const res = await fetch(`/${document.documentElement.lang}/admin/roles/${currentSyncRole}/sync`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ permissions: perms })
            });
            if (res.ok) window.location.reload();
            else alert('{{ __("common.error") }}');
        } catch (e) { alert('Tarmoq xatosi!'); }
    }

    function editRole(name, desc, type) {
        document.getElementById('add-role-title').innerText = '{{ __("roles.edit_title") }}';
        document.getElementById('role-form').action = `/${document.documentElement.lang}/admin/roles/${name}`;
        document.getElementById('role-method').value = 'PUT';
        
        const nameInput = document.getElementById('role-name-input');
        nameInput.value = name;
        nameInput.readOnly = true;
        nameInput.classList.add('cursor-not-allowed', 'bg-slate-200', 'opacity-70', 'text-slate-500');
        
        document.getElementById('role-desc-input').value = desc === 'null' ? '' : desc;
        document.getElementById('role-type-input').value = type;
        document.getElementById('add-role-modal').classList.remove('hidden');
    }

    function openAddRoleModal() {
        document.getElementById('add-role-title').innerText = '{{ __("roles.add_new") }}';
        document.getElementById('role-form').action = `{{ route('admin.roles.store') }}`;
        document.getElementById('role-method').value = 'POST';
        
        const nameInput = document.getElementById('role-name-input');
        nameInput.value = '';
        nameInput.readOnly = false;
        nameInput.classList.remove('cursor-not-allowed', 'bg-slate-200', 'opacity-70', 'text-slate-500');
        
        document.getElementById('role-desc-input').value = '';
        document.getElementById('role-type-input').value = '1';
        document.getElementById('add-role-modal').classList.remove('hidden');
    }

    function filterTable(type) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(`btn-${type}`).classList.add('active');

        const rows = document.querySelectorAll('.role-row');
        rows.forEach(row => {
            if (type === 'all') row.style.display = 'table-row';
            else row.style.display = row.dataset.type === type ? 'table-row' : 'none';
        });
    }
</script>
@endpush
@endsection
