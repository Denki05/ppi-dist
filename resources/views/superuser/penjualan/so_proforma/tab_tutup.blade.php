<!-- <div class="mb-3">
    <h5>Proforma Selesai</h5>
    <small class="text-muted">Daftar proforma yang sudah selesai diproses</small>
</div> -->

{{-- Info hasil (search ada di kanan atas header tab) --}}
<div class="mb-2"><small id="tutupInfo" class="text-muted"></small></div>

<table class="table table-sm table-striped">

    <thead>
        <tr>
            <th style="width:36px;">#</th>
            <th>Kode</th>
            <th>Customer</th>
            <th>Grand Total</th>
            <th>Tanggal</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody id="tutupBody">

    @forelse($tutup as $row)

    <tr data-name="{{ e(mb_strtolower($row->member->name ?? '')) }}">

        <td class="tutup-num">{{ $loop->iteration }}</td>
        <td><span class="font-weight-bold text-primary">{{ $row->code }}</span></td>
        <td>{{ $row->member->name ?? '-' }} <small class="text-muted">{{ $row->member->text_kota ?? '' }}</small></td>
        <td>{{ optional($row->details_cost)->grand_total_idr ? number_format($row->details_cost->grand_total_idr,0,',','.') : '-' }}</td>
        <td><small class="text-muted">{{ $row->updated_at ? $row->updated_at->format('d/m/Y H:i') : '-' }}</small></td>
        <td>
            <span class="badge badge-success">
                Selesai
            </span>
        </td>
    </tr>

    @empty

    <tr>
        <td colspan="6" class="text-center text-muted">
            Tidak ada data
        </td>
    </tr>

    @endforelse

    </tbody>

</table>

{{-- Pagination --}}
<div class="d-flex align-items-center justify-content-between mt-2">
    <small id="tutupPageInfo" class="text-muted"></small>
    <nav aria-label="Paging tab tutup"><ul id="tutupPager" class="pagination pagination-sm mb-0"></ul></nav>
</div>

<script>
(function () {
    var PER_PAGE = 10;
    var page = 1, q = '';
    var body = document.getElementById('tutupBody');
    if (!body) return;
    var rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-name]'));
    var search = document.getElementById('tutupSearch');
    var info = document.getElementById('tutupInfo');
    var pageInfo = document.getElementById('tutupPageInfo');
    var pager = document.getElementById('tutupPager');

    function filtered() {
        if (!q) return rows;
        return rows.filter(function (tr) {
            return (tr.getAttribute('data-name') || '').indexOf(q) !== -1;
        });
    }
    function render() {
        var list = filtered();
        var total = list.length;
        var pages = Math.max(1, Math.ceil(total / PER_PAGE));
        if (page > pages) page = pages;
        rows.forEach(function (tr) { tr.style.display = 'none'; });
        var start = (page - 1) * PER_PAGE;
        list.slice(start, start + PER_PAGE).forEach(function (tr, i) {
            tr.style.display = '';
            var num = tr.querySelector('.tutup-num');
            if (num) num.textContent = start + i + 1;
        });
        if (info) info.textContent = total ? ('Menampilkan ' + (total ? start + 1 : 0) + '-' + Math.min(start + PER_PAGE, total) + ' dari ' + total) : 'Tidak ada hasil untuk "' + search.value.trim() + '"';
        if (pageInfo) pageInfo.textContent = 'Hal ' + page + ' / ' + pages;
        if (pager) {
            pager.innerHTML = '';
            function btn(label, p, disabled, active) {
                var li = document.createElement('li');
                li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                var a = document.createElement('a');
                a.className = 'page-link'; a.href = '#'; a.textContent = label;
                if (!disabled && !active) a.addEventListener('click', function (e) { e.preventDefault(); page = p; render(); });
                li.appendChild(a); pager.appendChild(li);
            }
            btn('‹', page - 1, page <= 1, false);
            for (var p = 1; p <= pages; p++) {
                if (pages > 7 && Math.abs(p - page) > 2 && p !== 1 && p !== pages) {
                    if (pager.lastChild && pager.lastChild.textContent !== '…') {
                        var li = document.createElement('li'); li.className = 'page-item disabled';
                        var s = document.createElement('span'); s.className = 'page-link'; s.textContent = '…';
                        li.appendChild(s); pager.appendChild(li);
                    }
                    continue;
                }
                btn(String(p), p, false, p === page);
            }
            btn('›', page + 1, page >= pages, false);
            pager.style.display = pages <= 1 ? 'none' : '';
        }
    }
    var t = null;
    if (search) search.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () {
            q = search.value.trim().toLowerCase();
            page = 1; render();
        }, 250);
    });
    render();
})();
</script>