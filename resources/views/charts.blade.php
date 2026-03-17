@extends('layouts.admin')
@section('title', 'Statistika')

@section('content')
<div class="w-full mx-auto" id="charts-container">
    <!-- Header -->
    <div class="mb-10">
        <h1 class="text-4xl font-black text-indigo-900 tracking-tight">Audit Statistikasi</h1>
        <p class="text-[13px] font-medium text-slate-400 mt-2 tracking-wide">Tizimdagi barcha harakatlar va loyihalar bo'yicha tahliliy ma'lumotlar</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Projects Distribution -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 hover:shadow-2xl transition-all duration-300">
            <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
                <span class="p-2 bg-indigo-50 rounded-lg text-indigo-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                Loyihalar bo'yicha
            </h3>
            <div class="h-[300px] relative">
                <canvas id="projectsChart"></canvas>
            </div>
        </div>

        <!-- Events Distribution -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 hover:shadow-2xl transition-all duration-300">
            <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
                <span class="p-2 bg-emerald-50 rounded-lg text-emerald-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2"/></svg>
                </span>
                Amallar bo'yicha
            </h3>
            <div class="h-[300px] relative">
                <canvas id="eventsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Timeline Chart -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 mb-8 hover:shadow-2xl transition-all duration-300">
        <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
            <span class="p-2 bg-blue-50 rounded-lg text-blue-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </span>
            So'nggi 30 kundagi faollik
        </h3>
        <div class="h-[350px] relative">
            <canvas id="timelineChart"></canvas>
        </div>
    </div>

    <!-- Top URLs -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 hover:shadow-2xl transition-all duration-300">
        <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
            <span class="p-2 bg-purple-50 rounded-lg text-purple-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.821a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </span>
            Eng faol 10 ta URL
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="pb-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">URL MANZILI</th>
                        <th class="pb-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-right">SONI</th>
                    </tr>
                </thead>
                <tbody id="top-urls-body">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('charts-container');
        
        // Show loading state
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-white/50 backdrop-blur-sm z-50 flex items-center justify-center';
        loadingOverlay.innerHTML = `
            <div class="flex flex-col items-center gap-4">
                <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-bold text-slate-600">Statistika tayyorlanmoqda...</span>
            </div>
        `;
        document.body.appendChild(loadingOverlay);

        fetch('/api/audits/stats', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            loadingOverlay.remove();
            renderCharts(data);
        })
        .catch(err => {
            console.error('Stats loading error:', err);
            loadingOverlay.innerHTML = '<span class="text-rose-500 font-bold">Xatolik yuz berdi!</span>';
        });

        function renderCharts(data) {
            // Shared config
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 11, weight: '600', family: 'Inter' }
                        }
                    }
                }
            };

            // 1. Projects Chart (Bar)
            new Chart(document.getElementById('projectsChart'), {
                type: 'bar',
                data: {
                    labels: data.projects.map(p => p.project_name),
                    datasets: [{
                        label: 'Auditlar soni',
                        data: data.projects.map(p => p.count),
                        backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        borderRadius: 12,
                        hoverBackgroundColor: '#4f46e5'
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Events Chart (Doughnut)
            new Chart(document.getElementById('eventsChart'), {
                type: 'doughnut',
                data: {
                    labels: data.events.map(e => e.event),
                    datasets: [{
                        data: data.events.map(e => e.count),
                        backgroundColor: [
                            '#10b981', '#3b82f6', '#f43f5e', '#8b5cf6', 
                            '#f59e0b', '#06b6d4', '#d946ef', '#ec4899'
                        ],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '70%',
                }
            });

            // 3. Timeline Chart (Line)
            new Chart(document.getElementById('timelineChart'), {
                type: 'line',
                data: {
                    labels: data.timeline.map(t => t.date),
                    datasets: [{
                        label: 'Kunlik faollik',
                        data: data.timeline.map(t => t.count),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.05)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 4. URL Table
            const urlBody = document.getElementById('top-urls-body');
            data.urls.forEach(u => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-50 hover:bg-slate-50 transition-colors group';
                tr.innerHTML = `
                    <td class="py-4 pr-4">
                        <div class="text-xs font-bold text-slate-600 truncate max-w-[400px] font-mono group-hover:text-indigo-600 transition-colors" title="${u.url}">
                            ${u.url}
                        </div>
                    </td>
                    <td class="py-4 text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-100 text-slate-600">
                            ${u.count}
                        </span>
                    </td>
                `;
                urlBody.appendChild(tr);
            });
        }
    });
</script>
@endpush
@endsection
