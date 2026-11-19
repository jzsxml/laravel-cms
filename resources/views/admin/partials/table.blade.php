@props(['columns' => [], 'loadUrl' => ''])

<div class="card cms-card cms-table-wrap">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0" id="data-table">
                <thead>
                <tr>
                    @foreach($columns as $col)
                        <th>{{ $col['label'] }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <nav class="mt-3">
            <ul class="pagination pagination-sm mb-0" id="pager"></ul>
        </nav>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const loadUrl = @json($loadUrl);
    const columns = @json($columns);
    const tbody = document.querySelector('#data-table tbody');
    const pager = document.querySelector('#pager');
    let page = 1;
    const limit = 10;

    function renderRows(rows) {
        tbody.innerHTML = '';
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = columns.map(c => `<td>${row[c.key] ?? ''}</td>`).join('');
            tbody.appendChild(tr);
        });
    }

    function renderPager(total) {
        const pages = Math.max(1, Math.ceil(total / limit));
        const current = page;
        pager.innerHTML = '';
        for (let p = 1; p <= pages; p++) {
            const li = document.createElement('li');
            li.className = 'page-item' + (p === current ? ' active' : '');
            li.innerHTML = `<a class="page-link" href="#">${p}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                page = p;
                load();
            });
            pager.appendChild(li);
        }
    }

    async function load() {
        const res = await fetch(`${loadUrl}?limit=${limit}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        renderRows(data.rows || []);
        renderPager(data.total || 0);
    }

    load();
})();
</script>
@endpush