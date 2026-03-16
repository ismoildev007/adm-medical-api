@extends('layouts.admin')
@section('title', __('sidebar.audit_logs'))

@section('content')
    <div class="w-full mx-auto">

        <!-- Header -->
        <div class="mb-10 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-black text-indigo-900 tracking-tight">{{ __('audits.title') }}</h1>
                <p class="text-[13px] font-medium text-slate-400 mt-2 tracking-wide">{{ __('audits.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="applyFilters()" id="refresh-btn" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50 transition-all active:scale-95">
                    <svg id="refresh-icon" class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ __('audits.refresh') }}
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 p-6 lg:p-8">
            <div class="flex flex-wrap lg:flex-nowrap gap-6 items-end">

                <!-- Search Input -->
                <div class="flex flex-col gap-2 flex-[2] w-full lg:w-auto">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">{{ __('audits.filter_search') }}</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="filter-search" oninput="debouncedSearch()" placeholder="{{ __('audits.filter_search') }} ..."
                            class="w-full rounded-2xl border-none bg-slate-50 pl-12 pr-4 py-3.5 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">
                    </div>
                </div>

                <!-- Project Filter (Searchable) -->
                <div class="flex flex-col gap-2 flex-1 min-w-[200px] w-full lg:w-auto relative" id="project-filter-container">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">LOYIHA</label>
                    <div class="relative">
                        <div id="project-select-trigger" class="w-full rounded-2xl bg-slate-50 border-none px-4 py-3.5 text-sm font-bold text-slate-700 cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all">
                            <span id="project-selected-text" class="truncate">Loyiha (Barchasi)</span>
                            <div class="flex items-center gap-2">
                                <button type="button" id="clear-project-btn" class="text-slate-300 hover:text-rose-500 transition-colors hidden" onclick="clearProject(event)">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <svg class="h-4 w-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <!-- Dropdown Content -->
                        <div id="project-dropdown" class="absolute left-0 right-0 mt-3 bg-white rounded-[1.5rem] shadow-2xl border border-slate-100 p-3 z-[60] hidden overflow-hidden scale-95 opacity-0 transition-all duration-200 origin-top">
                            <div class="relative mb-3">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" id="project-search-input" placeholder="Loyihani qidiring..." autocomplete="off"
                                    class="w-full rounded-xl bg-slate-50 border-none pl-10 pr-4 py-2.5 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                            <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1" id="project-options-list">
                                <!-- Options will be loaded via JS -->
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="filter-project" value="">
                </div>

                <!-- Date From -->
                <div class="flex flex-col gap-2 min-w-[170px] w-full lg:w-auto">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">DAN</label>
                    <input type="date" id="filter-date-from" onchange="syncDateRange()"
                        class="w-full rounded-2xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                </div>

                <!-- Date To -->
                <div class="flex flex-col gap-2 min-w-[170px] w-full lg:w-auto">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] ml-1">GACHA</label>
                    <input type="date" id="filter-date-to"
                        class="w-full rounded-2xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                </div>

                <!-- Action Button -->
                <div class="pb-0.5">
                    <button onclick="clearFilters()" class="h-[52px] w-[52px] flex items-center justify-center rounded-2xl bg-white text-slate-400 border border-slate-200 hover:text-rose-500 hover:bg-rose-50 hover:border-rose-100 transition-all active:scale-95 shadow-sm" title="{{ __('audits.filter_clear') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

            </div>

            <!-- Active Filter Tags -->
            <div id="active-filters" class="mt-6 flex flex-wrap gap-2 hidden border-t border-slate-50 pt-6"></div>
        </div>

        <!-- Results count -->
        <div id="results-count" class="text-xs font-bold text-slate-400 mb-4 px-2 tracking-wider"></div>

        <!-- Table -->
        <div class="bg-white shadow-2xl shadow-slate-200/40 rounded-[2rem] border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_id') }}</th>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_project') }}</th>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_model') }}</th>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_action') }}</th>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_old_values') }}</th>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_new_values') }}</th>
                            <th class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">{{ __('audits.table_timestamp') }}</th>
                        </tr>
                    </thead>
                    <tbody id="audit-table-body" class="bg-white">
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-bold tracking-widest uppercase text-slate-300">{{ __('common.loading') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <x-pagination />
    </div>

    <!-- Detail Modal -->
    <div id="json-modal" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" id="modal-backdrop"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl ring-1 ring-slate-200">
                    <!-- Modal Header -->
                    <div class="bg-white px-6 pt-5 pb-0 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100" id="modal-icon-wrap">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold leading-6 text-slate-900" id="modal-title">{{ __('audits.modal_details') }}</h3>
                                <p class="text-xs text-slate-400" id="modal-subtitle"></p>
                            </div>
                        </div>
                        <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <!-- Modal Body -->
                    <div class="px-6 pt-4 pb-6">
                        <!-- JSON mode (for old/new values) -->
                        <div id="modal-body-json" class="hidden">
                            <div class="bg-slate-900 rounded-xl p-5 overflow-auto custom-scrollbar max-h-[65vh] shadow-inner">
                                <pre id="modal-content" class="text-sm text-emerald-400 font-mono leading-relaxed whitespace-pre-wrap"></pre>
                            </div>
                        </div>
                        <!-- Table mode (for model detail) -->
                        <div id="modal-body-table" class="hidden">
                            <div id="modal-source-badge" class="mb-3 flex items-center gap-2"></div>
                            <div class="overflow-auto custom-scrollbar max-h-[65vh] rounded-xl border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/3">{{ __('roles.role_name') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-table-body" class="bg-white divide-y divide-slate-100"></tbody>
                                </table>
                            </div>
                            <div id="modal-note" class="mt-3 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 hidden"></div>
                        </div>
                        <!-- Loading spinner -->
                         <div id="modal-loading" class="hidden py-10 text-center">
                            <svg class="animate-spin h-8 w-8 text-indigo-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-slate-400 mt-2">{{ __('common.loading') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showModal() {
            document.getElementById('json-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('json-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function setModalMode(mode) {
            document.getElementById('modal-body-json').classList.toggle('hidden', mode !== 'json');
            document.getElementById('modal-body-table').classList.toggle('hidden', mode !== 'table');
            document.getElementById('modal-loading').classList.toggle('hidden', mode !== 'loading');
        }

        function openModal(title, contentStr) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-subtitle').textContent = '';
            document.getElementById('modal-content').textContent = contentStr;
            setModalMode('json');
            showModal();
        }

        function openModelModal(auditId, modelName, modelId) {
            document.getElementById('modal-title').textContent = modelName + ' #' + modelId;
            document.getElementById('modal-subtitle').textContent = 'Full model record';
            setModalMode('loading');
            showModal();

            fetch(`/api/audits/${auditId}/model`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(result => {
                    const isLive = result.source === 'live';
                    document.getElementById('modal-source-badge').innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${
                            isLive ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200'
                        }">
                            <span class="h-1.5 w-1.5 rounded-full ${ isLive ? 'bg-emerald-500' : 'bg-amber-500' }"></span>
                            ${ isLive ? 'Live DB Record' : 'Audit Snapshot' }
                        </span>
                        <span class="text-xs text-slate-400">${result.project || ''}</span>
                    `;

                    const noteEl = document.getElementById('modal-note');
                    if (result.note) {
                        noteEl.textContent = '⚠ ' + result.note;
                        noteEl.classList.remove('hidden');
                    } else {
                        noteEl.classList.add('hidden');
                    }

                    const tbody = document.getElementById('modal-table-body');
                    const data = result.data || {};
                    tbody.innerHTML = Object.entries(data).map(([key, val]) => {
                        let display;
                        if (val === null || val === undefined) {
                            display = '<span class="text-slate-300 italic">null</span>';
                        } else if (typeof val === 'object') {
                            display = `<code class="text-xs bg-slate-50 px-2 py-1 rounded border border-slate-200 text-slate-600 block whitespace-pre-wrap">${JSON.stringify(val, null, 2)}</code>`;
                        } else if (typeof val === 'boolean') {
                            display = val
                                ? '<span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 font-medium">true</span>'
                                : '<span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-700 font-medium">false</span>';
                        } else {
                            const str = String(val);
                            if (str.startsWith('$2y$') || str.startsWith('$argon')) {
                                display = `<span class="font-mono text-xs text-slate-400">${str.substring(0,20)}…</span> <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-slate-100 rounded text-slate-500">hashed</span>`;
                            } else {
                                display = `<span class="text-slate-700">${str}</span>`;
                            }
                        }
                        return `<tr class="hover:bg-indigo-50/30">
                            <td class="px-4 py-2.5 text-xs font-mono font-semibold text-slate-500 align-top whitespace-nowrap">${key}</td>
                            <td class="px-4 py-2.5 text-sm align-top max-w-[380px] break-words">${display}</td>
                        </tr>`;
                    }).join('');

                    setModalMode('table');
                })
                .catch(err => {
                    setModalMode('json');
                    document.getElementById('modal-content').textContent = 'Error loading model data:\n' + err.message;
                    console.error(err);
                });
        }

        function getFilters() {
            return {
                project: document.getElementById('filter-project').value,
                date_from: document.getElementById('filter-date-from').value,
                date_to: document.getElementById('filter-date-to').value,
                search: document.getElementById('filter-search').value,
            };
        }

        function clearFilters() {
            if (typeof selectProject === 'function') {
                selectProject('');
            } else {
                document.getElementById('filter-project').value = '';
                if(document.getElementById('project-selected-text'))
                    document.getElementById('project-selected-text').innerText = 'Loyiha (Barchasi)';
            }

            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            document.getElementById('filter-date-to').min = ''; // Reset min constraint
            document.getElementById('filter-search').value = '';
            applyFilters();
        }

        function syncDateRange() {
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateToInput = document.getElementById('filter-date-to');

            if (dateFrom) {
                dateToInput.min = dateFrom;
                if (dateToInput.value && dateToInput.value < dateFrom) {
                    dateToInput.value = dateFrom;
                }
            } else {
                dateToInput.min = '';
            }
        }

        let searchTimeout;
        function debouncedSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        }

        function renderActiveTags(filters) {
            const container = document.getElementById('active-filters');
            const tags = [];

            if (filters.search) tags.push({ label: `Qidiruv: ${filters.search}`, key: 'search' });
            if (filters.project) tags.push({ label: `Loyiha: ${filters.project}`, key: 'project' });
            if (filters.date_from) tags.push({ label: `Dan: ${filters.date_from}`, key: 'date_from' });
            if (filters.date_to) tags.push({ label: `Gacha: ${filters.date_to}`, key: 'date_to' });

            if (tags.length === 0) {
                container.classList.add('hidden');
                container.innerHTML = '';
                return;
            }

            container.classList.remove('hidden');
            container.innerHTML = tags.map(t => `
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">
                    ${t.label}
                    <button onclick="clearSingleFilter('${t.key}')" class="hover:text-indigo-900 transition-colors">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            `).join('');
        }

        function clearSingleFilter(key) {
            const map = {
                project: 'filter-project',
                date_from: 'filter-date-from',
                date_to: 'filter-date-to',
                search: 'filter-search'
            };

            if (key === 'date_from') {
                document.getElementById('filter-date-to').min = '';
            }

            document.getElementById(map[key]).value = '';
            applyFilters();
        }

        function applyFilters() {
            currentPage = 1;
            const filters = getFilters();
            renderActiveTags(filters);

            const params = new URLSearchParams();
            if (filters.project) params.append('project', filters.project);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.search) params.append('search', filters.search);

            fetchAudits(params.toString());
        }

        function goToPage(page) {
            currentPage = page;
            const filters = getFilters();
            const params = new URLSearchParams();
            if (filters.project) params.append('project', filters.project);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.search) params.append('search', filters.search);
            params.append('page', page);

            fetchAudits(params.toString());
            window.scrollTo({ top: document.querySelector('table').offsetTop - 100, behavior: 'smooth' });
        }

        let currentPage = 1;

        function fetchAudits(queryString = '') {
            const tbody = document.getElementById('audit-table-body');
            tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">
                <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>Loading...</td></tr>`;

            const url = '/api/audits' + (queryString ? '?' + queryString : '');
            fetch(url, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(paginated => {
                    const data = paginated.data || [];
                    tbody.innerHTML = '';

                    document.getElementById('results-count').textContent = `Showing ${paginated.from || 0} - ${paginated.to || 0} of ${paginated.total || 0} records`;

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-14 text-center bg-slate-50 text-slate-500">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            No audit logs match the current filters.</td></tr>`;
                        document.getElementById('pagination-container').classList.add('hidden');
                        return;
                    }

                    // bu yerda auditdan kelgan malumotlar ko'rinadi (design)
                    data.forEach(audit => {
                        const tr = document.createElement('tr');
                        tr.className = "hover:bg-indigo-50/30 transition-colors group";

                        const modelParts = audit.auditable_type ? audit.auditable_type.split('\\') : ['Unknown'];
                        const modelName = modelParts[modelParts.length - 1];
                        const projectName = audit.project_name || 'audit';
                        const oldJson = formatJSONSafe(audit.old_values);
                        const newJson = formatJSONSafe(audit.new_values);

                        tr.innerHTML = `
                            <td class="px-6 py-6 whitespace-nowrap text-xs text-slate-400 font-bold border-b border-slate-50">#${audit.id}</td>
                            <td class="px-6 py-6 whitespace-nowrap border-b border-slate-50">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">${projectName}</span>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap border-b border-slate-50">
                                <button onclick="openModelModal(${audit.id}, '${modelName}', '${audit.auditable_id}')" class="text-left">
                                    <div class="text-[13px] font-black text-slate-800">${modelName}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID: ${audit.auditable_id}</div>
                                </button>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap border-b border-slate-50">
                                <span class="px-2.5 py-1 inline-flex text-[10px] font-black uppercase tracking-wider rounded-lg border ${getEventColor(audit.event)}">${audit.event}</span>
                            </td>
                            <td class="px-6 py-6 text-sm text-slate-600 w-1/4 max-w-[280px] border-b border-slate-50">
                                <div class="json-block group/json cursor-pointer relative" data-title="Old Values [#${audit.id} — ${modelName}]" data-content="${encodeURIComponent(oldJson)}">
                                    <div class="truncate text-[11px] font-mono bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-slate-500 transition-all group-hover/json:border-indigo-200 group-hover/json:bg-indigo-50/30">
                                        ${oldJson === 'None' ? '<span class="text-slate-200">—</span>' : oldJson}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-sm text-slate-600 w-1/4 max-w-[280px] border-b border-slate-50">
                                <div class="json-block group/json cursor-pointer relative" data-title="New Values [#${audit.id} — ${modelName}]" data-content="${encodeURIComponent(newJson)}">
                                    <div class="truncate text-[11px] font-mono bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-slate-500 transition-all group-hover/json:border-indigo-200 group-hover/json:bg-indigo-50/30">
                                        ${newJson === 'None' ? '<span class="text-slate-200">—</span>' : newJson}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-sm text-slate-500 border-b border-slate-50">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400 border border-slate-200">
                                        ${audit.user_id || '?'}
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-700">${new Date(audit.created_at).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })}</div>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    renderPagination(paginated, 'goToPage');
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-12 text-center text-rose-500 bg-rose-50 font-medium">Failed to load audit logs. Check console for details.</td></tr>';
                    console.error('Error fetching audits:', err);
                });
        }

        const projTrigger = document.getElementById('project-select-trigger');
        const projDropdown = document.getElementById('project-dropdown');
        const projSearchInput = document.getElementById('project-search-input');
        const projInput = document.getElementById('filter-project');
        const projSelectedText = document.getElementById('project-selected-text');
        const clearProjBtn = document.getElementById('clear-project-btn');

        if (projTrigger) {
            projTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = projDropdown.classList.contains('hidden');
                if (isHidden) {
                    projDropdown.classList.remove('hidden');
                    setTimeout(() => {
                        projDropdown.classList.remove('scale-95', 'opacity-0');
                        projSearchInput.focus();
                    }, 10);
                } else {
                    closeProjDropdown();
                }
            });
        }

        function closeProjDropdown() {
            if (!projDropdown) return;
            projDropdown.classList.add('scale-95', 'opacity-0');
            setTimeout(() => projDropdown.classList.add('hidden'), 200);
        }

        document.addEventListener('click', (e) => {
            if (projDropdown && !projDropdown.contains(e.target) && !projTrigger.contains(e.target)) {
                closeProjDropdown();
            }
        });

        if (projSearchInput) {
            projSearchInput.addEventListener('input', (e) => {
                const val = e.target.value.toLowerCase();
                document.querySelectorAll('.project-option').forEach(opt => {
                    const text = opt.innerText.toLowerCase();
                    opt.style.display = text.includes(val) ? 'flex' : 'none';
                });
            });
        }

        function selectProject(val) {
            projInput.value = val;
            projSelectedText.innerText = val || 'Loyiha (Barchasi)';

            if (val) {
                clearProjBtn.classList.remove('hidden');
            } else {
                clearProjBtn.classList.add('hidden');
            }

            closeProjDropdown();
            applyFilters();
        }

        function clearProject(e) {
            e.stopPropagation();
            selectProject('');
        }

        function loadProjects() {
            fetch('/api/audits/projects', {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(projects => {
                    const list = document.getElementById('project-options-list');
                    if (!list) return;

                    const allOpt = document.createElement('div');
                    allOpt.className = 'project-option px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt';
                    allOpt.innerHTML = `Barchasi`;
                    allOpt.onclick = () => selectProject('');
                    list.appendChild(allOpt);

                    projects.forEach(p => {
                        const opt = document.createElement('div');
                        opt.className = 'project-option px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer transition-all flex items-center justify-between group/opt';
                        opt.innerHTML = p;
                        opt.onclick = () => selectProject(p);
                        list.appendChild(opt);
                    });
                });
        }

        function getEventColor(event) {
            const colors = {
                'created': 'bg-emerald-50 text-emerald-600 border-emerald-100',
                'updated': 'bg-blue-50 text-blue-600 border-blue-100',
                'deleted': 'bg-rose-50 text-rose-600 border-rose-100',
                'restored': 'bg-purple-50 text-purple-600 border-purple-100',
                'LOGGED_IN': 'bg-slate-50 text-slate-600 border-slate-200',
                'LOGGED_OUT': 'bg-amber-50 text-amber-600 border-amber-100'
            };
            return colors[event] || 'bg-slate-50 text-slate-600 border-slate-100';
        }

        function formatJSONSafe(obj) {
            if (!obj) return 'None';
            if (typeof obj === 'string') {
                try { obj = JSON.parse(obj); } catch(e) { return obj; }
            }
            if (Object.keys(obj).length === 0) return 'None';
            return JSON.stringify(obj, null, 2);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('modal-backdrop').addEventListener('click', closeModal);

            document.getElementById('audit-table-body').addEventListener('click', function(e) {
                const block = e.target.closest('.json-block');
                if (block) {
                    const title = block.getAttribute('data-title');
                    const rawData = decodeURIComponent(block.getAttribute('data-content'));
                    openModal(title, rawData);
                }
            });

            ['filter-date-from', 'filter-date-to'].forEach(id => {
                document.getElementById(id).addEventListener('change', applyFilters);
            });

            loadProjects();
            fetchAudits();
        });
    </script>
@endsection
