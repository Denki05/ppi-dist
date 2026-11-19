@extends('superuser.app')

@push('styles')
<style>
  .hc-page {
    --hc-ink: #0f172a;
    --hc-muted: #64748b;
    --hc-line: #e2e8f0;
    --hc-soft: #f8fafc;
    --hc-primary: #4f46e5;
    --hc-primary-soft: #eef2ff;
    --hc-critical: #dc2626;
    --hc-critical-soft: #fef2f2;
    --hc-warning: #d97706;
    --hc-warning-soft: #fffbeb;
    --hc-info: #2563eb;
    --hc-info-soft: #eff6ff;
    --hc-ok: #059669;
    --hc-ok-soft: #ecfdf5;
    color: var(--hc-ink);
    padding-bottom: 24px;
  }
  .hc-page * { box-sizing: border-box; }

  .hc-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px; margin: 4px 0 14px; }
  .hc-head h1 { margin: 0; font: 600 21px/1.25 Poppins, Muli, sans-serif; letter-spacing: -.2px; }
  .hc-head .hc-sub { margin: 3px 0 0; color: var(--hc-muted); font-size: 12.5px; }
  .hc-crumb { font-size: 12px; color: var(--hc-muted); margin-bottom: 6px; }
  .hc-crumb a { color: var(--hc-muted); text-decoration: none; }
  .hc-crumb span { margin: 0 6px; opacity: .6; }

  .hc-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #fff; border: 1px solid var(--hc-line); color: var(--hc-muted); white-space: nowrap; }
  .hc-chip i { font-size: 12px; }
  .hc-chip.is-readonly { color: #475569; }
  .hc-chip.is-critical { background: var(--hc-critical-soft); border-color: #fecaca; color: var(--hc-critical); }
  .hc-chip.is-warning { background: var(--hc-warning-soft); border-color: #fde68a; color: var(--hc-warning); }

  .hc-panel { background: #fff; border: 1px solid var(--hc-line); border-radius: 14px; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); overflow: hidden; }
  .hc-panel-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 14px; border-bottom: 1px solid var(--hc-line); }

  .hc-tabs { display: inline-flex; background: #eef2f7; border-radius: 10px; padding: 3px; gap: 2px; }
  .hc-tabs a { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 7px; font-size: 12.5px; font-weight: 600; color: #475569; text-decoration: none; transition: all .15s ease; }
  .hc-tabs a:hover { color: var(--hc-ink); }
  .hc-tabs a.is-active { background: #fff; color: var(--hc-primary); box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
  .hc-tabs .hc-tab-count { display: inline-flex; min-width: 19px; height: 19px; align-items: center; justify-content: center; padding: 0 6px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #e2e8f0; color: #475569; }
  .hc-tabs a.is-active .hc-tab-count { background: var(--hc-primary-soft); color: var(--hc-primary); }
  .hc-tabs .hc-tab-count.is-danger { background: var(--hc-critical-soft); color: var(--hc-critical); }

  .hc-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 14px; border-bottom: 1px solid var(--hc-line); background: var(--hc-soft); }
  .hc-toolbar-left, .hc-toolbar-right { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
  .hc-note { font-size: 12px; color: var(--hc-muted); }
  .hc-note strong { color: #334155; }

  .hc-btn-run { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; border-radius: 9px; border: 0; color: #fff; font-weight: 600; font-size: 12.5px; background: linear-gradient(135deg, #4f46e5, #6366f1); box-shadow: 0 4px 12px rgba(79, 70, 229, .25); transition: transform .12s ease, box-shadow .12s ease; text-decoration: none; white-space: nowrap; }
  .hc-btn-run:hover { color: #fff; text-decoration: none; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, .32); }
  .hc-btn-run[aria-busy="true"] { opacity: .75; pointer-events: none; }

  .hc-seg { display: inline-flex; background: #fff; border: 1px solid var(--hc-line); border-radius: 9px; padding: 2px; gap: 2px; }
  .hc-seg a { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: var(--hc-muted); text-decoration: none; white-space: nowrap; }
  .hc-seg a:hover { color: var(--hc-ink); background: var(--hc-soft); }
  .hc-seg a.is-active { background: var(--hc-primary-soft); color: var(--hc-primary); }

  .hc-search { position: relative; }
  .hc-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12.5px; }
  .hc-search input { width: 240px; max-width: 100%; padding: 6px 10px 6px 30px; border: 1px solid var(--hc-line); border-radius: 8px; font-size: 12.5px; background: #fff; color: var(--hc-ink); outline: none; transition: border-color .15s ease, box-shadow .15s ease; }
  .hc-search input:focus { border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .15); }

  .hc-switch { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; color: #475569; cursor: pointer; user-select: none; margin: 0; }
  .hc-switch input { appearance: none; -webkit-appearance: none; width: 32px; height: 18px; border-radius: 999px; background: #cbd5e1; position: relative; cursor: pointer; transition: background .15s ease; margin: 0; flex: 0 0 auto; }
  .hc-switch input::after { content: ""; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; border-radius: 50%; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .25); transition: transform .15s ease; }
  .hc-switch input:checked { background: var(--hc-primary); }
  .hc-switch input:checked::after { transform: translateX(14px); }

  .hc-sev-filter { display: inline-flex; gap: 5px; }
  .hc-sev-btn { border: 1px solid var(--hc-line); background: #fff; color: var(--hc-muted); font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-radius: 999px; cursor: pointer; transition: all .15s ease; }
  .hc-sev-btn:hover { color: var(--hc-ink); }
  .hc-sev-btn.is-active[data-level="critical"] { background: var(--hc-critical-soft); border-color: #fecaca; color: var(--hc-critical); }
  .hc-sev-btn.is-active[data-level="warning"] { background: var(--hc-warning-soft); border-color: #fde68a; color: var(--hc-warning); }
  .hc-sev-btn.is-active[data-level="info"] { background: var(--hc-info-soft); border-color: #bfdbfe; color: var(--hc-info); }

  .hc-linkbtn { border: 0; background: none; color: var(--hc-primary); font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 3px 6px; border-radius: 6px; white-space: nowrap; }
  .hc-linkbtn:hover { background: var(--hc-primary-soft); }

  /* Daftar pemeriksaan: tabel padat, detail dibuka per baris */
  .hc-checks { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
  .hc-checks thead th { background: var(--hc-soft); color: var(--hc-muted); font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; padding: 8px 14px; border-bottom: 1px solid var(--hc-line); text-align: left; white-space: nowrap; }
  .hc-checks tbody td { border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  .hc-checks tbody tr:last-child td { border-bottom: 0; }

  .hc-check-row { cursor: pointer; }
  .hc-check-row > td { padding: 8px 14px; }
  .hc-check-row:hover > td { background: #fafcff; }
  .hc-check-row[aria-expanded="true"] > td { background: var(--hc-soft); }
  .hc-check-row.is-critical > td:first-child { box-shadow: inset 3px 0 0 var(--hc-critical); }
  .hc-check-row.is-warning > td:first-child { box-shadow: inset 3px 0 0 var(--hc-warning); }
  .hc-check-row.is-info > td:first-child { box-shadow: inset 3px 0 0 var(--hc-info); }
  .hc-check-row.is-ok > td:first-child { box-shadow: inset 3px 0 0 #34d399; }
  .hc-check-row:focus { outline: none; }
  .hc-check-row:focus-visible > td { background: var(--hc-primary-soft); }

  .hc-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .hc-check-status { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 7px; font-size: 13px; flex: 0 0 auto; }
  .hc-check-row.is-critical .hc-check-status { background: var(--hc-critical-soft); color: var(--hc-critical); }
  .hc-check-row.is-warning .hc-check-status { background: var(--hc-warning-soft); color: var(--hc-warning); }
  .hc-check-row.is-info .hc-check-status { background: var(--hc-info-soft); color: var(--hc-info); }
  .hc-check-row.is-ok .hc-check-status { background: var(--hc-ok-soft); color: var(--hc-ok); }
  .hc-check-title { font-weight: 600; font-size: 13px; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .hc-check-badges { display: inline-flex; flex-wrap: wrap; gap: 5px; }
  .hc-dur { font-size: 11.5px; color: #94a3b8; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .hc-caret { color: #94a3b8; font-size: 12px; transition: transform .15s ease; }
  .hc-check-row[aria-expanded="true"] .hc-caret { transform: rotate(180deg); }

  .hc-check-detail > td { padding: 0; background: #fff; border-bottom: 1px solid var(--hc-line); }
  .hc-check-body { border-top: 1px dashed var(--hc-line); background: #fff; }

  .hc-pill { display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: .2px; white-space: nowrap; }
  .hc-pill.critical { background: var(--hc-critical-soft); color: var(--hc-critical); }
  .hc-pill.warning { background: var(--hc-warning-soft); color: var(--hc-warning); }
  .hc-pill.info { background: var(--hc-info-soft); color: var(--hc-info); }
  .hc-pill.ok { background: var(--hc-ok-soft); color: var(--hc-ok); }

  .hc-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
  .hc-table thead th { position: sticky; top: 0; background: var(--hc-soft); color: var(--hc-muted); font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; padding: 8px 14px; border-bottom: 1px solid var(--hc-line); text-align: left; white-space: nowrap; }
  .hc-table tbody td { padding: 9px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
  .hc-table tbody tr:hover td { background: #fafcff; }
  .hc-table tbody tr:last-child td { border-bottom: 0; }
  .hc-find-table thead th { position: static; }
  .hc-find-table tbody td { vertical-align: middle; }
  .hc-msg { color: #334155; line-height: 1.45; }
  .hc-ref { display: inline-flex; align-items: center; gap: 6px; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 11.5px; color: #475569; background: var(--hc-soft); border: 1px dashed var(--hc-line); border-radius: 7px; padding: 2px 8px; max-width: 100%; word-break: break-word; }
  .hc-copy { border: 0; background: transparent; color: #94a3b8; padding: 0 2px; cursor: pointer; font-size: 12px; line-height: 1; }
  .hc-copy:hover { color: var(--hc-primary); }
  .hc-row-critical td:first-child { box-shadow: inset 3px 0 0 var(--hc-critical); }
  .hc-row-warning td:first-child { box-shadow: inset 3px 0 0 var(--hc-warning); }
  .hc-row-info td:first-child { box-shadow: inset 3px 0 0 var(--hc-info); }
  .hc-nofind { display: none; padding: 12px 14px; color: var(--hc-muted); font-size: 12.5px; }

  .hc-check-error { margin: 10px 14px; padding: 10px 12px; border-radius: 9px; background: var(--hc-critical-soft); border: 1px solid #fecaca; color: #991b1b; font-size: 12.5px; }
  .hc-trunc { padding: 9px 14px 12px; font-size: 12px; color: var(--hc-muted); }
  .hc-foot { padding: 9px 14px; border-top: 1px solid var(--hc-line); font-size: 12px; color: var(--hc-muted); background: #fff; }

  .hc-empty { text-align: center; padding: 46px 20px 50px; }
  .hc-empty-icon { width: 62px; height: 62px; margin: 0 auto 14px; border-radius: 18px; display: flex; align-items: center; justify-content: center; background: var(--hc-primary-soft); color: var(--hc-primary); font-size: 26px; }
  .hc-empty h3 { font-size: 15px; font-weight: 700; margin: 0 0 5px; }
  .hc-empty p { color: var(--hc-muted); font-size: 13px; margin: 0 auto 16px; max-width: 430px; }

  .hc-kv { display: grid; grid-template-columns: 120px 1fr; gap: 6px 12px; font-size: 12.5px; }
  .hc-kv dt { color: var(--hc-muted); font-weight: 600; margin: 0; }
  .hc-kv dd { margin: 0; color: #334155; word-break: break-word; }
  .hc-trace { margin: 10px 0 0; padding: 12px; border-radius: 10px; background: #0f172a; color: #e2e8f0; font-size: 11.5px; line-height: 1.55; max-height: 340px; overflow: auto; white-space: pre-wrap; word-break: break-word; }
  .hc-path { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 11.5px; color: #475569; word-break: break-word; line-height: 1.5; }

  .hc-err-detail-row > td { background: var(--hc-soft); padding: 14px 18px; }
  .hc-btn-xs { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 7px; font-size: 12px; font-weight: 600; border: 1px solid var(--hc-line); background: #fff; color: #475569; cursor: pointer; text-decoration: none; white-space: nowrap; }
  .hc-btn-xs:hover { color: var(--hc-primary); border-color: #c7d2fe; background: var(--hc-primary-soft); text-decoration: none; }
  .hc-btn-xs.is-primary { background: var(--hc-primary-soft); border-color: #c7d2fe; color: var(--hc-primary); }
  .hc-btn-xs.is-ok { background: var(--hc-ok-soft); border-color: #a7f3d0; color: var(--hc-ok); }

  .hc-loading-bar { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999; background: rgba(79, 70, 229, .15); display: none; overflow: hidden; }
  .hc-loading-bar.is-on { display: block; }
  .hc-loading-bar::before { content: ""; position: absolute; inset: 0; width: 38%; background: linear-gradient(90deg, transparent, #4f46e5, transparent); animation: hc-slide 1s infinite linear; }
  @keyframes hc-slide { from { transform: translateX(-100%); } to { transform: translateX(320%); } }

  .hc-page .pagination { margin: 14px 0 0; }
  .hc-page .pagination .page-link { color: #475569; border-color: var(--hc-line); font-size: 12.5px; padding: 5px 10px; }
  .hc-page .pagination .page-item.active .page-link { background: var(--hc-primary); border-color: var(--hc-primary); }

  @media (max-width: 767.98px) {
    .hc-search input { width: 100%; }
    .hc-toolbar, .hc-toolbar-left, .hc-toolbar-right { width: 100%; }
    .hc-checks th.is-dur, .hc-checks td.is-dur { display: none; }
    .hc-check-title { white-space: normal; }
    .hc-table thead { display: none; }
    .hc-table tbody td { display: block; padding: 7px 14px; border-bottom: 0; }
    .hc-table tbody tr { display: block; border-bottom: 1px solid #f1f5f9; padding: 6px 0; }
    .hc-table tbody td:first-child { box-shadow: none; }
  }
</style>
@endpush

@section('content')
@php
  $totalFindings = 0;
  $cleanChecks = 0;
  $totalChecks = 0;
  if ($result) {
      $totalChecks = count($result['checks']);
      foreach ($result['checks'] as $c) {
          $sum = array_sum($c['counts']);
          $totalFindings += $sum;
          if ($sum === 0) { $cleanChecks++; }
      }
  }
@endphp

<div class="hc-page">
  <div class="hc-head">
    <div>
      <div class="hc-crumb"><a href="{{ url('/superuser') }}">Zona Pengembang</a><span>/</span>Health Check</div>
      <h1>Health Check</h1>
      <p class="hc-sub">Pemindaian read-only untuk anomali data penjualan, stok, invoice, pembayaran, dan FAT.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center" style="gap:6px">
      @if($result)
        <span class="hc-chip"><i class="bi bi-clock-history"></i> {{ \Illuminate\Support\Carbon::parse($result['run_at'])->format('d/m/Y H:i') }}</span>
        <span class="hc-chip"><i class="bi bi-calendar-range"></i> {{ $result['since_days'] === 0 ? 'Semua transaksi' : $result['since_days'] . ' hari' }}</span>
        @if($result['summary']['critical'] > 0)
          <span class="hc-chip is-critical"><i class="bi bi-exclamation-octagon"></i> {{ $result['summary']['critical'] }} kritis</span>
        @endif
      @endif
      <span class="hc-chip is-readonly"><i class="bi bi-shield-check"></i> Read-only</span>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissable" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
      {{ session('success') }}
    </div>
  @endif

  <div class="hc-panel">
    <div class="hc-panel-head">
      <div class="hc-tabs">
        <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'since' => $sinceDays]) }}" class="{{ $tab === 'checks' ? 'is-active' : '' }}">
          <i class="bi bi-activity"></i> Anomali Data
          @if($result)
            <span class="hc-tab-count {{ $result['summary']['critical'] > 0 ? 'is-danger' : '' }}">{{ $totalFindings }}</span>
          @endif
        </a>
        <a href="{{ route('superuser.health_check.index', ['tab' => 'errors']) }}" class="{{ $tab === 'errors' ? 'is-active' : '' }}">
          <i class="bi bi-terminal"></i> Error Live
          <span class="hc-tab-count {{ $errorStats['unresolved'] > 0 ? 'is-danger' : '' }}">{{ $errorStats['unresolved'] }}</span>
        </a>
      </div>
      @if($tab === 'checks')
        <div class="d-flex flex-wrap align-items-center" style="gap:8px">
          <div class="hc-seg" role="group" aria-label="Rentang transaksi">
            @foreach([30 => '30 hari', 180 => '180 hari', 365 => '1 tahun', 0 => 'Semua'] as $days => $label)
              <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => $days]) }}" class="{{ (int) $sinceDays === $days ? 'is-active' : '' }}" title="Pindai ulang {{ $label }}">{{ $label }}</a>
            @endforeach
          </div>
          <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => $sinceDays]) }}" class="hc-btn-run" data-hc-run>
            <i class="bi bi-play-fill"></i> <span>{{ $result ? 'Pindai ulang' : 'Jalankan' }}</span>
          </a>
        </div>
      @else
        <span class="hc-chip"><i class="bi bi-list-check"></i> {{ $errors->total() }} error</span>
      @endif
    </div>

    @if($tab === 'checks')
      @if(!$result)
        <div class="hc-empty">
          <div class="hc-empty-icon"><i class="bi bi-clipboard2-pulse"></i></div>
          <h3>Belum ada hasil pemindaian</h3>
          <p>Jalankan pemeriksaan untuk mendeteksi qty, nominal, invoice, stok, status DO, revisi internal, pembayaran, dan FAT yang tidak konsisten.</p>
          <a href="{{ route('superuser.health_check.index', ['tab' => 'checks', 'run' => 1, 'since' => 180]) }}" class="hc-btn-run" data-hc-run>
            <i class="bi bi-play-fill"></i> <span>Jalankan sekarang (180 hari)</span>
          </a>
        </div>
      @else
        <div class="hc-toolbar">
          <div class="hc-toolbar-left">
            <span class="hc-note">
              <strong>{{ $totalFindings }}</strong> temuan · {{ $cleanChecks }}/{{ $totalChecks }} bersih · {{ number_format(array_sum(array_column($result['checks'], 'duration_ms')), 0, ',', '.') }} ms
            </span>
            <div class="hc-sev-filter">
              <button type="button" class="hc-sev-btn is-active" data-level="critical">Kritis</button>
              <button type="button" class="hc-sev-btn is-active" data-level="warning">Peringatan</button>
              <button type="button" class="hc-sev-btn is-active" data-level="info">Info</button>
            </div>
          </div>
          <div class="hc-toolbar-right">
            <label class="hc-switch">
              <input type="checkbox" id="hcOnlyFindings">
              <span>Hanya berisi temuan</span>
            </label>
            <div class="hc-search">
              <i class="bi bi-search"></i>
              <input type="text" id="hcSearch" placeholder="Cari pesan, kode DO, item..." autocomplete="off">
            </div>
            <button type="button" class="hc-linkbtn" id="hcToggleAll">Buka semua</button>
          </div>
        </div>

        <table class="hc-checks">
          <thead>
            <tr>
              <th>Pemeriksaan</th>
              <th style="width:250px">Temuan</th>
              <th class="is-dur" style="width:80px">Durasi</th>
              <th style="width:34px"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($result['checks'] as $check)
              @php
                $c = $check['counts'];
                $worst = $c['critical'] > 0 ? 'critical' : ($c['warning'] > 0 ? 'warning' : ($c['info'] > 0 ? 'info' : 'ok'));
                $sum = $c['critical'] + $c['warning'] + $c['info'];
                $hasError = !empty($check['error']);
              @endphp
              <tr class="hc-check-row is-{{ $hasError ? 'critical' : $worst }}"
                  data-check-toggle="{{ $check['key'] }}"
                  data-findings="{{ $sum }}"
                  tabindex="0"
                  role="button"
                  aria-expanded="false"
                  aria-controls="check-{{ $check['key'] }}">
                <td>
                  <div class="hc-cell">
                    <span class="hc-check-status">
                      @if($hasError)
                        <i class="bi bi-x-octagon"></i>
                      @elseif($worst === 'critical')
                        <i class="bi bi-exclamation-octagon"></i>
                      @elseif($worst === 'warning')
                        <i class="bi bi-exclamation-triangle"></i>
                      @elseif($worst === 'info')
                        <i class="bi bi-info-circle"></i>
                      @else
                        <i class="bi bi-check2-circle"></i>
                      @endif
                    </span>
                    <span class="hc-check-title">{{ $check['label'] }}</span>
                  </div>
                </td>
                <td>
                  <span class="hc-check-badges">
                    @if($hasError)
                      <span class="hc-pill critical">Gagal dijalankan</span>
                    @elseif($sum === 0)
                      <span class="hc-pill ok">Bersih</span>
                    @else
                      @if($c['critical'] > 0)<span class="hc-pill critical">{{ $c['critical'] }} kritis</span>@endif
                      @if($c['warning'] > 0)<span class="hc-pill warning">{{ $c['warning'] }} peringatan</span>@endif
                      @if($c['info'] > 0)<span class="hc-pill info">{{ $c['info'] }} info</span>@endif
                    @endif
                  </span>
                </td>
                <td class="is-dur"><span class="hc-dur">{{ $check['duration_ms'] }} ms</span></td>
                <td class="text-right"><i class="bi bi-chevron-down hc-caret"></i></td>
              </tr>
              <tr class="hc-check-detail" id="check-{{ $check['key'] }}" hidden>
                <td colspan="4">
                  <div class="hc-check-body">
                    @if($hasError)
                      <div class="hc-check-error">
                        <strong>Check gagal dijalankan.</strong> {{ $check['error'] }}
                      </div>
                    @endif

                    @if(!empty($check['findings']))
                      <table class="hc-table hc-find-table">
                        <thead>
                          <tr>
                            <th style="width:110px">Level</th>
                            <th>Pesan</th>
                            <th style="width:34%">Referensi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($check['findings'] as $finding)
                            <tr class="hc-find-row hc-row-{{ $finding['level'] }}" data-level="{{ $finding['level'] }}">
                              <td>
                                @if($finding['level'] === 'critical')
                                  <span class="hc-pill critical">Kritis</span>
                                @elseif($finding['level'] === 'warning')
                                  <span class="hc-pill warning">Peringatan</span>
                                @else
                                  <span class="hc-pill info">Info</span>
                                @endif
                              </td>
                              <td class="hc-msg">{{ $finding['message'] }}</td>
                              <td>
                                @if(!empty($finding['ref']))
                                  <span class="hc-ref">
                                    {{ $finding['ref'] }}
                                    <button type="button" class="hc-copy" data-copy="{{ $finding['ref'] }}" title="Salin referensi"><i class="bi bi-clipboard"></i></button>
                                  </span>
                                @else
                                  <span class="text-muted">—</span>
                                @endif
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                      <div class="hc-nofind">Tidak ada temuan yang cocok dengan filter.</div>
                      @if($check['truncated'])
                        <div class="hc-trunc"><i class="bi bi-scissors"></i> Daftar dipotong pada 50 temuan pertama. Sempitkan rentang waktu atau periksa lewat command.</div>
                      @endif
                    @elseif(!$hasError)
                      <div class="hc-trunc"><i class="bi bi-check2-circle" style="color:var(--hc-ok)"></i> Tidak ada temuan pada pemeriksaan ini.</div>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="hc-foot">
          <i class="bi bi-info-circle"></i> Klik baris untuk melihat detail temuan. Semua pemeriksaan hanya membaca data dan tidak mengubah apa pun.
        </div>
      @endif
    @else
      <div class="hc-toolbar" style="justify-content:space-between">
        <div class="hc-toolbar-left">
          <span class="hc-note">
            Exception 500/error PHP yang tertangkap otomatis. Notifikasi dikirim ke Developer maksimal 1x per 10 menit per jenis error.
          </span>
        </div>
        @if($errorStats['unresolved'] > 0)
          <form method="POST" action="{{ route('superuser.health_check.resolve_all') }}" onsubmit="return confirm('Tandai semua error sebagai selesai?')">
            @csrf
            <button type="submit" class="hc-btn-xs is-ok"><i class="bi bi-check2-all"></i> Tandai semua selesai</button>
          </form>
        @endif
      </div>

      @if($errors->count() === 0)
        <div class="hc-empty">
          <div class="hc-empty-icon" style="background:var(--hc-ok-soft);color:var(--hc-ok)"><i class="bi bi-shield-check"></i></div>
          <h3>Tidak ada error tercatat</h3>
          <p>Error aplikasi akan muncul di sini secara otomatis saat terjadi, beserta lokasi file dan trace-nya.</p>
        </div>
      @else
        <table class="hc-table">
          <thead>
            <tr>
              <th style="width:140px">Terakhir</th>
              <th>Error</th>
              <th style="width:22%">Lokasi</th>
              <th style="width:20%">URL</th>
              <th style="width:60px">Jumlah</th>
              <th style="width:90px">Status</th>
              <th style="width:130px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($errors as $error)
              <tr>
                <td>
                  <div style="font-size:12.5px;font-weight:600">{{ \Illuminate\Support\Carbon::parse($error->last_seen_at)->format('d/m/Y H:i') }}</div>
                  <div class="hc-dur">{{ \Illuminate\Support\Carbon::parse($error->last_seen_at)->diffForHumans() }}</div>
                </td>
                <td>
                  <div style="font-family:ui-monospace,Consolas,monospace;font-size:12px;color:#334155;font-weight:600">{{ $error->exception_class }}</div>
                  <div class="hc-msg" style="font-size:12.5px;margin-top:2px">{{ \Illuminate\Support\Str::limit($error->message, 150) }}</div>
                </td>
                <td class="hc-path">{{ $error->file }}:{{ $error->line }}</td>
                <td><span class="hc-dur">{{ \Illuminate\Support\Str::limit($error->url, 80) }}</span></td>
                <td><span class="hc-pill {{ $error->occurrences > 1 ? 'warning' : 'info' }}">{{ $error->occurrences }}x</span></td>
                <td>
                  @if($error->is_resolved)
                    <span class="hc-pill ok">Selesai</span>
                  @else
                    <span class="hc-pill critical">Baru</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex" style="gap:6px">
                    <button type="button" class="hc-btn-xs" data-err-toggle="err-{{ $error->id }}"><i class="bi bi-code-square"></i> Detail</button>
                    @if(!$error->is_resolved)
                      <form method="POST" action="{{ route('superuser.health_check.resolve', $error->id) }}">
                        @csrf
                        <button type="submit" class="hc-btn-xs is-primary"><i class="bi bi-check2"></i> Selesai</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
              <tr class="hc-err-detail-row" id="err-{{ $error->id }}" hidden>
                <td colspan="7">
                  <dl class="hc-kv">
                    <dt>Exception</dt>
                    <dd>{{ $error->exception_class }}</dd>
                    <dt>Pesan</dt>
                    <dd>{{ $error->message }}</dd>
                    <dt>Pertama</dt>
                    <dd>{{ $error->first_seen_at }}</dd>
                    <dt>Terakhir</dt>
                    <dd>{{ $error->last_seen_at }} ({{ $error->occurrences }}x)</dd>
                    <dt>User</dt>
                    <dd>{{ $error->user_label ?: '—' }} {{ $error->user_type ? '(' . $error->user_type . ' #' . $error->user_id . ')' : '' }}</dd>
                    <dt>Input</dt>
                    <dd>{{ $error->input_keys ?: '—' }}</dd>
                  </dl>
                  @if(!empty($error->trace))
                    <pre class="hc-trace">{{ $error->trace }}</pre>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        {{ $errors->appends(['tab' => 'errors'])->links() }}
      @endif
    @endif
  </div>
</div>

<div class="hc-loading-bar" id="hcLoadingBar"></div>
@endsection

@push('scripts')
<script>
(function () {
  var page = document.querySelector('.hc-page');
  if (!page) { return; }

  var loadingBar = document.getElementById('hcLoadingBar');
  page.querySelectorAll('[data-hc-run], .hc-seg a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (loadingBar) { loadingBar.classList.add('is-on'); }
      var runBtn = page.querySelector('[data-hc-run]');
      if (runBtn) {
        runBtn.setAttribute('aria-busy', 'true');
        var label = runBtn.querySelector('span');
        if (label) { label.textContent = 'Memindai...'; }
      }
    });
  });

  var search = document.getElementById('hcSearch');
  var filters = Array.prototype.slice.call(page.querySelectorAll('.hc-sev-btn'));
  var onlyFindings = document.getElementById('hcOnlyFindings');
  var checkRows = Array.prototype.slice.call(page.querySelectorAll('.hc-check-row'));

  function detailOf(row) {
    return document.getElementById('check-' + row.getAttribute('data-check-toggle'));
  }

  function setOpen(row, open) {
    var detail = detailOf(row);
    if (!detail) { return; }
    detail.hidden = !open;
    row.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function isOpen(row) {
    return row.getAttribute('aria-expanded') === 'true';
  }

  function applyFilters() {
    var term = (search && search.value || '').toLowerCase().trim();
    var levels = filters.filter(function (b) { return b.classList.contains('is-active'); })
      .map(function (b) { return b.getAttribute('data-level'); });
    var levelLimited = filters.length > 0 && levels.length < filters.length;

    checkRows.forEach(function (row) {
      var detail = detailOf(row);
      var rows = detail ? detail.querySelectorAll('.hc-find-row') : [];
      var visible = 0;
      rows.forEach(function (r) {
        var textOk = !term || r.textContent.toLowerCase().indexOf(term) !== -1;
        var levelOk = levels.indexOf(r.getAttribute('data-level')) !== -1;
        var show = textOk && levelOk;
        r.hidden = !show;
        if (show) { visible++; }
      });

      var noFind = detail ? detail.querySelector('.hc-nofind') : null;
      if (noFind) { noFind.style.display = (rows.length > 0 && visible === 0) ? 'block' : 'none'; }

      var count = parseInt(row.getAttribute('data-findings'), 10) || 0;
      var hide = false;
      if (onlyFindings && onlyFindings.checked && count === 0) { hide = true; }
      if ((term !== '' || levelLimited) && visible === 0) { hide = true; }
      row.style.display = hide ? 'none' : '';
      if (hide) { setOpen(row, false); }
      else if (term !== '' && visible > 0) { setOpen(row, true); }
    });
    updateToggleAllLabel();
  }

  checkRows.forEach(function (row) {
    row.addEventListener('click', function (e) {
      if (e.target.closest('a, button, form')) { return; }
      setOpen(row, !isOpen(row));
      updateToggleAllLabel();
    });
    row.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        setOpen(row, !isOpen(row));
        updateToggleAllLabel();
      }
    });
  });

  if (search) { search.addEventListener('input', applyFilters); }
  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.classList.toggle('is-active');
      applyFilters();
    });
  });
  if (onlyFindings) { onlyFindings.addEventListener('change', applyFilters); }

  var toggleAll = document.getElementById('hcToggleAll');
  function visibleRows() {
    return checkRows.filter(function (r) { return r.style.display !== 'none'; });
  }
  function updateToggleAllLabel() {
    if (!toggleAll) { return; }
    var rows = visibleRows();
    var anyClosed = rows.some(function (r) { return !isOpen(r); });
    toggleAll.textContent = anyClosed ? 'Buka semua' : 'Tutup semua';
  }
  if (toggleAll) {
    updateToggleAllLabel();
    toggleAll.addEventListener('click', function () {
      var rows = visibleRows();
      var anyClosed = rows.some(function (r) { return !isOpen(r); });
      rows.forEach(function (r) { setOpen(r, anyClosed); });
      toggleAll.textContent = anyClosed ? 'Tutup semua' : 'Buka semua';
    });
  }

  page.querySelectorAll('[data-err-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var row = document.getElementById(btn.getAttribute('data-err-toggle'));
      if (!row) { return; }
      row.hidden = !row.hidden;
      btn.classList.toggle('is-primary', !row.hidden);
    });
  });

  page.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy');
      var done = function () {
        var icon = btn.querySelector('i');
        if (icon) { icon.className = 'bi bi-check2'; }
        setTimeout(function () { if (icon) { icon.className = 'bi bi-clipboard'; } }, 1200);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done);
      } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(ta);
      }
    });
  });
})();
</script>
@endpush
