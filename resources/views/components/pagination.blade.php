<div id="pagination-container" class="mt-6 border-t border-slate-200 pt-5 flex items-center justify-between">
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
        <div id="pagination-container" class="mt-6 flex items-center justify-center">
            <div id="pagination-nav-links" class="flex items-center gap-3">
                <!-- buttons -->
            </div>
        </div>
    </div>
</div>

<style>
    .pagination-btn{
        display:flex;
        align-items:center;
        justify-content:center;
        width:38px;
        height:38px;
        border-radius:9999px;
        font-size:15px;
        font-weight:600;
        color:#94a3b8;
        transition:0.2s;
    }

    .pagination-btn:hover{
        background:#f1f5f9;
        color:#334155;
    }

    .pagination-btn.active{
        background:#cfe3db;
        color:#065f46;
    }

    .pagination-btn.disabled{
        opacity:.35;
        pointer-events:none;
    }

    .pagination-icon{
        width:20px;
        height:20px;
    }
</style>

<script>
    function renderPagination(data, onPageChange) {

        const navEl = document.getElementById('pagination-nav-links');

        if (!data || data.last_page <= 1) {
            document.getElementById('pagination-container').classList.add('hidden');
            return;
        }

        document.getElementById('pagination-container').classList.remove('hidden');

        let html = '';

        html += `
    <button onclick="${onPageChange}(1)"
    class="pagination-btn ${data.current_page === 1 ? 'disabled' : ''}">
        «
    </button>`;

        html += `
    <button onclick="${onPageChange}(${data.current_page-1})"
    class="pagination-btn ${data.current_page === 1 ? 'disabled' : ''}">
        ‹
    </button>`;


        html += `
    <button class="pagination-btn active">
        ${data.current_page}
    </button>`;


        html += `
    <button onclick="${onPageChange}(${data.current_page+1})"
    class="pagination-btn ${data.current_page === data.last_page ? 'disabled' : ''}">
        ›
    </button>`;


        html += `
    <button onclick="${onPageChange}(${data.last_page})"
    class="pagination-btn ${data.current_page === data.last_page ? 'disabled' : ''}">
        »
    </button>`;

        navEl.innerHTML = html;
    }
</script>
