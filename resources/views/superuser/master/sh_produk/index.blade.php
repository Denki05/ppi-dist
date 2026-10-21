@extends('superuser.app')

@push('styles')
<style>
:root{
  --sh-bg:#f4f6fb; --sh-card:#ffffff; --sh-border:#e8ecf3;
  --sh-ink:#1f2a37; --sh-muted:#8a94a6; --sh-primary:#4f46e5; --sh-primary2:#7c3aed;
  --sh-green:#10b981; --sh-amber:#f59e0b; --sh-red:#ef4444; --sh-blue:#3b82f6;
  --sh-shadow:0 8px 28px rgba(31,42,55,.08); --sh-radius:16px;
}
.sh-wrap{max-width:1120px;margin:0 auto;}
/* Penegasan hit-test modal halaman ini: netralisir konflik CSS Bootstrap ganda
   (plus-admin .modal-dialog{pointer-events:none} tanpa kompensasi saat CDN BS4 gagal). */
#modal-sumber, #modal-tinjau { pointer-events: auto; }
#modal-sumber .modal-dialog, #modal-tinjau .modal-dialog,
#modal-sumber .modal-content, #modal-tinjau .modal-content { pointer-events: auto; }
.sh-hero{background:linear-gradient(120deg,#312e81 0%,#4f46e5 45%,#7c3aed 100%);border-radius:20px;color:#fff;padding:20px 22px;box-shadow:var(--sh-shadow);position:relative;overflow:hidden;margin-bottom:14px;}
.sh-hero::after{content:'';position:absolute;right:-70px;top:-70px;width:260px;height:260px;background:radial-gradient(circle at center,rgba(255,255,255,.22),transparent 65%);pointer-events:none;}
.sh-hero::before{content:'';position:absolute;left:38%;bottom:-90px;width:320px;height:320px;background:radial-gradient(circle at center,rgba(255,255,255,.12),transparent 65%);pointer-events:none;}
.sh-hero h4{font-weight:800;letter-spacing:-.3px;margin:0;font-size:1.25rem;}
.sh-hero p{margin:2px 0 0;opacity:.82;font-size:.83rem;}
.sh-hero-actions{display:flex;gap:8px;flex-wrap:wrap;position:relative;z-index:1;}
.btn-sh{display:inline-flex;align-items:center;gap:7px;border-radius:12px;font-weight:700;font-size:.82rem;padding:9px 15px;border:1px solid transparent;transition:.16s;cursor:pointer;white-space:nowrap;}
.btn-sh-light{background:rgba(255,255,255,.16);color:#fff;border-color:rgba(255,255,255,.28);backdrop-filter:blur(6px);}
.btn-sh-light:hover{background:rgba(255,255,255,.26);color:#fff;}
.btn-sh-run{background:#fff;color:#4338ca;box-shadow:0 6px 16px rgba(0,0,0,.18);}
.btn-sh-run:hover{transform:translateY(-1px);box-shadow:0 10px 22px rgba(0,0,0,.22);color:#3730a3;}
.sh-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px;}
.sh-stat{background:var(--sh-card);border:1px solid var(--sh-border);border-radius:var(--sh-radius);padding:12px 14px;box-shadow:0 2px 10px rgba(31,42,55,.05);display:flex;gap:11px;align-items:center;position:relative;overflow:hidden;}
.sh-stat .ic{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;}
.sh-stat small{display:block;color:var(--sh-muted);font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.sh-stat strong{font-size:1.25rem;line-height:1;font-weight:800;color:var(--sh-ink);}
.sh-stat.total .ic{background:#eef2ff;color:#4f46e5;} .sh-stat.ok .ic{background:#dcfce7;color:#15803d;}
.sh-stat.warn .ic{background:#fef3c7;color:#b45309;} .sh-stat.bad .ic{background:#fee2e2;color:#b91c1c;}
.sh-progress{height:6px;background:#eef1f6;border-radius:99px;margin-top:8px;overflow:hidden;}
.sh-progress i{display:block;height:100%;background:linear-gradient(90deg,var(--sh-green),#34d399);border-radius:99px;transition:width .5s;}
.sh-card{background:var(--sh-card);border:1px solid var(--sh-border);border-radius:var(--sh-radius);box-shadow:0 2px 10px rgba(31,42,55,.05);overflow:hidden;}
.sh-card-head{padding:14px 16px 10px;border-bottom:1px solid var(--sh-border);}
.sh-seg{display:inline-flex;background:#eef1f6;border-radius:12px;padding:4px;gap:4px;}
.sh-seg a{padding:7px 16px;border-radius:9px;font-size:.82rem;font-weight:700;color:#5b6577;text-decoration:none;transition:.15s;white-space:nowrap;}
.sh-seg a:hover{color:var(--sh-ink);}
.sh-seg a.active{background:#fff;color:var(--sh-primary);box-shadow:0 2px 8px rgba(0,0,0,.1);}
.sh-seg a .cnt{font-weight:800;opacity:.75;font-size:.76rem;}
.sh-pills{display:flex;gap:7px;flex-wrap:wrap;align-items:center;}
.sh-pill{display:inline-flex;align-items:center;border:1px solid var(--sh-border);background:#fff;padding:5px 12px;border-radius:999px;font-size:.8rem;font-weight:600;color:#5b6577;cursor:pointer;transition:.15s;text-decoration:none;line-height:1.4;white-space:nowrap;}
.sh-pill:hover{border-color:#c7d2fe;color:var(--sh-primary);text-decoration:none;}
.sh-pill.active{background:var(--sh-ink);border-color:var(--sh-ink);color:#fff;}
.sh-pill .sh-ic{flex:none;margin-right:6px;}
.sh-pill .sh-lbl{white-space:nowrap;flex:none;}
.sh-pill .sh-cnt{flex:none;margin-left:7px;border-radius:999px;font-size:.7rem;font-weight:800;padding:1px 8px;line-height:1.5;display:inline-block;}
.sh-cnt-all{background:#eef2ff;color:#4f46e5;} .sh-cnt-ok{background:#dcfce7;color:#15803d;} .sh-cnt-warn{background:#fef3c7;color:#b45309;} .sh-cnt-bad{background:#fee2e2;color:#b91c1c;}
.sh-pill.active .sh-cnt{background:rgba(255,255,255,.22)!important;color:#fff!important;}
.sh-search{position:relative;min-width:230px;flex:1;max-width:320px;margin:0;}
.sh-search input{border-radius:12px!important;border:1.5px solid var(--sh-border)!important;padding:9px 38px 9px 36px!important;font-size:.84rem!important;background:#f8fafc!important;transition:.15s;width:100%;}
.sh-search input:focus{background:#fff!important;border-color:var(--sh-primary)!important;box-shadow:0 0 0 4px rgba(79,70,229,.12)!important;outline:none;}
.sh-search .lead{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9aa3b5;font-size:.85rem;pointer-events:none;}
.sh-search .spin{position:absolute;right:11px;top:50%;transform:translateY(-50%);display:none;color:var(--sh-primary);}
.sh-search.loading .spin{display:block;}
.sh-search .clr{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:#e5e7eb;width:20px;height:20px;border-radius:50%;font-size:.75rem;line-height:1;color:#4b5563;display:none;cursor:pointer;}
.sh-search.has-q .clr{display:block;} .sh-search.loading .clr{display:none;}
.sh-meta{font-size:.78rem;color:var(--sh-muted);}
.sh-table{width:100%;border-collapse:separate;border-spacing:0;}
.sh-table thead th{font-size:.7rem;text-transform:uppercase;letter-spacing:.6px;color:#8a94a6;font-weight:700;background:#f8fafc;padding:10px 12px;border-bottom:1px solid var(--sh-border);white-space:nowrap;position:sticky;top:0;z-index:2;}
.sh-table tbody td{padding:6px 10px!important;font-size:12.5px;line-height:1.4;border-bottom:1px solid #f1f4f9;vertical-align:middle;}
.sh-table tbody tr{transition:background .12s;}
.sh-table tbody tr:hover{background:#f8faff;}
.sh-table tbody tr:last-child td{border-bottom:none;}
.prod-line{display:flex;align-items:center;gap:6px;min-width:0;}
.prod-name{font-weight:700;color:var(--sh-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;}
.prod-code{color:var(--sh-muted);font-size:11.5px;white-space:nowrap;flex:none;}
.prod-prev{font-size:11px;flex:none;margin-left:2px;white-space:nowrap;}
.src-badge{font-size:.64rem;font-weight:800;padding:2px 7px;border-radius:6px;letter-spacing:.4px;display:inline-block;flex:none;}
.src-ID{background:#dcfce7;color:#15803d;} .src-SG{background:#dbeafe;color:#1d4ed8;}
.st{display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:999px;white-space:nowrap;}
.st::before{content:'';width:7px;height:7px;border-radius:50%;background:currentColor;flex:none;}
.st-ok{background:#dcfce7;color:#15803d;} .st-warn{background:#fef3c7;color:#92400e;} .st-bad{background:#fee2e2;color:#b91c1c;}
.match-one{display:inline-block;max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;font-size:12px;color:#374151;}
.match-key{color:var(--sh-muted);font-size:11px;white-space:nowrap;}
.hal-chip{font-size:.76rem;color:#4b5563;background:#f1f5f9;border-radius:8px;padding:3px 9px;white-space:nowrap;font-weight:600;}
.hal-chip.hal-est{background:#fffbeb;color:#92400e;border:1px dashed #f59e0b;}
.btn-tinjau{border:1.5px solid #e0e7ff!important;background:#eef2ff!important;color:#4338ca!important;font-weight:700!important;font-size:.76rem!important;border-radius:9px!important;padding:5px 13px!important;transition:.15s;}
.btn-tinjau:hover{background:#4338ca!important;color:#fff!important;border-color:#4338ca!important;}
.sh-skel td{padding:12px!important;}
.skel{display:block;height:14px;border-radius:7px;background:linear-gradient(90deg,#eef1f6 25%,#e2e8f0 50%,#eef1f6 75%);background-size:200% 100%;animation:sk 1.1s infinite;}
@keyframes sk{to{background-position:-200% 0;}}
.sh-empty{text-align:center;padding:44px 16px;color:var(--sh-muted);}
.sh-empty .big{width:64px;height:64px;border-radius:20px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 12px;}
.sh-pager{padding:12px 16px;border-top:1px solid var(--sh-border);background:#fafbfe;text-align:center;}
.sh-pager-nav{display:flex;align-items:center;justify-content:center;gap:6px;}
.pg-btn{border:1px solid var(--sh-border);background:#fff;border-radius:10px;padding:6px 14px;font-size:.8rem;font-weight:700;color:#374151;cursor:pointer;transition:.15s;}
.pg-btn:hover:not(:disabled){border-color:var(--sh-primary);color:var(--sh-primary);}
.pg-btn:disabled{opacity:.45;cursor:not-allowed;}
.pg-nav{min-width:36px;padding:6px 10px;border-radius:9px;font-size:.9rem;line-height:1.2;}
.sh-pageinfo{font-weight:800;font-size:.85rem;color:var(--sh-ink);min-width:64px;text-align:center;font-variant-numeric:tabular-nums;}
.sh-alert{border-radius:13px;border:1px solid;font-size:.83rem;padding:10px 14px;margin-bottom:12px;display:flex;gap:9px;align-items:flex-start;}
.sh-alert.ok{background:#ecfdf5;border-color:#a7f3d0;color:#065f46;} .sh-alert.err{background:#fef2f2;border-color:#fecaca;color:#991b1b;}
.sh-alert.warn{background:#fffbeb;border-color:#fde68a;color:#92400e;}
.sh-bulk{background:#eef2ff;border:1px dashed #a5b4fc;border-radius:12px;padding:9px 13px;margin-bottom:10px;display:none;align-items:center;gap:8px;flex-wrap:wrap;font-size:.82rem;}
.sh-bulk.show{display:flex;}
@keyframes shflash{0%{background:#fef9c3;}100%{background:transparent;}}
tr.sh-flash{animation:shflash 2.4s;}
#tinjau-hasil .list-group-item{cursor:pointer;}
#tinjau-hasil .list-group-item:hover{background:#eef2ff;}
.src-modal-sec{border:1px solid var(--sh-border);border-radius:13px;padding:13px 14px;margin-bottom:12px;background:#fafbfe;}
.src-modal-sec h6{font-weight:800;font-size:.85rem;margin:0 0 9px;display:flex;align-items:center;gap:7px;}
.upload-row{display:flex;gap:7px;flex-wrap:wrap;align-items:center;}
.upload-row input[type=file]{flex:1;min-width:150px;font-size:.78rem;}
.upload-row input[type=date],.upload-row input[type=url]{font-size:.78rem;}
.doc-hint{font-size:.75rem;color:var(--sh-muted);margin-top:6px;}
@media(max-width:820px){.sh-stats{grid-template-columns:1fr 1fr;}.sh-hero-actions{width:100%;}.sh-search{max-width:100%;flex-basis:100%;}}
@media(max-width:560px){.sh-stats{grid-template-columns:1fr 1fr;}.sh-table thead{display:none;}.sh-table,.sh-table tbody,.sh-table tr,.sh-table td{display:block;width:100%;}.sh-table tbody tr{border:1px solid var(--sh-border);border-radius:12px;margin:10px;padding:4px 6px;}.sh-table tbody td{border:none;padding:5px 10px!important;}}
</style>
@endpush

@section('content')
<div class="sh-wrap">

    @php
        $st = request('status', '');
        $sb = request('sumber', '');
        $cAll = ($stats['Cocok'] ?? 0) + ($stats['Perlu ditinjau'] ?? 0) + ($stats['Tidak cocok'] ?? 0);
        $cId = ($statsId['Cocok'] ?? 0) + ($statsId['Perlu ditinjau'] ?? 0) + ($statsId['Tidak cocok'] ?? 0);
        $cSg = ($statsSg['Cocok'] ?? 0) + ($statsSg['Perlu ditinjau'] ?? 0) + ($statsSg['Tidak cocok'] ?? 0);
        $cOk = $stats['Cocok'] ?? 0; $cWarn = $stats['Perlu ditinjau'] ?? 0; $cBad = $stats['Tidak cocok'] ?? 0;
        $pct = $cAll ? round($cOk / $cAll * 100) : 0;
        // URL asli tiap tab (fallback reload bila JS mati; JS akan cegah & hot-reload).
        $srcUrl = function ($s) { $p = []; if (request('status')) $p['status'] = request('status'); if (request('cari')) $p['cari'] = request('cari'); if ($s) $p['sumber'] = $s; return route('superuser.master.sh_produk.index', $p); };
        $tabUrl = function ($s) { $p = []; if ($s) $p['status'] = $s; if (request('sumber')) $p['sumber'] = request('sumber'); if (request('cari')) $p['cari'] = request('cari'); return route('superuser.master.sh_produk.index', $p); };
    @endphp

    {{-- HERO --}}
    <div class="sh-hero">
        <div class="d-flex flex-wrap align-items-center" style="gap:12px;position:relative;z-index:1;">
            <div style="flex:1;min-width:220px;">
                <h4><i class="fa-solid fa-certificate mr-2" style="opacity:.85;"></i>SH Produk</h4>
                <p>Sertifikasi Halal &bull; pencocokan master &harr; list halal &harr; PDF resmi &bull; <span id="sh-hero-total">{{ number_format($cAll) }}</span> produk terpantau &bull; {{ $pct }}% cocok</p>
            </div>
            <div class="sh-hero-actions">
                <button type="button" class="btn-sh btn-sh-light" data-toggle="modal" data-target="#modal-sumber">
                    <i class="fa-solid fa-database"></i>Kelola Sumber
                </button>
                <form action="{{ route('superuser.master.sh_produk.run') }}" method="POST" class="mb-0 d-inline">
                    @csrf
                    <button type="submit" class="btn-sh btn-sh-run" onclick="return confirm('Jalankan pencocokan ulang memakai dokumen terbaru?')">
                        <i class="fa fa-sync"></i>Jalankan Pencocokan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="sh-stats">
        <div class="sh-stat total"><div class="ic"><i class="fa-solid fa-layer-group"></i></div><div style="flex:1;"><small>Total Produk</small><strong data-stat="all">{{ number_format($cAll) }}</strong><div class="sh-progress"><i id="sh-bar" style="width:{{ $pct }}%"></i></div></div></div>
        <div class="sh-stat ok"><div class="ic"><i class="fa-solid fa-circle-check"></i></div><div><small>Cocok</small><strong data-stat="Cocok">{{ number_format($cOk) }}</strong><div class="prod-sub" id="sh-pct">{{ $pct }}% tampil di landing</div></div></div>
        <div class="sh-stat warn"><div class="ic"><i class="fa-solid fa-hourglass-half"></i></div><div><small>Perlu Ditinjau</small><strong data-stat="Perlu ditinjau">{{ number_format($cWarn) }}</strong><div class="prod-sub">menunggu keputusan</div></div></div>
        <div class="sh-stat bad"><div class="ic"><i class="fa-solid fa-circle-xmark"></i></div><div><small>Tidak Cocok</small><strong data-stat="Tidak cocok">{{ number_format($cBad) }}</strong><div class="prod-sub">disembunyikan</div></div></div>
    </div>

    @if(session('success'))<div class="sh-alert ok"><i class="fa fa-check-circle mt-1"></i><div>{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="sh-alert err"><i class="fa fa-exclamation-circle mt-1"></i><div>{{ session('error') }}</div></div>@endif
    @if($errors->any())<div class="sh-alert err"><i class="fa fa-exclamation-triangle mt-1"></i><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>@endif

    @if(!$petaConfirmed)
        <div class="sh-alert warn">
            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
            <div style="flex:1;"><strong>Aturan 5 belum dikonfirmasi:</strong> {{ $petaCount }} produk PPI FF / PPI NON FF dicocokkan ke IVA hanya berdasar pola kode &mdash; ditahan sebagai <em>Perlu ditinjau</em>.</div>
            <form action="{{ route('superuser.master.sh_produk.confirm_brand_map') }}" method="POST" class="mb-0">@csrf<button class="btn-sh" style="background:#f59e0b;color:#fff;padding:7px 13px;" onclick="return confirm('Pastikan produk PPI FF / PPI NON FF memang sama dengan produk IVA di sertifikat, lalu jalankan pencocokan ulang. Lanjut?')">Saya Konfirmasi PPI = IVA</button></form>
        </div>
    @endif

    {{-- MAIN CARD --}}
    <div class="sh-card">
        <div class="sh-card-head">
            <div class="d-flex flex-wrap align-items-center" style="gap:10px;">
                <div class="sh-seg" id="sh-src">
                    <a href="{{ $srcUrl('') }}" data-sumber="" class="{{ $sb===''?'active':'' }}">ID + SG <span class="cnt" data-srctotal="all">{{ number_format($cAll) }}</span></a>
                    <a href="{{ $srcUrl('ID') }}" data-sumber="ID" class="{{ $sb==='ID'?'active':'' }}">ID <span class="cnt" data-srctotal="ID">{{ number_format($cId) }}</span></a>
                    <a href="{{ $srcUrl('SG') }}" data-sumber="SG" class="{{ $sb==='SG'?'active':'' }}">SG <span class="cnt" data-srctotal="SG">{{ number_format($cSg) }}</span></a>
                </div>
                <form method="GET" action="{{ route('superuser.master.sh_produk.index') }}" class="sh-search" id="sh-searchbox">
                    <input type="hidden" name="status" value="{{ $st }}" id="sh-f-status">
                    <input type="hidden" name="sumber" value="{{ $sb }}" id="sh-f-sumber">
                    <span class="lead"><i class="fa fa-search"></i></span>
                    <input type="text" name="cari" id="sh-q" value="{{ request('cari') }}" placeholder="Cari kode / nama produk…" autocomplete="off">
                    <span class="spin"><i class="fa fa-spinner fa-spin"></i></span>
                    <button type="button" class="clr" id="sh-clear" title="Bersihkan">&times;</button>
                </form>
            </div>
            <div class="d-flex flex-wrap align-items-center mt-2" style="gap:10px;">
                <div class="sh-pills" id="sh-status">
                    <a class="sh-pill {{ $st===''?'active':'' }}" data-status="" href="{{ $tabUrl('') }}"><span class="sh-lbl">Semua</span><span class="sh-cnt sh-cnt-all" data-count="all">{{ number_format($cAll) }}</span></a>
                    <a class="sh-pill {{ $st==='Cocok'?'active':'' }}" data-status="Cocok" href="{{ $tabUrl('Cocok') }}"><i class="sh-ic fa-solid fa-circle-check" style="color:#10b981;"></i><span class="sh-lbl">Cocok</span><span class="sh-cnt sh-cnt-ok" data-count="Cocok">{{ number_format($cOk) }}</span></a>
                    <a class="sh-pill {{ $st==='Perlu ditinjau'?'active':'' }}" data-status="Perlu ditinjau" href="{{ $tabUrl('Perlu ditinjau') }}"><i class="sh-ic fa-solid fa-hourglass-half" style="color:#f59e0b;"></i><span class="sh-lbl">Tinjau</span><span class="sh-cnt sh-cnt-warn" data-count="Perlu ditinjau">{{ number_format($cWarn) }}</span></a>
                    <a class="sh-pill {{ $st==='Tidak cocok'?'active':'' }}" data-status="Tidak cocok" href="{{ $tabUrl('Tidak cocok') }}"><i class="sh-ic fa-solid fa-circle-xmark" style="color:#ef4444;"></i><span class="sh-lbl">Tidak cocok</span><span class="sh-cnt sh-cnt-bad" data-count="Tidak cocok">{{ number_format($cBad) }}</span></a>
                </div>
                <div class="sh-meta ml-auto" id="sh-meta"></div>
            </div>
            <div class="sh-bulk" id="sh-bulkbar">
                <strong><span id="sh-selcount">0</span> dipilih</strong>
                <span>Ubah ke:</span>
                <button type="button" class="pg-btn" data-bulk="Cocok" style="border-color:#10b981;color:#15803d;">Cocok</button>
                <button type="button" class="pg-btn" data-bulk="Perlu ditinjau" style="border-color:#f59e0b;color:#92400e;">Tinjau</button>
                <button type="button" class="pg-btn" data-bulk="Tidak cocok" style="border-color:#ef4444;color:#b91c1c;">Tidak</button>
                <input type="text" id="sh-bulk-note" class="form-control form-control-sm d-inline-block" style="width:200px;border-radius:9px;" placeholder="catatan (opsional)">
                <button type="button" class="btn btn-sm btn-link p-0" id="sh-selclear">batalkan</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="sh-table mb-0">
                <thead><tr>
                    <th style="width:36px;"><input type="checkbox" id="sh-checkall" title="Pilih semua di halaman ini"></th>
                    <th>Produk Master</th><th>List Halal</th><th>PDF</th><th>Status</th><th class="text-center">Aksi</th>
                </tr></thead>
                <tbody id="sh-tbody">
                    @forelse($rows as $r)
                    <tr data-id="{{ $r->id }}">
                        <td><input type="checkbox" class="sh-check" value="{{ $r->id }}"></td>
                        <td><div class="prod-line"><span class="src-badge src-{{ $r->sumber }}">{{ $r->sumber }}</span><span class="prod-name" title="{{ $r->nama_produk }}">{{ $r->nama_produk }}</span><span class="prod-code">{{ $r->kode_produk }}</span><a class="prod-prev" href="{{ url('/halal/produk/' . $r->id . '/preview') }}?v={{ $r->updated_at ? $r->updated_at->timestamp : time() }}" target="_blank" title="{{ $r->merek }} • {{ $r->kode_produk }}">PDF &nearr;</a></div></td>
                        <td>@if($r->nama_di_list_halal)<span class="match-one" title="{{ $r->nama_di_list_halal }} ({{ $r->kunci_yang_dipakai ?? '-' }})">{{ $r->nama_di_list_halal }} <span class="match-key">#{{ $r->nomor_urut_list_halal }} • {{ $r->kunci_yang_dipakai ?? '-' }}</span></span>@else<span class="text-muted">&mdash;</span>@endif</td>
                        <td>@if($r->halaman_pdf)@php $halTampil = $r->sumber === 'SG' ? $r->halaman_pdf : max(1, $r->halaman_pdf - 1); $halEst = $r->status_pdf !== 'Terverifikasi PDF'; @endphp<span class="hal-chip {{ $halEst ? 'hal-est' : '' }}" title="{{ $halEst ? 'Estimasi — belum terverifikasi PDF' : 'Terverifikasi PDF' }} • Nomor urut #{{ $r->nomor_urut_pdf }}">H.{{ $halTampil }}{{ $halEst ? '*' : '' }}</span>@else<span class="text-muted">&mdash;</span>@endif</td>
                        <td>@if($r->status_pencocokan=='Cocok')<span class="st st-ok" @if($r->catatan_tinjauan)title="{{ $r->catatan_tinjauan }}"@endif>Cocok</span>@elseif($r->status_pencocokan=='Perlu ditinjau')<span class="st st-warn" @if($r->catatan_tinjauan)title="{{ $r->catatan_tinjauan }}"@endif>Tinjau</span>@else<span class="st st-bad" @if($r->catatan_tinjauan)title="{{ $r->catatan_tinjauan }}"@endif>Tidak</span>@endif</td>
                        <td class="text-center"><button type="button" class="btn-tinjau" data-toggle="modal" data-target="#modal-tinjau" data-id="{{ $r->id }}" data-nama="{{ $r->nama_produk }}" data-kode="{{ $r->merek }} • {{ $r->kode_produk }}" data-status="{{ $r->status_pencocokan }}" data-catatan="{{ $r->catatan_tinjauan }}" data-sumber="{{ $r->sumber }}" data-list="{{ $r->nomor_urut_list_halal }}" data-namalist="{{ $r->nama_di_list_halal }}" data-hal="{{ $r->halaman_pdf ? ($r->sumber === 'SG' ? $r->halaman_pdf : max(1, $r->halaman_pdf - 1)) : '' }}">Tinjau</button></td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="sh-empty"><div class="big"><i class="fa-solid fa-box-open"></i></div><strong>Belum ada data</strong><div>Upload sumber lalu jalankan pencocokan.</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="sh-pager" id="sh-pager-wrap">
            <div class="sh-pager-nav">
                <button class="pg-btn pg-nav" id="sh-first" title="Halaman pertama">&laquo;</button>
                <button class="pg-btn pg-nav" id="sh-prev" title="Halaman sebelumnya">&lt;</button>
                <span class="sh-pageinfo" id="sh-pageinfo">1/{{ $rows->lastPage() }}</span>
                <button class="pg-btn pg-nav" id="sh-next" title="Halaman berikutnya">&gt;</button>
                <button class="pg-btn pg-nav" id="sh-last" title="Halaman terakhir">&raquo;</button>
            </div>
            <div class="sh-meta text-center w-100 mt-1"><span id="sh-meta"></span><span id="sh-totalmeta" class="ml-2"></span></div>
            <div id="sh-pager-links" style="display:none;">{{ $rows->links() }}</div>
        </div>
        <noscript><div class="p-2">{{ $rows->links() }}</div></noscript>

        <div class="px-3 pb-3">
            <a data-toggle="collapse" href="#riwayat-dok" role="button" aria-expanded="false" class="small">Riwayat dokumen sumber (klik untuk buka)</a>
            <div id="riwayat-dok" class="collapse mt-1">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Jenis</th><th>Nama File</th><th>Tgl Versi</th><th>Isi</th><th>Sertifikat</th><th>Diunggah</th></tr></thead>
                    <tbody>
                        @forelse($docs as $d)<tr><td><span class="badge badge-{{ $d->jenis == 'list' ? 'info' : 'danger' }}">{{ $d->jenis }}</span></td><td>{{ $d->original_name }}</td><td>{{ $d->tanggal_versi }}</td><td>{{ $d->total_baris }}</td><td><small>{{ $d->nomor_sertifikat ?? '-' }}</small></td><td><small>{{ $d->created_at }}</small></td></tr>
                        @empty<tr><td colspan="6" class="text-center text-muted">Belum ada dokumen.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
{{-- POPUP KELOLA SUMBER --}}
<div class="modal fade" id="modal-sumber" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius:18px;overflow:hidden;border:none;">
      <div class="modal-header" style="background:linear-gradient(120deg,#312e81,#7c3aed);color:#fff;border:none;padding:16px 20px;">
        <h5 class="modal-title" style="font-weight:800;font-size:1rem;"><i class="fa-solid fa-database mr-2"></i>Kelola Sumber Data Halal</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:.9;"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" style="padding:18px 20px;background:#f8fafc;">
        <div class="src-modal-sec" style="background:#fff;">
            <h6><span class="src-badge src-ID">ID</span> Indonesia — BPJPH</h6>
            <label class="small font-weight-bold mb-1">1 &bull; Excel List Halal</label>
            <form action="{{ route('superuser.master.sh_produk.upload_list') }}" method="POST" enctype="multipart/form-data" class="upload-row mb-2">
                @csrf
                <input type="file" name="file_list" accept=".xlsx,.xls" required class="form-control form-control-sm">
                <input type="date" name="tanggal_list" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm" style="max-width:150px;">
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius:9px;font-weight:700;">Upload</button>
            </form>
            <div class="doc-hint">{{ $docList ? $docList->original_name . ' • ' . $docList->total_baris . ' baris • ' . $docList->tanggal_versi : 'Belum ada file list.' }}</div>
            <hr class="my-2">
            <label class="small font-weight-bold mb-1">2 &bull; PDF Sertifikat Resmi</label>
            <form action="{{ route('superuser.master.sh_produk.upload_pdf') }}" method="POST" enctype="multipart/form-data" class="upload-row">
                @csrf
                <input type="file" name="file_pdf" accept=".pdf" required class="form-control form-control-sm">
                <input type="date" name="tanggal_pdf" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm" style="max-width:150px;">
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius:9px;font-weight:700;">Upload</button>
            </form>
            <div class="doc-hint">{{ $docPdf ? $docPdf->original_name . ' • ' . $docPdf->total_baris . ' produk • ' . $docPdf->nomor_sertifikat : 'Belum ada PDF.' }}</div>
        </div>
        <div class="src-modal-sec" style="background:#fff;">
            <h6><span class="src-badge src-SG">SG</span> Singapore — MUIS</h6>
            <label class="small font-weight-bold mb-1">1 &bull; Import QR MUIS (otomatis cocokkan)</label>
            <form action="{{ route('superuser.master.sh_produk.import_qr_sg') }}" method="POST" class="upload-row mb-2">
                @csrf
                <input type="url" name="qr_url" value="https://halal.muis.gov.sg/qr?n=DF7ag2NswkfuxRHTDo%2bQyz33Kq9RpbHpzJPKoNE5Dmw%3d" required class="form-control form-control-sm" style="flex:2;min-width:200px;">
                <input type="date" name="tanggal_list_sg" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm" style="max-width:150px;">
                <button type="submit" class="btn btn-sm btn-success" style="border-radius:9px;font-weight:700;">Import + Cocokkan</button>
            </form>
            <div class="doc-hint">{{ isset($docListSg) && $docListSg ? $docListSg->nomor_sertifikat . ' • ' . $docListSg->total_baris . ' produk • exp ' . ($docListSg->tanggal_kedaluwarsa ?? '-') : 'Belum ada data QR SG.' }}</div>
            <hr class="my-2">
            <label class="small font-weight-bold mb-1">2 &bull; PDF Annex A (opsional)</label>
            <div class="d-flex" style="gap:7px;flex-wrap:wrap;align-items:center;">
            <form action="{{ route('superuser.master.sh_produk.upload_pdf_sg') }}" method="POST" enctype="multipart/form-data" class="upload-row" style="flex:1;">
                @csrf
                <input type="file" name="file_pdf_sg" accept=".pdf" required class="form-control form-control-sm">
                <input type="date" name="tanggal_pdf_sg" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm" style="max-width:150px;">
                <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius:9px;font-weight:700;">Upload</button>
            </form>
            <form action="{{ route('superuser.master.sh_produk.run_sg') }}" method="POST" class="mb-0">@csrf<button type="submit" class="btn btn-sm btn-outline-success" style="border-radius:9px;font-weight:700;" onclick="return confirm('Jalankan pencocokan SG ulang?')">Run SG</button></form>
            <a href="{{ route('superuser.master.sh_produk.dokumen_sg') }}" target="_blank" class="btn btn-sm btn-outline-info" style="border-radius:9px;font-weight:700;">PDF Semua List (SG)</a>
            </div>
            <div class="doc-hint">{{ isset($docPdfSg) && $docPdfSg ? $docPdfSg->original_name . ' • ' . $docPdfSg->total_baris . ' produk • ' . ($docPdfSg->nomor_sertifikat ?? '-') : 'Belum ada PDF SG (opsional).' }}</div>
        </div>
      </div>
      <div class="modal-footer" style="border:none;background:#fff;">
        <button type="button" class="pg-btn" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<span id="doc-name-ID" class="d-none">{{ isset($docList) && $docList ? $docList->original_name . ' • ' . $docList->total_baris . ' baris' : 'belum ada dokumen list ID' }}</span><span id="doc-name-SG" class="d-none">{{ isset($docListSg) && $docListSg ? $docListSg->nomor_sertifikat . ' • ' . $docListSg->total_baris . ' produk' : 'belum ada dokumen list SG' }}</span>
<div class="modal fade" id="modal-tinjau" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="form-tinjau" method="POST" action="">
      @csrf
      <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
        <div class="modal-header py-2" style="background:#1f2a37;color:#fff;border:none;">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:800;">Tinjau Produk</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="mb-2"><strong id="tinjau-nama"></strong><br><small class="text-muted" id="tinjau-kode"></small></p>
          <div class="form-group mb-2">
            <label class="mb-1">Status</label>
            <select name="status_pencocokan" id="tinjau-status" class="form-control form-control-sm">
              <option value="Cocok">Cocok — tampil di landing</option>
              <option value="Perlu ditinjau">Perlu ditinjau — tombol ditahan</option>
              <option value="Tidak cocok">Tidak cocok — disembunyikan</option>
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="mb-1">Tautkan ke list halal <small class="text-muted">(opsional — no urut + halaman terisi otomatis)</small></label>
            <div id="tinjau-doc" class="small text-muted"></div>
            <div id="tinjau-cur" class="small text-muted mb-1">Belum terhubung ke list.</div>
            <input type="hidden" name="nomor_urut_list" id="tinjau-nomor" value="">
            <input type="hidden" name="lepas_tautan" id="tinjau-lepas-flag" value="">
            <div class="input-group input-group-sm">
              <input type="text" id="tinjau-cari" class="form-control form-control-sm" placeholder="ketik no / nama di list…" autocomplete="off">
              <div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="tinjau-lepas" title="Lepas tautan">×</button></div>
            </div>
            <div id="tinjau-hasil" class="list-group mt-1" style="max-height:150px;overflow:auto;display:none;"></div>
          </div>
          <div class="form-group mb-0">
            <label class="mb-1">Catatan</label>
            <input type="text" name="catatan_tinjauan" id="tinjau-catatan" class="form-control form-control-sm" placeholder="alasan / keputusan">
          </div>
        </div>
        <div class="modal-footer py-2" style="border:none;">
          <button type="button" class="btn btn-sm btn-secondary" style="border-radius:9px;" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-primary" id="tinjau-save" style="border-radius:9px;font-weight:700;">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
/* SH Produk hot-reload: vanilla JS + fetch (tanpa ketergantungan jQuery),
   supaya klik tab/sub-tab/search/pager tidak pernah full-reload. */
(function(){
  'use strict';
  var dataUrl = "{{ route('superuser.master.sh_produk.data') }}";
  var indexUrl = "{{ route('superuser.master.sh_produk.index') }}";
  var bulkUrl = "{{ route('superuser.master.sh_produk.bulk') }}";
  var reviewBase = "{{ url('superuser/master/sh_produk/review') }}";
  var listCariUrl = "{{ route('superuser.master.sh_produk.list_cari') }}";
  var metaCsrf = document.querySelector('meta[name="csrf-token"]');
  var csrf = metaCsrf ? metaCsrf.getAttribute('content') : "{{ csrf_token() }}";

  var state = {
    sumber: @json(request('sumber','')),
    status: @json(request('status','')),
    cari: @json(request('cari','')),
    page: {{ $rows->currentPage() }},
    last: {{ $rows->lastPage() }}
  };

  function $(s, c){ return (c||document).querySelector(s); }
  function $all(s, c){ return Array.prototype.slice.call((c||document).querySelectorAll(s)); }

  var tbody = $('#sh-tbody'), box = $('#sh-searchbox'), q = $('#sh-q'),
      meta = $('#sh-meta'), pageinfo = $('#sh-pageinfo'), totalmeta = $('#sh-totalmeta'),
      prevBtn = $('#sh-prev'), nextBtn = $('#sh-next'),
      firstBtn = $('#sh-first'), lastBtn = $('#sh-last'),
      fStatus = $('#sh-f-status'), fSumber = $('#sh-f-sumber');

  function paintPager(last){
    last = last || 1;
    if(pageinfo) pageinfo.textContent = state.page + '/' + last;
    if(prevBtn) prevBtn.disabled = state.page <= 1;
    if(nextBtn) nextBtn.disabled = state.page >= last;
    if(firstBtn) firstBtn.disabled = state.page <= 1;
    if(lastBtn) lastBtn.disabled = state.page >= last;
  }

  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function fmt(n){ try{ return Number(n||0).toLocaleString('id-ID'); }catch(e){ return String(n||0); } }
  function params(){
    var p = new URLSearchParams();
    if(state.sumber) p.set('sumber', state.sumber);
    if(state.status) p.set('status', state.status);
    if(state.cari) p.set('cari', state.cari);
    if(state.page > 1) p.set('page', state.page);
    return p;
  }
  function syncUrl(){
    var qs = params().toString();
    try{ window.history.replaceState({}, '', indexUrl + (qs ? ('?'+qs) : '')); }catch(e){}
    if(fStatus) fStatus.value = state.status; // fallback form tetap sinkron
    if(fSumber) fSumber.value = state.sumber;
  }
  function setActive(){
    $all('#sh-src a').forEach(function(a){
      var v = a.getAttribute('data-sumber') || '';
      if(v === state.sumber){ a.classList.add('active'); } else { a.classList.remove('active'); }
    });
    $all('#sh-status .sh-pill').forEach(function(a){
      var v = a.getAttribute('data-status') || '';
      if(v === state.status){ a.classList.add('active'); } else { a.classList.remove('active'); }
    });
    if(box) box.classList.toggle('has-q', !!(q && q.value));
  }
  function skeletons(){
    var h = '';
    for(var i=0;i<8;i++){ h += '<tr class="sh-skel"><td><span class="skel" style="width:16px;"></span></td><td><span class="skel" style="width:75%;"></span></td><td><span class="skel" style="width:85%;"></span></td><td><span class="skel" style="width:44px;"></span></td><td><span class="skel" style="width:64px;height:20px;border-radius:99px;"></span></td><td><span class="skel" style="width:64px;height:24px;margin:0 auto;"></span></td></tr>'; }
    tbody.innerHTML = h;
  }
  function statusBadge(s, note){
    var t = note ? ' title="'+esc(note)+'"' : '';
    if(s==='Cocok') return '<span class="st st-ok"'+t+'>Cocok</span>';
    if(s==='Perlu ditinjau') return '<span class="st st-warn"'+t+'>Tinjau</span>';
    return '<span class="st st-bad"'+t+'>Tidak</span>';
  }
  function rowHtml(r){
    var halTampil = r.sumber==='SG' ? r.halaman_pdf : Math.max(1, r.halaman_pdf-1);
    var halEst = r.status_pdf !== 'Terverifikasi PDF';
    var hal = r.halaman_pdf ? '<span class="hal-chip'+(halEst?' hal-est':'')+'" title="'+(halEst?'Estimasi \u2014 belum terverifikasi PDF':'Terverifikasi PDF')+' \u2022 Nomor urut #'+esc(r.nomor_urut_pdf)+'">H.'+esc(halTampil)+(halEst?'*':'')+'</span>' : '<span class="text-muted">&mdash;</span>';
    var lst = r.nama_di_list_halal ? '<span class="match-one" title="'+esc(r.nama_di_list_halal)+' ('+esc(r.kunci_yang_dipakai||'-')+')">'+esc(r.nama_di_list_halal)+' <span class="match-key">#'+esc(r.nomor_urut_list_halal)+' • '+esc(r.kunci_yang_dipakai||'-')+'</span></span>' : '<span class="text-muted">&mdash;</span>';
    return '<tr data-id="'+r.id+'">'
      +'<td><input type="checkbox" class="sh-check" value="'+r.id+'"></td>'
      +'<td><div class="prod-line"><span class="src-badge src-'+esc(r.sumber)+'">'+esc(r.sumber)+'</span><span class="prod-name" title="'+esc(r.nama_produk)+'">'+esc(r.nama_produk)+'</span><span class="prod-code">'+esc(r.kode_produk)+'</span><a class="prod-prev" href="'+esc(r.preview_url)+'" target="_blank" title="'+esc(r.merek)+' • '+esc(r.kode_produk)+'">PDF &nearr;</a></div></td>'
      +'<td>'+lst+'</td><td>'+hal+'</td><td>'+statusBadge(r.status_pencocokan, r.catatan_tinjauan)+'</td>'
      +'<td class="text-center"><button type="button" class="btn-tinjau" data-toggle="modal" data-target="#modal-tinjau" data-id="'+r.id+'" data-nama="'+esc(r.nama_produk)+'" data-kode="'+esc(r.merek)+' • '+esc(r.kode_produk)+'" data-status="'+esc(r.status_pencocokan)+'" data-catatan="'+esc(r.catatan_tinjauan||'')+'" data-sumber="'+esc(r.sumber)+'" data-list="'+(r.nomor_urut_list_halal||'')+'" data-namalist="'+esc(r.nama_di_list_halal||'')+'" data-hal="'+(r.halaman_pdf?(r.sumber==='SG'?r.halaman_pdf:Math.max(1,r.halaman_pdf-1)):'')+'">Tinjau</button></td></tr>';
  }
  function setText(sel, v){
    $all(sel).forEach(function(el){ el.textContent = v; });
  }
  function updateCounts(d){
    var sc = d.scoped || {};
    setText('[data-count="all"]', fmt((sc.Cocok||0)+(sc['Perlu ditinjau']||0)+(sc['Tidak cocok']||0)));
    setText('[data-count="Cocok"]', fmt(sc.Cocok||0));
    setText('[data-count="Perlu ditinjau"]', fmt(sc['Perlu ditinjau']||0));
    setText('[data-count="Tidak cocok"]', fmt(sc['Tidak cocok']||0));
    var g = d.stats||{}, t = (g.Cocok||0)+(g['Perlu ditinjau']||0)+(g['Tidak cocok']||0);
    setText('[data-stat="all"]', fmt(t));
    setText('[data-stat="Cocok"]', fmt(g.Cocok||0));
    setText('[data-stat="Perlu ditinjau"]', fmt(g['Perlu ditinjau']||0));
    setText('[data-stat="Tidak cocok"]', fmt(g['Tidak cocok']||0));
    var pct = t ? Math.round((g.Cocok||0)/t*100) : 0;
    var bar = $('#sh-bar'); if(bar) bar.style.width = pct+'%';
    var pctEl = $('#sh-pct'); if(pctEl) pctEl.textContent = pct+'% tampil di landing';
    var heroT = $('#sh-hero-total'); if(heroT) heroT.textContent = fmt(t);
    var ti=(d.statsId||{}), ts=(d.statsSg||{});
    setText('[data-srctotal="all"]', fmt(t));
    setText('[data-srctotal="ID"]', fmt((ti.Cocok||0)+(ti['Perlu ditinjau']||0)+(ti['Tidak cocok']||0)));
    setText('[data-srctotal="SG"]', fmt((ts.Cocok||0)+(ts['Perlu ditinjau']||0)+(ts['Tidak cocok']||0)));
  }

  var reqSeq = 0;
  function fetchData(opts){
    opts = opts || {};
    var my = ++reqSeq;
    if(box) box.classList.add('loading');
    if(!opts.silent) skeletons();
    syncUrl(); setActive();
    return fetch(dataUrl + '?' + params().toString(), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
      .then(function(res){ return res.json().then(function(d){ return {ok:res.ok, status:res.status, d:d}; }); })
      .then(function(w){
        if(my !== reqSeq) return;
        if(!w.ok){ throw {status:w.status, d:w.d}; }
        var d = w.d, html = '';
        if(d.rows && d.rows.length){ html = d.rows.map(rowHtml).join(''); }
        else { html = '<tr><td colspan="6"><div class="sh-empty"><div class="big"><i class="fa-solid fa-magnifying-glass"></i></div><strong>Tidak ada hasil</strong><div>Coba ubah kata kunci atau filter status / sumber.</div></div></td></tr>'; }
        tbody.innerHTML = html;
        refreshBulk();
        updateCounts(d);
        state.page = d.current_page || 1;
        state.last = d.last_page || 1;
        paintPager(state.last);
        var range = d.total ? ('Menampilkan '+(d.from||0)+'\u2013'+(d.to||0)+' dari '+fmt(d.total)+' data') : 'Tidak ada data';
        if(meta) meta.textContent = range;
        if(totalmeta) totalmeta.textContent = state.cari ? ('Hasil pencarian: \u201C'+state.cari+'\u201D') : '';
        syncUrl(); setActive();
      })
      .catch(function(err){
        if(my !== reqSeq) return;
        var msg = 'HTTP '+(err && err.status ? err.status : 'error');
        try{ if(err && err.d && err.d.error) msg = err.d.error; }catch(e){}
        tbody.innerHTML = '<tr><td colspan="6"><div class="sh-empty"><div class="big"><i class="fa-solid fa-triangle-exclamation"></i></div><strong>Gagal memuat</strong><div>'+esc(msg)+'</div></div></td></tr>';
      })
      .then(function(){ if(my === reqSeq && box) box.classList.remove('loading'); });
  }

  // Tab sumber: cegah pindah halaman, hot-reload via AJAX.
  var srcWrap = $('#sh-src');
  if(srcWrap) srcWrap.addEventListener('click', function(e){
    var a = e.target.closest ? e.target.closest('a[data-sumber]') : null;
    if(!a) return;
    e.preventDefault();
    state.sumber = a.getAttribute('data-sumber') || '';
    state.page = 1;
    fetchData();
  });
  // Sub-tab status: cegah pindah halaman, hot-reload via AJAX.
  var stWrap = $('#sh-status');
  if(stWrap) stWrap.addEventListener('click', function(e){
    var a = e.target.closest ? e.target.closest('.sh-pill') : null;
    if(!a) return;
    e.preventDefault();
    state.status = a.getAttribute('data-status') || '';
    state.page = 1;
    fetchData();
  });
  // Search: debounce + cegah submit reload.
  var deb = null;
  if(box) box.addEventListener('submit', function(e){
    e.preventDefault();
    if(deb) clearTimeout(deb);
    state.cari = q.value.trim(); state.page = 1;
    fetchData({silent:true});
  });
  if(q) q.addEventListener('input', function(){
    setActive();
    if(deb) clearTimeout(deb);
    deb = setTimeout(function(){ state.cari = q.value.trim(); state.page = 1; fetchData({silent:true}); }, 350);
  });
  var clr = $('#sh-clear');
  if(clr) clr.addEventListener('click', function(){ q.value=''; state.cari=''; state.page=1; fetchData(); q.focus(); });
  // Pager AJAX: << < 1/10 > >>.
  if(firstBtn) firstBtn.addEventListener('click', function(){ if(state.page>1){ state.page=1; fetchData(); } });
  if(prevBtn) prevBtn.addEventListener('click', function(){ if(state.page>1){ state.page--; fetchData(); } });
  if(nextBtn) nextBtn.addEventListener('click', function(){ if(state.page < state.last){ state.page++; fetchData(); } });
  if(lastBtn) lastBtn.addEventListener('click', function(){ if(state.page < state.last){ state.page = state.last; fetchData(); } });
  var pagerLinks = $('#sh-pager-links');
  if(pagerLinks) pagerLinks.addEventListener('click', function(e){
    var a = e.target.closest ? e.target.closest('a') : null;
    if(!a) return;
    e.preventDefault();
    var m = (a.getAttribute('href')||'').match(/[?&]page=(\d+)/);
    state.page = m ? parseInt(m[1],10) : 1;
    fetchData();
  });

  // Bulk select.
  function refreshBulk(){
    var checked = $all('.sh-check:checked').length;
    var total = $all('.sh-check').length;
    var sc = $('#sh-selcount'); if(sc) sc.textContent = checked;
    var bar = $('#sh-bulkbar'); if(bar) bar.classList.toggle('show', checked>0);
    var all = $('#sh-checkall'); if(all) all.checked = total>0 && checked===total;
  }
  if(tbody) tbody.addEventListener('change', function(e){
    if(e.target && e.target.classList && e.target.classList.contains('sh-check')) refreshBulk();
  });
  var checkAll = $('#sh-checkall');
  if(checkAll) checkAll.addEventListener('change', function(){
    $all('.sh-check').forEach(function(c){ c.checked = checkAll.checked; });
    refreshBulk();
  });
  var selClear = $('#sh-selclear');
  if(selClear) selClear.addEventListener('click', function(){
    $all('.sh-check').forEach(function(c){ c.checked = false; });
    refreshBulk();
  });
  $all('[data-bulk]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var ids = $all('.sh-check:checked').map(function(c){ return c.value; });
      if(!ids.length) return;
      btn.disabled = true;
      var body = new URLSearchParams();
      body.set('_token', csrf);
      ids.forEach(function(id){ body.append('ids[]', id); });
      body.set('status_pencocokan', btn.getAttribute('data-bulk'));
      var note = $('#sh-bulk-note'); body.set('catatan_tinjauan', note ? note.value : '');
      fetch(bulkUrl, {method:'POST', body:body, headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json().catch(function(){ return {ok:r.ok}; }); })
        .then(function(){ if(note) note.value=''; fetchData({silent:true}); })
        .catch(function(){ alert('Gagal menyimpan massal'); })
        .then(function(){ btn.disabled = false; });
    });
  });

  // Isi modal tinjau saat tombol diklik (delegasi; tidak mengganggu data-toggle Bootstrap).
  if(tbody) tbody.addEventListener('click', function(e){
    var b = e.target.closest ? e.target.closest('.btn-tinjau') : null;
    if(!b) return;
    var nm = $('#tinjau-nama'); if(nm) nm.textContent = b.getAttribute('data-nama') || '';
    var kd = $('#tinjau-kode'); if(kd) kd.textContent = b.getAttribute('data-kode') || '';
    var st = $('#tinjau-status'); if(st) st.value = b.getAttribute('data-status') || 'Perlu ditinjau';
    var ct = $('#tinjau-catatan'); if(ct) ct.value = b.getAttribute('data-catatan') || '';
    var rid = (b.getAttribute('data-id') || '').replace(/^\/+/, '');
    var f = $('#form-tinjau'); if(f) f.setAttribute('action', reviewBase.replace(/\/+$/, '') + '/' + encodeURIComponent(rid));
    tinjauLinkInit(b);
  });

  // Picker tautan list halal di modal Tinjau.
  var tinjauSumber = 'ID', tinjauSeq = 0, tinjauDeb = null;
  function tinjauCurLabel(){
    var cur = $('#tinjau-cur'), hid = $('#tinjau-nomor'), flag = $('#tinjau-lepas-flag');
    if(!cur) return;
    if(flag && flag.value === '1'){ cur.textContent = 'Tautan akan dilepas saat Simpan.'; return; }
    if(hid && hid.value){
      cur.textContent = 'Terpilih: #' + hid.value + (hid.getAttribute('data-nama') ? ' — ' + hid.getAttribute('data-nama') : '') + ' (tersimpan saat Simpan)';
    } else {
      var l = cur.getAttribute('data-cur');
      cur.textContent = l ? ('Terhubung: ' + l) : 'Belum terhubung ke list.';
    }
  }
  function tinjauLinkInit(b){
    tinjauSumber = b.getAttribute('data-sumber') || 'ID';
    var docEl = $('#doc-name-' + tinjauSumber), docBox = $('#tinjau-doc');
    if(docBox) docBox.textContent = 'Mencari di list ' + tinjauSumber + ': ' + (docEl ? docEl.textContent : '-');
    var hid = $('#tinjau-nomor'); if(hid){ hid.value = ''; hid.removeAttribute('data-nama'); }
    var flag = $('#tinjau-lepas-flag'); if(flag) flag.value = '';
    var nol = b.getAttribute('data-list') || '', nm = b.getAttribute('data-namalist') || '', hal = b.getAttribute('data-hal') || '';
    var cur = $('#tinjau-cur');
    if(cur) cur.setAttribute('data-cur', nol ? ('#' + nol + ' — ' + nm + (hal ? ' (H.' + hal + ')' : '')) : '');
    var cari = $('#tinjau-cari');
    if(cari){ cari.value = b.getAttribute('data-nama') || ''; }
    var box = $('#tinjau-hasil'); if(box){ box.style.display = 'none'; box.innerHTML = ''; }
    tinjauCurLabel();
    tinjauCari();
  }
  function tinjauCari(){
    var box = $('#tinjau-hasil'), cari = $('#tinjau-cari');
    if(!box || !cari) return;
    var my = ++tinjauSeq, qs = 'sumber=' + encodeURIComponent(tinjauSumber) + '&q=' + encodeURIComponent(cari.value.trim());
    box.innerHTML = '<div class="list-group-item py-1 px-2 text-muted" style="font-size:.8rem;">Mencari…</div>';
    box.style.display = 'block';
    fetch(listCariUrl + '?' + qs, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(rows){
        if(my !== tinjauSeq) return;
        if(!rows || !rows.length){
          box.innerHTML = '<div class="list-group-item py-1 px-2 text-muted" style="font-size:.8rem;">Tidak ditemukan di list ' + esc(tinjauSumber) + '. Coba nomor / kata kunci lain.</div>';
          box.style.display = 'block';
          return;
        }
        box.innerHTML = rows.map(function(it){
          return '<button type="button" class="list-group-item list-group-item-action py-1 px-2" style="font-size:.8rem;" data-nol="' + it.nomor_urut + '" data-nm="' + esc(it.nama_lengkap) + '"><strong>#' + it.nomor_urut + '</strong> — ' + esc(it.nama_lengkap) + '</button>';
        }).join('');
        box.style.display = 'block';
      })
      .catch(function(){
        if(my !== tinjauSeq) return;
        box.innerHTML = '<div class="list-group-item py-1 px-2 text-danger" style="font-size:.8rem;">Gagal mencari. Coba lagi.</div>';
        box.style.display = 'block';
      });
  }
  var tinjauCariInput = $('#tinjau-cari');
  if(tinjauCariInput) tinjauCariInput.addEventListener('input', function(){
    if(tinjauDeb) clearTimeout(tinjauDeb);
    tinjauDeb = setTimeout(tinjauCari, 300);
  });
  var tinjauHasil = $('#tinjau-hasil');
  if(tinjauHasil) tinjauHasil.addEventListener('click', function(e){
    var it = e.target.closest ? e.target.closest('[data-nol]') : null;
    if(!it) return;
    var hid = $('#tinjau-nomor');
    if(hid){ hid.value = it.getAttribute('data-nol'); hid.setAttribute('data-nama', it.getAttribute('data-nm') || ''); }
    var flag = $('#tinjau-lepas-flag'); if(flag) flag.value = '';
    tinjauHasil.style.display = 'none';
    tinjauCurLabel();
  });
  var tinjauLepas = $('#tinjau-lepas');
  if(tinjauLepas) tinjauLepas.addEventListener('click', function(){
    var hid = $('#tinjau-nomor'); if(hid){ hid.value = ''; hid.removeAttribute('data-nama'); }
    var flag = $('#tinjau-lepas-flag'); if(flag) flag.value = '1';
    var box = $('#tinjau-hasil'); if(box){ box.style.display = 'none'; }
    tinjauCurLabel();
  });
  // Simpan tinjauan via fetch agar tidak reload; fallback submit biasa bila gagal.
  var formT = $('#form-tinjau');
  if(formT) formT.addEventListener('submit', function(e){
    e.preventDefault();
    var url = formT.getAttribute('action');
    var saveBtn = $('#tinjau-save');
    if(saveBtn){ saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan…'; }
    fetch(url, {method:'POST', body:new URLSearchParams(new FormData(formT)), headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.text().then(function(t){ return {ok:r.ok, t:t}; }); })
      .then(function(w){
        var savedId = null, savedNo = null;
        try{ var j = JSON.parse(w.t); if(!w.ok && j.error) throw j; savedId = j.id || null; savedNo = j.nomor || null; }
        catch(err){ if(!w.ok) throw err; }
        hideTinjau();
        fetchData({silent:true}).then(function(){
          if(savedId && tbody){
            var tr = tbody.querySelector('tr[data-id="' + savedId + '"]');
            if(tr) tr.classList.add('sh-flash');
          }
          if(meta) meta.textContent = 'Tersimpan' + (savedNo ? ' — tertaut #' + savedNo : '') + '. ' + (meta.textContent || '');
        });
      })
      .catch(function(){
        formT.submit(); // fallback: submit biasa via DOM (tidak memicu listener submit)
      })
      .then(function(){ if(saveBtn){ saveBtn.disabled = false; saveBtn.textContent = 'Simpan'; } });
  });
  function hideTinjau(){
    try{
      if(window.jQuery && jQuery('#modal-tinjau').modal){ jQuery('#modal-tinjau').modal('hide'); return; }
    }catch(e){}
    var m = $('#modal-tinjau'); if(m) m.style.display = 'none';
    $all('.modal-backdrop').forEach(function(b){ b.parentNode.removeChild(b); });
    document.body.classList.remove('modal-open');
  }

  setActive();
  // Inisialisasi info pager dari render server.
  (function initPager(){
    var cur = window.location.search.match(/[?&]page=(\d+)/);
    if(cur) state.page = parseInt(cur[1],10);
    paintPager(state.last || state.page);
  })();
})();
</script>
@endpush
