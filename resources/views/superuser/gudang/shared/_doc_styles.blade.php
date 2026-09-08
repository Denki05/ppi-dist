  {{-- CSS bersama dokumen gudang (PO + receiving): layout 2 kolom, tab, tabel, stepper, toggle. Dipakai via @include di dalam tag <style>. --}}
    /* ===== PO Detail — two column layout ===== */
    :root {
      --po-ink: #1c2733;
      --po-muted: #7a8494;
      --po-line: #e6eaf1;
      --po-bg-soft: #f5f7fb;
      --po-accent: #3d6fd1;
      --po-accent-soft: #eaf1fd;
      --po-amber: #e0a326;
      --po-amber-soft: #fdf3e0;
      --po-green: #2fa85a;
      --po-red: #d9534f;
    }

    .po-wrap { display: flex; gap: 14px; align-items: flex-start; }
    .po-wrap .po-col-left { flex: 0 0 300px; max-width: 300px; }
    .po-wrap .po-col-right { flex: 1 1 auto; min-width: 0; }
    @media (max-width: 991px) {
      .po-wrap { flex-direction: column; }
      .po-wrap .po-col-left { flex: 1 1 auto; max-width: 100%; position: static !important; }
    }

    /* ---- Left: info card ---- */
    .po-info-card { position: sticky; top: 16px; background: #fff; border: 1px solid var(--po-line); border-radius: 16px; padding: 12px 14px; box-shadow: 0 6px 20px rgba(23,43,77,.06); }
    .po-info-code { font-size: 17px; font-weight: 800; color: var(--po-ink); letter-spacing: -.01em; margin: 0; }
    .po-status-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 20px; margin-top: 8px; }
    .po-status-pill.is-draft { background: var(--po-amber-soft); color: #96690f; }
    .po-status-pill.is-live { background: #e6f7ec; color: #1d7a41; }
    .po-status-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .po-info-list { margin: 10px 0 0; padding: 0; list-style: none; border-top: 1px solid var(--po-line); }
    .po-info-list li { display: flex; align-items: flex-start; gap: 8px; padding: 7px 0; border-bottom: 1px solid var(--po-line); }
    .po-info-list li .po-info-icon { flex: 0 0 22px; height: 22px; border-radius: 7px; background: var(--po-bg-soft); color: var(--po-muted); display: flex; align-items: center; justify-content: center; font-size: 10.5px; margin-top: 1px; }
    .po-info-list li .po-info-body { min-width: 0; }
    .po-info-list li .po-info-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; color: var(--po-muted); display: block; }
    .po-info-list li .po-info-val { font-size: 13.5px; font-weight: 600; color: var(--po-ink); overflow-wrap: anywhere; }

    .po-info-actions { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
  .po-info-actions .row { margin-left: -3px; margin-right: -3px; }
  .po-info-actions .row > [class*="col-"] { padding-left: 3px; padding-right: 3px; display: flex; }
  .po-info-actions .row .btn { flex: 1 1 auto; }
    .po-info-actions .row { margin-left: -3px; margin-right: -3px; }
    .po-info-actions .row > [class*="col-"] { padding-left: 3px; padding-right: 3px; }
    .po-info-actions .btn { border-radius: 9px; font-weight: 600; font-size: 12px; text-align: left; padding: 6px 10px; box-shadow: none; border: 1px solid transparent; }
    .po-info-actions .btn-back { background: #fff; border-color: var(--po-line); color: var(--po-muted); }
    .po-info-actions .btn-back:hover { background: var(--po-bg-soft); color: var(--po-ink); }
    .po-info-actions .btn-edit { background: var(--po-accent-soft); color: var(--po-accent); }
    .po-info-actions .btn-edit:hover { background: #ddeafb; color: var(--po-accent); }
    .po-info-actions .btn-publish { background: var(--po-green); color: #fff; }
    .po-info-actions .btn-publish:hover { background: #268f4d; color: #fff; }
    .po-info-actions .btn-danger-ghost { background: #fdeceb; color: var(--po-red); }
    .po-info-actions .btn-success-ghost { background: #e6f7ec; color: #1d7a41; }
    .po-info-actions .btn i { width: 16px; text-align: center; margin-right: 6px; }

    /* ---- Right: main card ---- */
    .po-main-card { background: #fff; border: 1px solid var(--po-line); border-radius: 16px; box-shadow: 0 6px 20px rgba(23,43,77,.06); overflow: hidden; }
    .po-main-card .po-main-inner { padding: 0 10px 10px; }
    .po-hint { background: var(--po-accent-soft); border: 1px solid #d7e5fa; color: #2d5aa8; border-radius: 12px; padding: 10px 14px; font-size: 12.5px; line-height: 1.5; }
    .po-hint b { font-weight: 700; }

    .po-tabs-wrap { margin-top: 8px; }
    .po-tabs-wrap .nav-tabs { border-bottom: 0; background: var(--po-bg-soft); border-radius: 12px; padding: 4px; margin-bottom: 0; display: inline-flex; flex-wrap: wrap; max-width: 100%; gap: 2px; }
    .po-tabs-wrap .nav-tabs .nav-item { margin-bottom: 0; }
    .po-tabs-wrap .nav-tabs .nav-link { border: 0; border-radius: 9px; color: var(--po-muted); font-weight: 600; font-size: 13px; padding: 7px 14px; display: flex; align-items: center; gap: 7px; }
    .po-tabs-wrap .nav-tabs .nav-link:hover { color: var(--po-ink); }
    .po-tabs-wrap .nav-tabs .nav-link.active { background: #fff; color: var(--po-ink); box-shadow: 0 2px 8px rgba(23,43,77,.14); }
    .po-tabs-wrap .nav-tabs .badge { border-radius: 20px; font-size: 10.5px; font-weight: 700; }
    .po-tabs-wrap .nav-tabs .badge-primary { background: var(--po-accent); }
    .po-tabs-wrap .nav-tabs .badge-secondary { background: #aab3c2; }

    .po-panel { border: 1px solid var(--po-line); border-radius: 12px; overflow: hidden; margin-top: 8px; }
    .po-main-card .tab-content { padding: 0; }

    .po-section-label { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--po-ink); margin: 0 0 4px 2px; }
    .po-inputbar { background: linear-gradient(180deg, #fbfcfe, var(--po-bg-soft)); padding: 6px 8px; border-bottom: 1px solid var(--po-line); }
    .po-inputbar .form-group { margin-bottom: 0; }
    .po-inputbar .form-row { margin-bottom: 0; }
    .po-input-caption { font-size: 16px; font-weight: 800; color: var(--po-ink); margin: 0 2px 4px; }
    .po-toolbar-btns { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
  .po-inputbar .form-row > [class*="col-"] { min-width: 0; }
  .po-inputbar .qty-compact { font-size: 12px; padding-left: 4px; padding-right: 4px; }

  /* Stepper workflow (receiving): ACTIVE > QC > READY > ACC */
  .ri-stepper { display: flex; align-items: flex-start; margin: 4px 0 12px; }
  .ri-step { flex: 1 1 0; text-align: center; position: relative; }
  .ri-step .ri-dot { width: 26px; height: 26px; border-radius: 50%; background: #e6eaf1; color: #8a94a6; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; position: relative; z-index: 2; }
  .ri-step .ri-lbl { display: block; font-size: 10.5px; font-weight: 700; color: #8a94a6; margin-top: 4px; text-transform: uppercase; letter-spacing: .04em; }
  .ri-step::before { content: ""; position: absolute; top: 13px; left: -50%; width: 100%; height: 2px; background: #e6eaf1; z-index: 1; }
  .ri-step:first-child::before { display: none; }
  .ri-step.is-done .ri-dot { background: var(--po-green); color: #fff; }
  .ri-step.is-done .ri-lbl { color: var(--po-green); }
  .ri-step.is-done::before { background: var(--po-green); }
  .ri-step.is-current .ri-dot { background: var(--po-accent); color: #fff; box-shadow: 0 0 0 4px var(--po-accent-soft); }
  .ri-step.is-current .ri-lbl { color: var(--po-accent); }
    .po-toolbar-btns .staged-count { font-size: 11.5px; color: var(--po-muted); font-weight: 600; white-space: nowrap; }
    .po-edit-mode .hdr-field { margin-bottom: 6px; }
    .po-edit-mode .hdr-field .field-label { margin-bottom: 0; }
    .po-inputbar .field-label { display: block; font-size: 10.5px; font-weight: 600; color: var(--po-muted); margin-bottom: 3px; }
    .po-inputbar .field-label .req { color: var(--po-red); margin-left: 2px; }
    .po-inputbar .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 12.5px; }
    .po-inputbar .form-control:focus { border-color: var(--po-accent); box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
    .po-inputbar .form-control.is-invalid { border-color: var(--po-red); box-shadow: 0 0 0 3px rgba(217,83,79,.14); animation: po-shake .35s; }
    @keyframes po-shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }

    .po-edit-mode .form-group { margin-bottom: 6px; }
    .po-edit-mode .field-label { display: block; font-size: 10.5px; font-weight: 600; color: var(--po-muted); margin-bottom: 2px; text-align: left; }
    .po-edit-mode .field-label .req { color: var(--po-red); margin-left: 2px; }
    .po-edit-mode .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 12.5px; }
    .po-edit-mode .form-control:focus { border-color: var(--po-accent); box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
    .po-edit-mode .row { margin-left: -3px; margin-right: -3px; }
    .po-edit-mode .row > [class*="col-"] { padding-left: 3px; padding-right: 3px; }
    .po-info-code { padding-right: 28px; }
    .btn-collapse-info { position: absolute; top: 8px; right: 8px; opacity: .65; }
    .btn-collapse-info:hover { opacity: 1; }

    .po-kemasan-fixed { position: relative; }
    .po-kemasan-fixed .form-control[readonly] { background: #eef1f6; color: var(--po-muted); cursor: not-allowed; padding-right: 26px; }
    .po-kemasan-fixed .fa-lock { position: absolute; right: 9px; top: 27px; font-size: 10px; color: #a7b0c0; pointer-events: none; }

    .po-panel .table { margin-bottom: 0; }
    .po-panel .table thead th { border: 0; background: var(--po-bg-soft); color: var(--po-muted); font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; padding: 9px 8px; }
    .po-panel .table tbody td { border-top: 1px solid #eef1f7; padding: 3px 6px; font-size: 12.5px; vertical-align: middle; }
    .po-panel .table thead th { padding: 5px 6px; }
    .po-panel tr.row-editing td { background: #eaf3ff !important; }
    .po-panel .cell-inp { width: 100%; border: 1px solid var(--po-accent); border-radius: 6px; padding: 3px 6px; font-size: 12.5px; }
    .orphan-item { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 3px 0; border-top: 1px dashed #f0d48a; font-size: 12px; }
    .po-panel .table tbody tr:hover td { background: #f6faff; }
    .po-panel .table tbody tr.table-warning td { background: var(--po-amber-soft) !important; }
    .po-panel .table tbody tr.table-warning:hover td { background: #fbecc9 !important; }
    .po-panel .table tbody tr.row-just-added td { animation: po-row-flash 1s ease-out; }
    @keyframes po-row-flash { 0% { background: #d9ecff !important; } 100% { background: inherit; } }

    .empty-state td { padding: 12px 8px !important; }
    .empty-state .fa-inbox { font-size: 22px; color: #c3cbd8; display: block; margin-bottom: 6px; }
    .empty-state .empty-title { color: var(--po-muted); font-weight: 600; font-size: 13px; }
    .empty-state .empty-hint { color: #a7b0c0; font-size: 11.5px; margin-top: 2px; }

    .btn-add-tab { border-radius: 8px; box-shadow: 0 3px 10px rgba(40,167,69,.30); white-space: nowrap; padding: 4px 14px; display: inline-flex; align-items: center; gap: 5px; }
    .btn-save-tab { white-space: nowrap; }
    .btn-note-tab { white-space: nowrap; padding: 4px 14px; display: inline-flex; align-items: center; gap: 5px; }
    .btn-note-tab .fa, .btn-add-tab .fa { margin-right: 0 !important; }

    /* Modal catatan produk */
    #modalNote .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
    #modalNote .modal-header { background: var(--po-bg-soft); border-bottom: 1px solid var(--po-line); padding: 12px 16px; }
    #modalNote .modal-title { font-weight: 800; font-size: 15px; color: var(--po-ink); }
    #modalNote .modal-body { padding: 14px 16px; }
    #modalNote .field-label { display: block; font-size: 10.5px; font-weight: 600; color: var(--po-muted); margin-bottom: 3px; }
    #modalNote .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 12.5px; }
    #modalNote .form-control:focus { border-color: var(--po-accent); box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
    #modalNote .modal-footer { border-top: 1px solid var(--po-line); padding: 10px 16px; }
    #modalNote .modal-footer .btn { border-radius: 8px; }
    #modalNote textarea { resize: vertical; min-height: 52px; }

    .po-save-bar { position: sticky; bottom: 0; z-index: 5; background: #fff; border-top: 1px solid var(--po-line); padding: 6px 12px; display: flex; justify-content: flex-end; align-items: center; gap: 10px; transition: box-shadow .2s; }
    .po-save-bar.has-staged { box-shadow: 0 -6px 14px rgba(23,43,77,.08); }
    .po-save-bar .staged-count { font-size: 12px; color: var(--po-muted); font-weight: 600; }
    .btn-save-tab { border-radius: 9px; box-shadow: 0 3px 10px rgba(61,111,209,.30); padding: 5px 14px; font-weight: 600; }
    .btn-save-tab:disabled { opacity: .45; box-shadow: none; cursor: not-allowed; }

    .qty-inp, .edit-qty { text-align: center; }
    .select2-container .select2-selection--single { height: 31px !important; border-radius: 8px !important; border-color: #d8dfec !important; overflow: hidden; display: flex !important; align-items: center !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: normal !important; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 24px; text-align: center; flex: 1 1 auto; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 29px !important; }
  /* List produk: scroll dalam card + header nempel di atas */
  .po-table-scroll { max-height: 320px; overflow-y: auto; }
  .po-table-scroll thead th { position: sticky; top: 0; z-index: 2; }

  /* Form halaman create/edit */
  .po-form .field-label { display: block; font-size: 10.5px; font-weight: 600; color: var(--po-muted); margin-bottom: 3px; text-align: left; }
  .po-form .field-label .req { color: var(--po-red); margin-left: 2px; }
  .po-form .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 13px; }
  .po-form .form-control:focus { border-color: var(--po-accent); box-shadow: 0 0 0 3px rgba(61,111,209,.14); }

  /* Toggle kotak generik (dipakai modal QC, Auto SPK, dsb) */
  .po-spk-toggle { display: flex; align-items: center; gap: 10px; border: 1px solid #d8dfec; border-radius: 8px; padding: 6px 10px; cursor: pointer; margin: 0; min-height: 38px; }
  .po-spk-toggle input { display: none; }
  .po-spk-box { flex: 0 0 22px; height: 22px; border-radius: 6px; border: 2px solid #c3cad6; color: transparent; display: flex; align-items: center; justify-content: center; font-size: 12px; }
  .po-spk-toggle input:checked + .po-spk-box { background: #2fa85a; border-color: #2fa85a; color: #fff; }
  .po-spk-toggle input:checked ~ .po-spk-text strong { color: #1d7a41; }
  .po-spk-text { line-height: 1.25; }
  .po-spk-text strong { display: block; font-size: 12.5px; color: #1c2733; }
  .po-spk-text small { color: #7a8494; font-size: 10.5px; }
  .po-spk-toggle.mini { min-height: 31px; padding: 3px 8px; gap: 6px; }
  .po-spk-toggle.mini .po-spk-box { flex-basis: 18px; height: 18px; font-size: 10px; }
  .po-spk-toggle.mini .po-spk-text { min-width: 0; }
  .po-spk-toggle.mini .po-spk-text strong { font-size: 10px; }
  #qc-status { text-align: center; text-align-last: center; }
  #qc-status option { text-align: center; }

  /* Label & modal generik */
  .modal .field-label { display: block; font-size: 10.5px; font-weight: 600; color: #7a8494; margin-bottom: 3px; text-align: left; }
  .modal .field-label .req { color: #d9534f; margin-left: 2px; }
    /* Kolom baku: layout fixed + lebar pasti, semua sel 1 baris (teks penuh via tooltip) */
    .po-panel table.table-fit { table-layout: fixed; }
    .po-panel table.table-fit th, .po-panel table.table-fit td {
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--po-accent) !important; box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
