@push('styles')
<style>
  .enrich-wrap { max-width: 1100px; margin: 0 auto; }
  .enrich-head { margin: 2px 0 16px; }
  .enrich-head h5 { font-weight: 700; margin: 0; color: #2f3b4c; }
  .enrich-head small { color: #8a94a6; }

  .enrich-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
  .enrich-tab {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 14px; border-radius: 999px;
    border: 1px solid #dfe5ee; background: #fff; color: #5c6b77;
    font-size: .84rem; font-weight: 500; line-height: 1.2; transition: all .15s;
  }
  .enrich-tab:hover { background: #f4f7fc; border-color: #c9d7ea; color: #0665d0; text-decoration: none; }
  .enrich-tab.active { background: #0665d0; border-color: #0665d0; color: #fff; box-shadow: 0 3px 8px rgba(6,101,208,.22); }

  .enrich-card { background: #fff; border: 1px solid #e6eaf0; border-radius: 10px; box-shadow: 0 1px 2px rgba(20,30,50,.04); }
  .enrich-card + .enrich-card { margin-top: 14px; }
  .enrich-card-head { padding: 13px 16px; border-bottom: 1px solid #eef1f5; font-weight: 600; color: #2f3b4c; }

  .enrich-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 16px; border-bottom: 1px solid #eef1f5; }
  .enrich-toolbar .form-control { height: 34px; font-size: .85rem; }
  .enrich-search { flex: 1 1 240px; max-width: 340px; }
  .enrich-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-left: auto; }
  .enrich-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 999px; white-space: nowrap;
    background: #f5f7fb; border: 1px solid #e7ecf4; color: #6b7787;
    font-size: .75rem; font-weight: 500;
  }

  .enrich-pills { display: flex; gap: 6px; flex-wrap: wrap; }
  .enrich-pill {
    padding: 5px 12px; border-radius: 999px;
    border: 1px solid #dfe5ee; background: #fff; color: #5c6b77;
    font-size: .8rem; font-weight: 500;
  }
  .enrich-pill:hover { background: #f4f7fc; color: #0665d0; text-decoration: none; }
  .enrich-pill.active { background: #0665d0; border-color: #0665d0; color: #fff; }
  .enrich-pill .count { opacity: .72; margin-left: 4px; }

  .table.enrich-table { margin-bottom: 0; }
  .table.enrich-table thead th {
    background: #f8fafc; border-top: 0; border-bottom: 1px solid #edf1f6;
    font-size: .71rem; letter-spacing: .4px; text-transform: uppercase;
    color: #8a94a6; font-weight: 600; padding: 9px 12px; white-space: nowrap;
  }
  .table.enrich-table thead th,
  .table.enrich-table tbody td { text-align: left; }
  .table.enrich-table thead th.col-mid,
  .table.enrich-table tbody td.col-mid { text-align: center; }
  .table.enrich-table thead th.col-end,
  .table.enrich-table tbody td.col-end { text-align: right; }
  .table.enrich-table tbody td { padding: 9px 12px; font-size: .85rem; vertical-align: middle; white-space: normal; }
  .table.enrich-table tbody tr:hover { background: #fafbfe; }

  .enrich-name { font-weight: 600; color: #2f3b4c; line-height: 1.25; }
  .enrich-sub { font-size: .76rem; color: #8a94a6; line-height: 1.25; }
  .enrich-mono { font-family: SFMono-Regular, Menlo, Consolas, monospace; font-size: .78rem; color: #6b7787; }

  .enrich-empty { padding: 46px 16px; text-align: center; color: #8a94a6; font-size: .88rem; }
  .enrich-empty i { font-size: 26px; display: block; margin-bottom: 8px; opacity: .55; }

  .enrich-actionbar {
    position: sticky; bottom: 0; z-index: 5;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 11px 16px; background: rgba(255,255,255,.97);
    border-top: 1px solid #eef1f5; border-radius: 0 0 10px 10px;
  }
  .enrich-count { font-size: .85rem; color: #6b7787; }

  .badge-soft { display: inline-block; font-weight: 600; font-size: .7rem; padding: 3px 8px; border-radius: 999px; line-height: 1.3; }
  .badge-soft.gray  { background: #eef1f5; color: #65707f; }
  .badge-soft.green { background: #e6f7ee; color: #1c7c4a; }
  .badge-soft.amber { background: #fff5e0; color: #9a6b00; }
  .badge-soft.red   { background: #fdecec; color: #a52a2a; }

  .enrich-note { font-size: .78rem; color: #8a94a6; }

  @media (max-width: 767.98px) {
    .enrich-search { max-width: 100%; flex-basis: 100%; }
    .enrich-meta { margin-left: 0; }
  }

  @media (max-width: 991.98px) {
    /* navbar template fixed 48px tanpa offset konten di breakpoint ini */
    .enrich-wrap { padding-top: 52px; }
    .table.enrich-table { min-width: 640px; }
  }
</style>
@endpush

<div class="enrich-tabs">
  <a class="enrich-tab {{ $activeTab === 'produk' ? 'active' : '' }}" href="{{ route('superuser.enrichment.products') }}">
    <i class="fas fa-play"></i> Jalankan
  </a>
  <a class="enrich-tab {{ $activeTab === 'hasil' ? 'active' : '' }}" href="{{ route('superuser.enrichment.results') }}">
    <i class="fas fa-clipboard-check"></i> Hasil &amp; Verifikasi
  </a>
  <a class="enrich-tab {{ $activeTab === 'bandingkan' ? 'active' : '' }}" href="{{ route('superuser.enrichment.compare') }}">
    <i class="fas fa-code-compare"></i> Bandingkan
  </a>
  <a class="enrich-tab {{ $activeTab === 'pengaturan' ? 'active' : '' }}" href="{{ route('superuser.enrichment.settings.index') }}">
    <i class="fas fa-gear"></i> Pengaturan
  </a>
</div>
