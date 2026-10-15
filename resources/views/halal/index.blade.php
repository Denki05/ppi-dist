<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Halal Produk - LS Fragrance</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23146845'/%3E%3Ctext x='32' y='42' font-size='26' text-anchor='middle' fill='%23ffe9a8' font-family='Arial' font-weight='bold'%3E%D8%AD%3C/text%3E%3C/svg%3E">
    <!-- Halaman dev/internal. Versi production: halal-landing/index.html -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
    <style>
        :root {
            --green-900: #0b3d2e; --green-700: #146845; --green-500: #1d9e6b;
            --green-100: #e3f4ec; --gold: #c9a227; --ink: #1f2937; --muted: #6b7280; --bg: #f3f7f5;
        }
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif; margin: 0; color: var(--ink); background: var(--bg); min-height: 100vh; display: flex; flex-direction: column; }
        .hero { background: linear-gradient(135deg, var(--green-900) 0%, var(--green-700) 60%, var(--green-500) 100%); color: #fff; padding: 44px 20px 70px; text-align: center; position: relative; overflow: hidden; }
        .hero::after { content: ""; position: absolute; left: 0; right: 0; bottom: -1px; height: 34px; background: var(--bg); border-radius: 50% 50% 0 0 / 100% 100% 0 0; }
        .hero h1 { margin: 6px 0 6px; font-size: clamp(26px, 5vw, 40px); font-weight: 800; }
        .hero p { margin: 0 auto; max-width: 560px; opacity: .92; font-size: 15px; }
        .container { width: 100%; max-width: 720px; margin: -34px auto 0; padding: 0 16px 40px; position: relative; z-index: 2; flex: 1; }
        .card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 12px 32px rgba(11, 61, 46, .12); }
        .card label { font-weight: 600; font-size: 14px; display: block; margin-bottom: 8px; }
        #produk { width: 100%; }
        .select2-container .select2-selection--single { height: 48px; border-radius: 10px; border: 1.5px solid #d1d5db; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px; font-size: 15px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; }
        .badge-cocok { color: var(--green-700); font-weight: 700; }
        .badge-tinjau { color: #b45309; font-weight: 700; }
        .badge-tidak { color: #b91c1c; font-weight: 700; }
        .status-line { min-height: 22px; margin-top: 10px; font-size: 14px; }
        .status-line.ok { color: var(--green-700); } .status-line.warn { color: #b45309; } .status-line.err { color: #b91c1c; }
        .btn-row { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
        button.primary { background: linear-gradient(135deg, var(--green-700), var(--green-500)); color: #fff; border: none; border-radius: 10px; padding: 13px 26px; font-size: 15px; font-weight: 700; cursor: pointer; flex: 1; min-width: 200px; box-shadow: 0 6px 16px rgba(29, 158, 107, .35); }
        button.primary:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
        .product-chip, .badge-cocok, .badge-tinjau, .badge-tidak { display: none; }
        #err { display: none; margin-top: 12px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 10px; padding: 10px 14px; font-size: 14px; }
        footer { text-align: center; color: var(--muted); font-size: 12.5px; padding: 18px; }
        .modal-overlay { display: none; position: fixed; inset: 0; z-index: 50; background: rgba(11, 30, 24, .65); align-items: center; justify-content: center; padding: 16px; }
        .modal-overlay.open { display: flex; }
        .modal { background: #fff; border-radius: 16px; width: 100%; max-width: 1100px; max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 24px 64px rgba(0, 0, 0, .35); }
        .modal.fullscreen { max-width: 100%; height: 100%; max-height: 100%; border-radius: 0; }
        .modal.fullscreen iframe { height: calc(100vh - 150px); }
        .modal-head { display: flex; align-items: center; gap: 12px; padding: 14px 18px; background: linear-gradient(135deg, var(--green-900), var(--green-700)); color: #fff; }
        .modal-head .titles { flex: 1; min-width: 0; }
        .modal-head .titles strong { display: block; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .modal-head .titles small { opacity: .85; }
        .modal-close { background: rgba(255, 255, 255, .15); color: #fff; border: none; border-radius: 50%; width: 34px; height: 34px; font-size: 18px; cursor: pointer; flex-shrink: 0; }
        .modal-expand { background: rgba(255,255,255,.15); color: #fff; border: none; border-radius: 8px; height: 34px; padding: 0 12px; font-size: 13px; font-weight: 700; cursor: pointer; flex-shrink: 0; }
        .modal-expand.active { background: var(--gold); color: var(--green-900); }
        .modal iframe { width: 100%; height: 62vh; min-height: 380px; border: none; background: #fff; }
        .frame-loading { display: none; padding: 26px; text-align: center; color: var(--muted); font-size: 14px; }
        .frame-loading.show { display: block; }
        .frame-loading .spin { display: inline-block; width: 22px; height: 22px; margin-right: 8px; vertical-align: -5px; border: 3px solid #d1fae5; border-top-color: var(--green-500); border-radius: 50%; animation: putar 0.8s linear infinite; }
        @keyframes putar { to { transform: rotate(360deg); } }
        .modal-foot { display: flex; gap: 10px; align-items: center; padding: 12px 18px; border-top: 1px solid #e5e7eb; flex-wrap: wrap; font-size: 13px; color: var(--muted); }
        .modal-foot .spacer { flex: 1; }
        a.btn-ghost { display: inline-block; text-decoration: none; color: var(--green-700); font-weight: 700; border: 1.5px solid var(--green-500); border-radius: 8px; padding: 8px 16px; font-size: 14px; }
    </style>
</head>
<body>

<header class="hero">
    <h1>Cek Kehalalan Produk</h1>
    <p>Pilih produk di bawah, lalu lihat preview sertifikat halal (ID / SG) langsung di halaman ini.</p>
</header>

<main class="container">
    <section class="card">
        <label for="produk">Nama produk</label>
        <select id="produk"></select>
        <input type="hidden" id="sertifikat_id">
        <input type="hidden" id="produk_id">
        <input type="hidden" id="produk_nama">

        <div id="msg" class="status-line"></div>

        <div class="btn-row">
            <button id="btnLanjut" class="primary" disabled>Cari</button>
        </div>
        <div id="err"></div>
    </section>
</main>

<footer>Data sertifikasi bersumber dari BPJPH Indonesia &bull; MUIS Singapore &bull; PT Kreasi Mewangikan Indonesia / SENSES</footer>

<div id="modalOverlay" class="modal-overlay">
    <div class="modal" role="dialog" aria-modal="true">
        <div class="modal-head">
            <div class="titles">
                <strong id="modalTitle">Sertifikat Halal</strong>
                <small id="modalSub"></small>
            </div>
            <button class="modal-expand" id="modalExpand" aria-label="Layar penuh">&#x26F6; Penuh</button>
            <button class="modal-close" id="modalClose" aria-label="Tutup">&times;</button>
        </div>
        <iframe id="bpjphFrame" title="Preview sertifikat halal"></iframe>
        <div id="frameLoading" class="frame-loading"><span class="spin"></span>Memuat preview sertifikat…</div>
        <div class="modal-foot">
            <span id="modalInfo"></span>
            <span class="spacer"></span>
            <a id="modalNewTab" class="btn-ghost" href="#" target="_blank" rel="noopener">Buka di tab baru</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<script>
(function () {
    var errBox = document.getElementById('err');
    function showErr(t) { errBox.textContent = t; errBox.style.display = 'block'; }
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
        showErr('Gagal memuat Select2 (butuh internet). Muat ulang halaman.');
        return;
    }

    var btn = document.getElementById('btnLanjut');
    var msg = document.getElementById('msg');
    var sertHidden = document.getElementById('sertifikat_id');
    var produkHidden = document.getElementById('produk_id');
    var namaHidden = document.getElementById('produk_nama');
    var overlay = document.getElementById('modalOverlay');
    var frame = document.getElementById('bpjphFrame');
    var frameLoading = document.getElementById('frameLoading');
    var selected = null;
    var detailToken = 0;
    var frameTimer = null;
    var detailToken = 0;
    var frameTimer = null;

    function setMsg(text, cls) { msg.textContent = text; msg.className = 'status-line' + (cls ? ' ' + cls : ''); }
    function reset() {
        selected = null; sertHidden.value = ''; produkHidden.value = ''; namaHidden.value = '';
        btn.disabled = true; setMsg('');
    }

    jQuery('#produk').select2({
        placeholder: 'Ketik atau klik untuk pilih produk…',
        allowClear: true, minimumInputLength: 0, width: '100%', delay: 300,
        language: {
            noResults: function () { return 'Produk tidak ditemukan. Coba kata kunci lain.'; },
            searching: function () { return 'Mencari…'; },
            errorLoading: function () { return 'Tidak dapat memuat data.'; }
        },
        language: {
            noResults: function () { return 'Produk tidak ditemukan. Coba kata kunci lain.'; },
            searching: function () { return 'Mencari…'; },
            errorLoading: function () { return 'Tidak dapat memuat data.'; }
        },
        ajax: {
            url: '/halal/search', dataType: 'json',
            data: function (params) { return { q: params.term || '' }; },
            processResults: function (data) {
                errBox.style.display = 'none';
                return { results: (data || []).map(function (r) {
                    return { id: r.id, text: r.nama, _row: r };
                }) };
            },
            error: function (jqXHR, textStatus) {
                if (textStatus === 'abort') return; // ketikan baru membatalkan request lama: bukan error
                showErr('Tidak dapat menghubungi server. Coba lagi.');
            }
        },
        templateResult: function (item) {
            return item.text;
        }
    });

    jQuery('#produk').on('select2:select', function (e) {
        var item = (e.params && e.params.data) || {};
        var r = item._row;
        if (!r || !r.id) { reset(); return; }
        selected = r; errBox.style.display = 'none';
        sertHidden.value = r.sertifikat_id || ''; produkHidden.value = r.id; namaHidden.value = r.nama;
        if (r.button_active) { btn.disabled = false; setMsg('Produk terdaftar halal. Silakan tekan Cari.', 'ok'); }
        else if (r.status === 'Perlu ditinjau') { btn.disabled = true; setMsg('Produk perlu ditinjau tim, tombol ditahan.', 'warn'); }
        else { btn.disabled = true; setMsg('Belum ditemukan di daftar halal.', 'err'); }
    });
    jQuery('#produk').on('select2:clear select2:unselect', reset);

    function closeModal() {
        detailToken++;
        if (frameTimer) { clearTimeout(frameTimer); frameTimer = null; }
        frameLoading.classList.remove('show');
        overlay.classList.remove('open');
        frame.removeAttribute('src');
    }
    document.getElementById('modalClose').addEventListener('click', closeModal);
    var modalBox = document.querySelector('#modalOverlay .modal');
    var expandBtn = document.getElementById('modalExpand');
    expandBtn.addEventListener('click', function () {
        var on = modalBox.classList.toggle('fullscreen');
        expandBtn.classList.toggle('active', on);
    });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    frame.addEventListener('load', function () {
        if (frameTimer) { clearTimeout(frameTimer); frameTimer = null; }
        frameLoading.classList.remove('show');
    });

    btn.addEventListener('click', function () {
        if (!selected) return;
        var myToken = ++detailToken;
        btn.disabled = true;
        setMsg('Memuat detail produk…', '');
        fetch('/halal/produk/' + selected.id)
            .then(function (res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
            .then(function (d) {
                if (myToken !== detailToken) return; // abaikan respons basi
                btn.disabled = false;
                if (!d.button_active) { setMsg('Produk ini belum bisa ditampilkan sertifikatnya.', 'warn'); return; }
                var src = d.preview_url;
                var lembaga = d.sumber === 'SG' ? 'MUIS Singapore' : 'BPJPH Indonesia';
                document.getElementById('modalTitle').textContent = 'Sertifikat Halal (' + d.sumber + ') — ' + (d.nama_produk || d.nama);
                document.getElementById('modalSub').textContent = d.merek + ' - ' + d.kode;
                document.getElementById('modalInfo').textContent =
                    '[' + d.sumber + ' • ' + lembaga + '] No. list ' + (d.nomor_urut_list || '-') + '  •  Hal. PDF ' + (d.halaman_pdf || '-') +
                    '  •  Sertifikat ' + (d.sertifikat_id || '-');
                document.getElementById('modalNewTab').href = src;
                frameLoading.classList.add('show');
                if (frameTimer) clearTimeout(frameTimer);
                frameTimer = setTimeout(function () {
                    if (myToken !== detailToken) return;
                    frameLoading.classList.remove('show');
                    showErr('Preview lambat. Silakan "Buka di tab baru".');
                }, 25000);
                frame.src = src;
                overlay.classList.add('open');
            })
            .catch(function () {
                if (myToken !== detailToken) return;
                btn.disabled = false;
                showErr('Tidak dapat memuat detail produk. Coba lagi.');
            });
    });
})();
</script>
</body>
</html>
