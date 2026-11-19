/**
 * FastAdmin-compatible bootstrap-table client (offset/limit, filter/op, multi).
 */
window.CmsTable = {
    init(options) {
        const host = document.getElementById('cms-table-host') || document.querySelector('.cms-table-host');
        const cfg = {
            base: options.base,
            tbody: document.querySelector(options.tbody || '#tbody'),
            pager: document.querySelector(options.pager || '#pager'),
            searchInput: document.querySelector(options.searchInput || '#search'),
            searchBtn: document.querySelector(options.searchBtn || '#btn-search'),
            refreshBtn: document.querySelector(options.refreshBtn || '#btn-refresh'),
            refreshIcon: document.querySelector(options.refreshIcon || '#btn-refresh-icon'),
            checkAll: document.querySelector(options.checkAll || '#check-all'),
            batchBtn: document.querySelector(options.batchBtn || '#btn-batch'),
            batchField: document.querySelector(options.batchField || '#batch-field'),
            batchValue: document.querySelector(options.batchValue || '#batch-value'),
            exportBtn: document.querySelector(options.exportBtn || '#btn-export'),
            exportXlsxBtn: document.querySelector(options.exportXlsxBtn || '#btn-export-xlsx'),
            exportXlsxUrl: options.exportXlsxUrl || null,
            pageSizeSelect: document.querySelector(options.pageSizeSelect || '#page-size'),
            totalLabel: document.querySelector(options.totalLabel || '#table-total'),
            loadingEl: document.querySelector(options.loadingEl || '#cms-table-loading'),
            applyFilterBtn: document.querySelector(options.applyFilterBtn || '#btn-apply-filter'),
            resetFilterBtn: document.querySelector(options.resetFilterBtn || '#btn-reset-filter'),
            filterTags: document.querySelector(options.filterTags || '#cms-filter-tags'),
            headingFilter: options.headingFilter || null,
            rowHtml: options.rowHtml,
            listFields: options.listFields || [],
            limit: options.limit || parseInt(document.querySelector('#page-size')?.value, 10) || 10,
            onBind: options.onBind || (() => {}),
            i18n: options.i18n || {},
        };

        let offset = 0;
        let sort = options.sort || 'id';
        let order = options.order || 'desc';
        let search = '';
        let filter = {};
        let op = {};

        function setLoading(on) {
            if (!cfg.loadingEl) return;
            cfg.loadingEl.classList.toggle('d-none', !on);
            host?.classList.toggle('is-loading', on);
            cfg.refreshIcon?.classList.toggle('fa-spin', on);
        }

        function collectAdvFilters() {
            const nextFilter = {};
            const nextOp = {};
            document.querySelectorAll('.cms-filter-item').forEach((item) => {
                const field = item.getAttribute('data-field');
                if (!field) return;
                const opEl = item.querySelector('.cms-filter-op');
                const opVal = opEl?.value || item.getAttribute('data-default-op') || '=';
                const start = item.querySelector('.cms-filter-val-start');
                const end = item.querySelector('.cms-filter-val-end');
                if (start && end) {
                    if (!start.value && !end.value) return;
                    const from = start.value ? Math.floor(new Date(start.value + 'T00:00:00').getTime() / 1000) : 0;
                    const to = end.value ? Math.floor(new Date(end.value + 'T23:59:59').getTime() / 1000) : 2147483647;
                    nextFilter[field] = `${from},${to}`;
                    nextOp[field] = 'RANGE';
                    return;
                }
                const valEl = item.querySelector('.cms-filter-val');
                const val = valEl?.value?.trim() ?? '';
                if (val === '') return;
                nextFilter[field] = val;
                nextOp[field] = opVal;
            });
            return { filter: nextFilter, op: nextOp };
        }

        function mergeHeadingFilter() {
            if (!cfg.headingFilter?.field) return;
            const active = document.querySelector(`${cfg.headingFilter.selector}.active`);
            const val = active?.getAttribute('data-value');
            if (val !== undefined && val !== '') {
                filter[cfg.headingFilter.field] = val;
                op[cfg.headingFilter.field] = '=';
            } else {
                delete filter[cfg.headingFilter.field];
                delete op[cfg.headingFilter.field];
            }
        }

        function renderFilterTags() {
            if (!cfg.filterTags) return;
            const chips = [];
            Object.keys(filter).forEach((field) => {
                if (cfg.headingFilter?.field === field) return;
                const v = filter[field];
                const o = op[field] || '=';
                chips.push(`<span class="badge rounded-pill cms-filter-tag" data-field="${field}">${field} ${o} ${v} <button type="button" class="btn-close btn-close-white btn-close-tag" aria-label="移除"></button></span>`);
            });
            cfg.filterTags.innerHTML = chips.join('');
            cfg.filterTags.querySelectorAll('.btn-close-tag').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    const tag = e.target.closest('.cms-filter-tag');
                    const field = tag?.getAttribute('data-field');
                    if (!field) return;
                    delete filter[field];
                    delete op[field];
                    const item = document.querySelector(`.cms-filter-item[data-field="${field}"]`);
                    item?.querySelectorAll('.cms-filter-val, .cms-filter-val-start, .cms-filter-val-end').forEach((el) => { el.value = ''; });
                    offset = 0;
                    load();
                });
            });
        }

        function updateSortIcons() {
            document.querySelectorAll('.cms-sort-th').forEach((th) => {
                const icon = th.querySelector('.cms-sort-icon');
                const f = th.getAttribute('data-sort-field');
                if (!icon || !f) return;
                icon.className = 'fas cms-sort-icon ' + (f === sort
                    ? (order === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary')
                    : 'fa-sort text-muted');
            });
        }

        function selectedIds() {
            return [...cfg.tbody.querySelectorAll('.row-check:checked')].map((c) => c.value);
        }

        async function load() {
            mergeHeadingFilter();
            setLoading(true);
            const params = new URLSearchParams({
                offset, limit: cfg.limit, sort, order, search,
                filter: JSON.stringify(filter),
                op: JSON.stringify(op),
            });
            if (options.extraQuery && typeof options.extraQuery === 'object') {
                Object.entries(options.extraQuery).forEach(([k, v]) => {
                    if (v !== '' && v !== null && v !== undefined) {
                        params.set(k, v);
                    }
                });
            }
            try {
                const res = await fetch(`${cfg.base}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const data = await res.json();
                cfg.tbody.innerHTML = (data.rows || []).map(cfg.rowHtml).join('');
                if (!cfg.tbody.children.length) {
                    const cols = (cfg.listFields?.length || 4) + 2;
                    const noData = cfg.i18n?.noData || '暂无数据';
                    cfg.tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>${noData}</td></tr>`;
                }
                cfg.onBind(cfg.tbody, data.rows || []);
                renderPager(data.total || 0);
                if (cfg.totalLabel) {
                    const from = data.total ? offset + 1 : 0;
                    const to = Math.min(offset + cfg.limit, data.total || 0);
                    const total = data.total || 0;
                    if (cfg.i18n.totalRange) {
                        cfg.totalLabel.textContent = cfg.i18n.totalRange
                            .replace('__TOTAL__', total)
                            .replace('__FROM__', from)
                            .replace('__TO__', to);
                    } else if (cfg.i18n.total) {
                        cfg.totalLabel.textContent = cfg.i18n.total.replace('__COUNT__', total);
                    } else {
                        cfg.totalLabel.textContent = `共 ${total} 条，显示 ${from}–${to}`;
                    }
                }
                renderFilterTags();
                updateSortIcons();
            } catch (e) {
                const loadFail = cfg.i18n?.loadFail || '加载失败，请重试';
                cfg.tbody.innerHTML = `<tr><td colspan="20" class="text-danger text-center py-3">${loadFail}</td></tr>`;
            } finally {
                setLoading(false);
            }
        }

        function renderPager(total) {
            const pages = Math.max(1, Math.ceil(total / cfg.limit));
            const current = Math.floor(offset / cfg.limit) + 1;
            if (!cfg.pager) return;
            cfg.pager.innerHTML = '';

            const add = (label, page, disabled = false, active = false) => {
                const li = document.createElement('li');
                li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                li.innerHTML = `<a class="page-link" href="#" data-page="${page}">${label}</a>`;
                cfg.pager.appendChild(li);
            };

            add('«', current - 1, current <= 1);
            const windowSize = 5;
            let start = Math.max(1, current - Math.floor(windowSize / 2));
            let end = Math.min(pages, start + windowSize - 1);
            start = Math.max(1, end - windowSize + 1);
            if (start > 1) {
                add('1', 1);
                if (start > 2) add('…', 0, true);
            }
            for (let p = start; p <= end; p++) {
                add(String(p), p, false, p === current);
            }
            if (end < pages) {
                if (end < pages - 1) add('…', 0, true);
                add(String(pages), pages);
            }
            add('»', current + 1, current >= pages);

            cfg.pager.querySelectorAll('.page-link[data-page]').forEach((a) => {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    const p = parseInt(a.getAttribute('data-page'), 10);
                    if (!p || p < 1 || p > pages) return;
                    offset = (p - 1) * cfg.limit;
                    load();
                });
            });
        }

        if (cfg.headingFilter?.field) {
            document.querySelectorAll(cfg.headingFilter.selector).forEach((el) => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelectorAll(cfg.headingFilter.selector).forEach((n) => n.classList.remove('active'));
                    el.classList.add('active');
                    offset = 0;
                    load();
                });
            });
        }

        cfg.searchBtn?.addEventListener('click', () => {
            search = cfg.searchInput?.value?.trim() || '';
            offset = 0;
            load();
        });
        cfg.searchInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                cfg.searchBtn?.click();
            }
        });

        cfg.refreshBtn?.addEventListener('click', () => load());

        cfg.pageSizeSelect?.addEventListener('change', () => {
            cfg.limit = parseInt(cfg.pageSizeSelect.value, 10) || 10;
            offset = 0;
            load();
        });

        cfg.applyFilterBtn?.addEventListener('click', () => {
            const collected = collectAdvFilters();
            filter = collected.filter;
            op = collected.op;
            offset = 0;
            load();
        });

        cfg.resetFilterBtn?.addEventListener('click', () => {
            document.querySelectorAll('.cms-filter-val, .cms-filter-val-start, .cms-filter-val-end').forEach((el) => { el.value = ''; });
            filter = {};
            op = {};
            offset = 0;
            load();
        });

        document.querySelectorAll('.cms-sort-th').forEach((th) => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const field = th.getAttribute('data-sort-field');
                if (!field) return;
                if (sort === field) {
                    order = order === 'asc' ? 'desc' : 'asc';
                } else {
                    sort = field;
                    order = 'desc';
                }
                load();
            });
        });

        cfg.checkAll?.addEventListener('change', () => {
            cfg.tbody.querySelectorAll('.row-check').forEach((c) => { c.checked = cfg.checkAll.checked; });
        });

        cfg.batchBtn?.addEventListener('click', async () => {
            const ids = selectedIds();
            const field = cfg.batchField?.value;
            const value = cfg.batchValue?.value;
            if (!ids.length || !field) return alert('请选择记录和字段');
            const body = new URLSearchParams({
                ids: ids.join(','),
                params: field,
                value,
                _token: document.querySelector('meta[name=csrf-token]').content,
            });
            const res = await fetch(`${cfg.base}/multi`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body,
            });
            const data = await res.json();
            alert(data.msg || '完成');
            if (data.code === 1) load();
        });

        cfg.exportBtn?.addEventListener('click', async () => {
            mergeHeadingFilter();
            const params = new URLSearchParams({
                offset: 0, limit: 10000, sort, order, search,
                filter: JSON.stringify(filter), op: JSON.stringify(op),
            });
            setLoading(true);
            try {
                const res = await fetch(`${cfg.base}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const data = await res.json();
                const rows = data.rows || [];
                if (!rows.length) return alert('无数据');
                const headers = Object.keys(rows[0]);
                const csv = [headers.join(',')].concat(rows.map((r) => headers.map((h) => JSON.stringify(r[h] ?? '')).join(','))).join('\n');
                const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'export.csv';
                a.click();
            } finally {
                setLoading(false);
            }
        });

        cfg.exportXlsxBtn?.addEventListener('click', () => {
            if (!cfg.exportXlsxUrl) return alert('当前列表不支持 Excel 导出');
            mergeHeadingFilter();
            const params = new URLSearchParams({
                offset: 0, limit: 10000, sort, order, search,
                filter: JSON.stringify(filter), op: JSON.stringify(op),
            });
            window.location.href = `${cfg.exportXlsxUrl}?${params.toString()}`;
        });

        load();

        return { reload: () => load(), getOffset: () => offset, setOffset: (v) => { offset = v; } };
    },
};