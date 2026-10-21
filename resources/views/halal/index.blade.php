<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Halal Produk - LS Fragrance</title>
    <meta name="description" content="Cek dan lihat sertifikat halal resmi produk LS Fragrance (BPJPH Indonesia &amp; MUIS Singapore).">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' fill='%23146845'/%3E%3Ctext x='32' y='42' font-size='26' text-anchor='middle' fill='%23ffe9a8' font-family='Arial' font-weight='bold'%3E%D8%AD%3C/text%3E%3C/svg%3E">
    <!-- Halaman dev/internal. Versi production: halal-landing/index.html -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
    <style>
        :root {
            --green-900: #0b3d2e; --green-700: #146845; --green-500: #1d9e6b;
            --green-100: #e3f4ec; --gold: #c9a227; --ink: #1f2937; --muted: #6b7280;
            --line: #e5e7eb; --bg: #f3f7f5;
        }
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif; margin: 0; color: var(--ink); background: var(--bg); min-height: 100vh; display: flex; flex-direction: column; }
        a { color: var(--green-700); }
        .hero { background: linear-gradient(135deg, var(--green-900) 0%, var(--green-700) 60%, var(--green-500) 100%); color: #fff; padding: 44px 20px 74px; text-align: center; position: relative; overflow: hidden; }
        .hero::after { content: ""; position: absolute; left: 0; right: 0; bottom: -1px; height: 34px; background: var(--bg); border-radius: 50% 50% 0 0 / 100% 100% 0 0; }
        .hero h1 { margin: 6px 0 8px; font-size: clamp(26px, 5vw, 40px); font-weight: 800; }
        .hero p { margin: 0 auto; max-width: 620px; opacity: .93; font-size: 15px; }
        .hero .total { display: inline-block; margin-top: 16px; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.3); border-radius: 999px; padding: 7px 18px; font-size: 13.5px; font-weight: 700; }
        .hero .total b { color: var(--gold); }
        .container { width: 100%; max-width: 760px; margin: -34px auto 0; padding: 0 16px 48px; position: relative; z-index: 2; flex: 1; }
        .card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 12px 32px rgba(11,61,46,.12); }
        .card label { font-weight: 600; font-size: 14px; display: block; margin-bottom: 8px; }
        .field-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
        .field-head label { margin: 0; }
        .chips { display: flex; gap: 6px; flex-wrap: wrap; }
        .chip { border: 1.5px solid var(--green-500); background: #fff; color: var(--green-700); border-radius: 999px; padding: 5px 13px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit; transition: .12s; }
        .chip:hover { background: var(--green-100); }
        .chip.active { background: var(--green-700); color: #fff; border-color: var(--green-700); }
        #produk { width: 100%; }
        .select2-container .select2-selection--single { height: 48px; border-radius: 10px; border: 1.5px solid #d1d5db; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px; font-size: 15px; padding-left: 12px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; }
        .select2-container .select2-search__field { font-size: 15px; }
        .res-item { display: flex; align-items: center; gap: 10px; }
        .res-item .res-nm { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .res-item .res-sub { color: var(--muted); font-size: 12.5px; font-weight: 400; }
        .badge { flex-shrink: 0; font-size: 11px; font-weight: 800; letter-spacing: .4px; border-radius: 6px; padding: 3px 8px; white-space: nowrap; }
        .badge.id { background: var(--green-100); color: var(--green-700); }
        .badge.sg { background: #fef3c7; color: #92400e; }
        .status-line { min-height: 22px; margin-top: 10px; font-size: 14px; }
        .status-line.ok { color: var(--green-700); } .status-line.warn { color: #b45309; } .status-line.err { color: #b91c1c; }
        .btn-row { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
        .btn { border: none; border-radius: 10px; padding: 13px 26px; font-size: 15px; font-weight: 700; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: linear-gradient(135deg, var(--green-700), var(--green-500)); color: #fff; flex: 1; min-width: 200px; box-shadow: 0 6px 16px rgba(29,158,107,.35); }
        .btn-primary:hover { filter: brightness(1.06); }
        .btn-primary:disabled, .btn-primary.disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
        .btn-ghost { background: #fff; color: var(--green-700); border: 1.5px solid var(--green-500); }
        .btn-ghost:hover { background: var(--green-100); }
        #err { display: none; margin-top: 12px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 10px; padding: 10px 14px; font-size: 14px; }
        footer { text-align: center; color: var(--muted); font-size: 12.5px; padding: 22px 16px; }
        .hint { text-align: center; color: var(--muted); font-size: 12.5px; margin-top: 14px; }
        .modal-overlay { display: none; position: fixed; inset: 0; z-index: 50; background: rgba(11,30,24,.68); align-items: center; justify-content: center; padding: 16px; }
        .modal-overlay.open { display: flex; }
        .modal { background: #fff; border-radius: 16px; width: 100%; max-width: 1000px; max-height: 94vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 24px 64px rgba(0,0,0,.35); }
        .modal.fullscreen { max-width: 100%; height: 100%; max-height: 100%; border-radius: 0; }
        .modal-head { display: flex; align-items: center; gap: 12px; padding: 13px 18px; background: linear-gradient(135deg, var(--green-900), var(--green-700)); color: #fff; flex-wrap: wrap; }
        .modal-head .titles { flex: 1; min-width: 140px; }
        .modal-head .titles strong { display: block; font-size: 15.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .modal-head .titles small { opacity: .85; font-size: 12.5px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .modal-head button { background: rgba(255,255,255,.15); color: #fff; border: none; border-radius: 8px; height: 34px; padding: 0 12px; font-size: 13px; font-weight: 700; cursor: pointer; flex-shrink: 0; }
        .modal-head button.active { background: var(--gold); color: var(--green-900); }
        .modal-head .close { border-radius: 50%; width: 34px; padding: 0; font-size: 18px; }
        .modal-tabs { display: flex; gap: 3px; background: rgba(255,255,255,.15); border-radius: 9px; padding: 3px; flex-shrink: 0; }
        .mtab { background: transparent; color: #fff; border: none; border-radius: 7px; padding: 7px 14px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit; white-space: nowrap; }
        .mtab:hover { background: rgba(255,255,255,.14); }
        .mtab.active { background: var(--gold); color: var(--green-900); }
        .mtab[hidden] { display: none; }
        .frame-wrap { --crop: 920px; overflow: hidden; position: relative; background: #fff; }
        .frame-wrap iframe { width: 100%; height: 66vh; min-height: 420px; border: none; background: #fff; display: block; }
        .modal.fullscreen .frame-wrap iframe { height: calc(100vh - 140px); }
        /* Crop navbar + form pencarian BPJPH (iframe cross-origin tidak bisa di-scroll via JS). */
        .frame-wrap.cropped iframe { margin-top: calc(var(--crop) * -1); height: calc(66vh + var(--crop)); min-height: calc(420px + var(--crop)); }
        .modal.fullscreen .frame-wrap.cropped iframe { height: calc(100vh - 140px + var(--crop)); }
        @media (max-width: 640px) { .frame-wrap { --crop: 1400px; } }
        .frame-loading { display: none; padding: 30px; text-align: center; color: var(--muted); font-size: 14px; }
        .frame-loading.show { display: block; }
        .frame-loading .spin { display: inline-block; width: 22px; height: 22px; margin-right: 8px; vertical-align: -5px; border: 3px solid #d1fae5; border-top-color: var(--green-500); border-radius: 50%; animation: putar .8s linear infinite; }
        @keyframes putar { to { transform: rotate(360deg); } }
        .modal-foot { display: flex; gap: 10px; align-items: center; padding: 11px 18px; border-top: 1px solid var(--line); flex-wrap: wrap; font-size: 13px; color: var(--muted); }
        .modal-foot .spacer { flex: 1; }
        .modal-foot .btn { padding: 8px 14px; font-size: 13px; }
        .modal-foot a.btn-ghost { display: inline-block; text-decoration: none; }
        .crop-toggle { background: #eef7f1; color: var(--green-700); border: 1.5px solid var(--green-500); border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
        .crop-toggle.active { background: var(--green-700); color: #fff; }
        @media (max-width: 640px) {
            .container { padding: 0 12px 40px; }
            .card { padding: 16px; }
            .frame-wrap iframe { height: 66vh; min-height: 320px; }
        }
    </style>
</head>
<body>

<header class="hero">
    <h1>Cek Kehalalan Produk</h1>
    <p>Cari produk Anda di bawah, lalu lihat sertifikat halal resminya langsung di halaman ini.</p>
    <div class="total" id="heroTotal">Memuat data produk…</div>
</header>

<main class="container">
    <section class="card">
        <div class="field-head">
            <label for="produk">Nama produk</label>
            <div class="chips" id="sumberChips">
                <button class="chip active" type="button" data-sumber="ID">Indonesia</button>
                <button class="chip" type="button" data-sumber="SG">Singapura</button>
                <button class="chip" type="button" data-sumber="ALL">Semua</button>
            </div>
        </div>
        <select id="produk" placeholder="Ketik atau klik untuk pilih produk…"></select>
        <input type="hidden" id="sertifikat_id">
        <input type="hidden" id="produk_id">
        <input type="hidden" id="produk_nama">

        <div id="msg" class="status-line"></div>

        <div class="btn-row">
            <button id="btnLihat" class="btn btn-primary" disabled>Lihat PDF</button>
            <button id="btnBpjph" class="btn btn-ghost" type="button" style="display:none">Cek di BPJPH</button>
        </div>

        <div id="err"></div>
    </section>

    <p class="hint">Produk yang sama bisa punya 2 sertifikat halal (Indonesia &amp; Singapura) &mdash; pilih yang mau dilihat.</p>
</main>

<footer>
    Data sertifikasi bersumber dari BPJPH Indonesia &bull; MUIS Singapore<br>
    PT Kreasi Mewangikan Indonesia &bull; SENSES INTERNATIONAL PTE. LTD.
</footer>

<div id="modalOverlay" class="modal-overlay">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-head">
            <div class="titles">
                <strong id="modalTitle">Sertifikat Halal</strong>
                <small id="modalSub"></small>
            </div>
            <div class="modal-tabs" id="modalTabs">
                <button type="button" class="mtab active" data-mode="pdf">Cek PDF</button>
                <button type="button" class="mtab" data-mode="bpjph" id="mtabBpjph">Cek di BPJPH</button>
            </div>
            <button type="button" id="modalExpand" aria-label="Layar penuh">&#x26F6; Penuh</button>
            <button type="button" id="modalClose" class="close" aria-label="Tutup">&times;</button>
        </div>
        <div class="frame-wrap" id="frameWrap">
            <iframe id="pdfFrame" title="Preview sertifikat halal"></iframe>
        </div>
        <div id="frameLoading" class="frame-loading"><span class="spin"></span>Memuat PDF sertifikat…</div>
        <div class="modal-foot">
            <span id="modalInfo"></span>
            <span class="spacer"></span>
            <button id="cropToggle" class="crop-toggle active" type="button" style="display:none">Fokus: Tabel ✓</button>
            <a id="modalNewTab" class="btn btn-ghost" href="#" target="_blank" rel="noopener">Buka di tab baru</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<script>
(function () {
    'use strict';

    var API_BASE = '{{ rtrim(url('/api'), '/') }}';
    var LIST_URL = API_BASE + '/halal/produk';
    var MAX_SUGGESTI = 60;

    var errBox = document.getElementById('err');
    function showErr(t) { errBox.textContent = t; errBox.style.display = 'block'; }
    function hideErr() { errBox.style.display = 'none'; }

    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
        showErr('Gagal memuat Select2 (butuh internet). Muat ulang halaman.');
        return;
    }

    var msg = document.getElementById('msg');
    var sertHidden = document.getElementById('sertifikat_id');
    var produkHidden = document.getElementById('produk_id');
    var namaHidden = document.getElementById('produk_nama');
    var btnLihat = document.getElementById('btnLihat');
    var btnBpjph = document.getElementById('btnBpjph');
    var elChips = document.getElementById('sumberChips');
    var elHeroTotal = document.getElementById('heroTotal');

        var overlay = document.getElementById('modalOverlay');
        var modalBox = document.querySelector('#modalOverlay .modal');
        var frameWrap = document.getElementById('frameWrap');
        var frame = document.getElementById('pdfFrame');
        var frameLoading = document.getElementById('frameLoading');
        var elModalTitle = document.getElementById('modalTitle');
        var elModalSub = document.getElementById('modalSub');
        var elModalInfo = document.getElementById('modalInfo');
        var elModalNewTab = document.getElementById('modalNewTab');
        var elModalTabs = document.getElementById('modalTabs');
        var elTabBpjph = document.getElementById('mtabBpjph');
        var cropToggle = document.getElementById('cropToggle');

        var state = { sumber: 'ID', selected: null, detailToken: 0, frameTimer: null };
        // Modal punya 2 tampilan: PDF internal & halaman BPJPH (iframe).
        var modalSrc = { mode: 'pdf', pdf: null, bpjph: null };

    function setMsg(text, cls) {
        msg.textContent = text;
        msg.className = 'status-line' + (cls ? ' ' + cls : '');
    }

    // Badge sumber SH. Objek jQuery (bukan string HTML) karena Select2
    // 4.1.0-rc.0 meng-escape string pada templateResult.
    function badgeEl(sumber) {
        return $('<span class="badge ' + (sumber === 'SG' ? 'sg' : 'id') + '"></span>')
            .text(sumber === 'SG' ? 'SG' : 'ID');
    }

    function reset() {
        state.selected = null;
        sertHidden.value = '';
        produkHidden.value = '';
        namaHidden.value = '';
        btnLihat.disabled = true;
        btnLihat.classList.add('disabled');
        btnBpjph.style.display = 'none';
        setMsg('');
    }

    /* ---------------- Select2 ---------------- */

    jQuery('#produk').select2({
        placeholder: 'Ketik atau klik untuk pilih produk…',
        allowClear: true,
        minimumInputLength: 0,
        width: '100%',
        delay: 300,
        language: {
            noResults: function () { return 'Produk tidak ditemukan. Coba kata kunci lain.'; },
            searching: function () { return 'Mencari…'; },
            errorLoading: function () { return 'Tidak dapat memuat data.'; }
        },
        ajax: {
            url: LIST_URL,
            dataType: 'json',
            data: function (params) {
                return { q: params.term || '', sumber: state.sumber, per_page: MAX_SUGGESTI };
            },
            processResults: function (payload) {
                hideErr();
                var data = (payload && payload.data) || [];
                var meta = (payload && payload.meta) || {};
                // Urut: nama produk A-Z, satu produk dgn 2 SH -> ID tampil dulu.
                data.sort(function (a, b) {
                    var na = (a.nama_produk || '').toLowerCase();
                    var nb = (b.nama_produk || '').toLowerCase();
                    if (na !== nb) { return na < nb ? -1 : 1; }
                    return (a.sumber === 'SG' ? 1 : 0) - (b.sumber === 'SG' ? 1 : 0);
                });
                if (meta.total_cocok) {
                    elHeroTotal.innerHTML = '';
                    elHeroTotal.appendChild(document.createTextNode('Total '));
                    var b = document.createElement('b');
                    b.textContent = String(meta.total_cocok);
                    elHeroTotal.appendChild(b);
                    elHeroTotal.appendChild(document.createTextNode(' produk terdaftar halal'));
                }
                return {
                    results: data.map(function (r) {
                        return {
                            id: r.id,
                            text: r.nama_produk + ' — ' + r.merek + ' ' + r.kode
                                + (r.sumber === 'SG' ? ' [SG]' : ' [ID]'),
                            _row: r
                        };
                    })
                };
            },
            error: function (jqXHR, textStatus) {
                if (textStatus === 'abort') return; // ketikan baru membatalkan request lama: bukan error
                showErr('Tidak dapat menghubungi server. Coba lagi.');
            }
        },
        templateResult: function (item) {
            var r = item._row || {};
            if (!r.id) return item.text;
            var $wrap = $('<div>');
            var $top = $('<div class="res-item">');
            $top.append($('<span class="res-nm"></span>').text(r.nama_produk || ''));
            $top.append(badgeEl(r.sumber));
            $wrap.append($top);
            var sub = (r.merek || '-') + ' · ' + (r.kode || '-');
            if (r.sertifikat_id) sub += ' · Sertifikat ' + r.sertifikat_id;
            var $sub = $('<div class="res-item"><span class="res-sub"></span></div>');
            $sub.find('.res-sub').text(sub);
            $wrap.append($sub);
            return $wrap;
        },
        // Teks terpilih: polos (Select2 meng-escape hasilnya).
        templateSelection: function (item) {
            var r = item._row || {};
            if (!r.id) return item.text;
            return (r.nama_produk || '') + ' [' + (r.sumber === 'SG' ? 'SG' : 'ID') + ']';
        }
    });

    jQuery('#produk').on('select2:select', function (e) {
        var item = (e.params && e.params.data) || {};
        var r = item._row;
        if (!r || !r.id) { reset(); return; }
        state.selected = r;
        hideErr();
        sertHidden.value = r.sertifikat_id || '';
        produkHidden.value = r.id;
        namaHidden.value = r.nama_produk || r.nama || '';

        if (r.button_active) {
            btnLihat.disabled = false;
            btnLihat.classList.remove('disabled');
            setMsg('Produk terdaftar halal. Silakan tekan "Lihat PDF".', 'ok');
        } else {
            btnLihat.disabled = true;
            btnLihat.classList.add('disabled');
            setMsg('Produk ini belum bisa ditampilkan sertifikatnya.', 'warn');
        }

        if (r.bpjph_url) {
            btnBpjph.style.display = '';
        } else {
            btnBpjph.style.display = 'none';
        }
    });

    jQuery('#produk').on('select2:clear select2:unselect', reset);

    elChips.addEventListener('click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        var chips = elChips.querySelectorAll('.chip');
        for (var i = 0; i < chips.length; i++) { chips[i].classList.remove('active'); }
        chip.classList.add('active');
        state.sumber = chip.getAttribute('data-sumber') || '';
        jQuery('#produk').select2('open');
    });

    /* ---------------- Preview PDF / BPJPH ---------------- */

    /**
     * Ganti tampilan di dalam modal.
     * mode 'pdf'   -> iframe PDF sertifikat (romouflan internal)
     * mode 'bpjph' -> iframe halaman resmi BPJPH (dengan crop navbar)
     */
    function setMode(mode) {
        if (mode === 'bpjph' && !modalSrc.bpjph) { mode = 'pdf'; }
        modalSrc.mode = mode;

        var tabs = elModalTabs.querySelectorAll('.mtab');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.toggle('active', tabs[i].getAttribute('data-mode') === mode);
        }

        if (mode === 'bpjph') {
            frameWrap.classList.add('cropped');
            cropToggle.style.display = '';
            elModalNewTab.href = modalSrc.bpjph;
            frameLoading.innerHTML = '<span class="spin"></span>Memuat halaman BPJPH…';
            frame.src = modalSrc.bpjph;
        } else {
            frameWrap.classList.remove('cropped');
            cropToggle.style.display = 'none';
            elModalNewTab.href = modalSrc.pdf || '#';
            frameLoading.innerHTML = '<span class="spin"></span>Memuat PDF sertifikat…';
            frame.src = modalSrc.pdf;
        }
        frameLoading.classList.add('show');
    }

    function openPdf(row, mode) {
        var token = ++state.detailToken;
        elModalTitle.textContent = 'Sertifikat Halal — ' + (row.nama_produk || row.nama);
        elModalSub.textContent = (row.merek || '-') + ' · ' + (row.kode || '-');
        elModalInfo.textContent = 'Memuat detail…';

        modalSrc.pdf = row.preview_url;
        modalSrc.bpjph = row.bpjph_url || null;
        // Tab BPJPH hanya untuk produk Indonesia (produk SG tidak ada di BPJPH).
        elTabBpjph.hidden = !modalSrc.bpjph;
        var wantMode = mode || 'pdf';

        overlay.classList.add('open');

        fetch(LIST_URL + '/' + row.id, { headers: { 'Accept': 'application/json' } })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (d) {
                if (token !== state.detailToken) return;
                if (!d.button_active) {
                    elModalInfo.textContent = 'Produk ini belum bisa ditampilkan sertifikatnya.';
                    return;
                }
                var halTxt = (d.halaman_tampil !== null && d.halaman_tampil !== undefined)
                    ? d.halaman_tampil : (d.halaman_pdf || '-');
                if (d.halaman_estimasi) halTxt += '* (estimasi)';
                elModalInfo.textContent =
                    '[' + d.sumber + ' • ' + (d.lembaga || '-') + ']  No. list ' + (d.nomor_urut_list || '-') +
                    '  •  Hal. SH ' + halTxt +
                    '  •  Sertifikat ' + (d.sertifikat_id || '-');
                if (d.preview_url) modalSrc.pdf = d.preview_url;
                if (d.bpjph_url) { modalSrc.bpjph = d.bpjph_url; elTabBpjph.hidden = false; }
                setMode(wantMode);
            })
            .catch(function () {
                if (token !== state.detailToken) return;
                elModalInfo.textContent = '';
                setMode(wantMode);
            });
    }

    function closeModal() {
        state.detailToken++;
        if (state.frameTimer) { clearTimeout(state.frameTimer); state.frameTimer = null; }
        frameLoading.classList.remove('show');
        overlay.classList.remove('open');
        frameWrap.classList.remove('cropped');
        frame.removeAttribute('src');
    }

    btnLihat.addEventListener('click', function () {
        if (!state.selected) return;
        openPdf(state.selected, 'pdf');
    });

    // Tombol "Cek di BPJPH" -> popup modal langsung mode BPJPH (bukan tab baru)
    btnBpjph.addEventListener('click', function () {
        if (!state.selected) return;
        openPdf(state.selected, 'bpjph');
    });

    // Tab di dalam modal: Cek PDF <-> Cek di BPJPH
    elModalTabs.addEventListener('click', function (e) {
        var tab = e.target.closest('.mtab');
        if (!tab) return;
        setMode(tab.getAttribute('data-mode'));
    });

    // Crop navbar BPJPH (iframe cross-origin tidak bisa di-scroll via JS)
    cropToggle.addEventListener('click', function () {
        var on = frameWrap.classList.toggle('cropped');
        cropToggle.classList.toggle('active', on);
        cropToggle.textContent = on ? 'Fokus: Tabel ✓' : 'Fokus: Tabel';
    });

    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('modalExpand').addEventListener('click', function () {
        var on = modalBox.classList.toggle('fullscreen');
        this.classList.toggle('active', on);
    });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
    frame.addEventListener('load', function () {
        if (state.frameTimer) { clearTimeout(state.frameTimer); state.frameTimer = null; }
        frameLoading.classList.remove('show');
    });

    jQuery('#produk').select2('open', false);
})();
</script>
</body>
</html>