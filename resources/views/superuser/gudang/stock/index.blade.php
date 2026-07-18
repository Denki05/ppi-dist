@extends('superuser.app')

@section('content')

<style>
/* ===== STYLE SAMPAI SEKARANG TIDAK DIUBAH ===== */
body{ background:#1f242a; font-family: "Segoe UI", Roboto, sans-serif; }
.crm-wrapper{ max-width:1000px; margin:0 auto; height:calc(100vh - 90px); }
.crm-card{ height:100%; border-radius:10px; border:none; box-shadow:0 4px 14px rgba(0,0,0,.08); }
.crm-card .card-body{ padding:10px 14px; overflow-y:auto; }
.form-label{ font-size:12px; font-weight:600; margin-bottom:3px; }
.form-control, .select2-container--default .select2-selection--single{ height:32px !important; font-size:13px; padding:4px 8px; }
.table-responsive{ padding:0 !important; margin:0 !important; max-height: calc(100vh - 260px); overflow-y: auto; overflow-x: hidden }
#datatable{ width:100% !important; table-layout:fixed !important; border-collapse:collapse !important; margin:0 !important; font-size:14px; }
#datatable thead th{ background:#f4f6f9; font-size:14px; font-weight:600; padding:4px 6px !important; border-bottom:1px solid #dfe3e8; text-transform:uppercase; letter-spacing:.3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; position: sticky; top: 0; z-index: 5; background: #f4f6f9; }
#datatable tbody td{ font-size:13.5px; padding:3px 6px !important; line-height:1.2 !important; border-bottom:1px solid #eef1f4; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; vertical-align:middle; }
#datatable tbody tr{ height:30px; }
#datatable tbody tr:hover{ background:#f8fafc; }
#datatable th, #datatable td{ border-right:1px solid #f1f3f5; }
.table > :not(caption) > * > *{ padding:0 !important; }
.table-striped tbody tr:nth-of-type(odd){ background:none !important; }
.dataTables_wrapper{ margin-top:4px; }
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter{ margin-bottom:6px; font-size:12px; }
.dataTables_wrapper .dataTables_info{ font-size:11px; padding-top:4px !important; }
.dataTables_wrapper .dataTables_paginate{ padding-top:2px !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button{ padding:2px 8px !important; font-size:11px; }
.text-danger-strong{ color:#d92d20; font-weight:800; }
.text-warning-strong{ color:#f79009; font-weight:800; }
.text-success-strong{ color:#067647; font-weight:800; }
.dataTables_wrapper .dataTables_paginate{ display:flex !important; justify-content:center !important; margin-top:10px; }
.erp-pagination{ display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; }
.erp-pagination button{ background:#f4f6f9; border:1px solid #dfe3e8; padding:3px 8px; border-radius:4px; cursor:pointer; font-size:13px; }
.erp-pagination button:hover{ background:#e9eef5; }
.erp-pagination .page-info{ min-width:50px; text-align:center; }
#datatable tbody td.text-end{ font-size:13px; font-weight:700; letter-spacing:.3px; }
.text-danger-strong, .text-warning-strong, .text-success-strong{ font-size:13px; }
#datatable th:nth-child(5), #datatable td:nth-child(5), 
#datatable th:nth-child(6), #datatable td:nth-child(6){ width:65px !important; text-align:right; }

.ks-fixed-modal{
    height: 75vh;
    display: flex;
    flex-direction: column;
}

.ks-fixed-modal .modal-body{
    flex: 1;
    overflow: hidden;
}

.ks-modal-body {
    flex: 1;
    overflow: hidden;
}

#ksDetailModal .modal-content {
    height: 75vh;
    display: flex;
    flex-direction: column;
}

#ksDetailModal .modal-body {
    flex: 1;
    overflow: hidden;
}

/* ===== PERBAIKAN CENTER MODAL (TANPA MERUSAK STYLE LAIN) ===== */
#ksDetailModal .modal-dialog {
    max-width: 1200px;
}

/* ===== Compact Action Toolbar ===== */
.action-toolbar .btn{
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.action-toolbar form{
    margin: 0;
}

.action-toolbar .btn i{
    font-size: 11px;
}

/* ── Tombol Audit Log ─────────────────────────────────── */
.btn-teal { background: #0f766e; border-color: #0f766e; color: #fff; }
.btn-teal:hover { background: #0d6560; color: #fff; }

/* ── Tab bar ──────────────────────────────────────────── */
.al-tab-btn {
    padding: 8px 20px; font-size: 13px; font-weight: 600;
    border: none; background: none; cursor: pointer;
    color: #64748b; border-bottom: 2px solid transparent;
    transition: all .2s ease;
}
.al-tab-btn.active { color: #0f766e; border-bottom-color: #0f766e; }
.al-tab-btn:hover:not(.active) { color: #334155; background: #f8fafc; border-radius: 4px 4px 0 0; }

/* ── Accordion rows ───────────────────────────────────── */
.al-group-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; 
    background: #f1f5f9; /* Level 1: Lebih gelap untuk kesan Header */
    border-bottom: 1px solid #cbd5e1; /* Garis pemisah lebih tegas */
    cursor: pointer; user-select: none;
    transition: background-color .2s;
}
.al-group-row:hover { background: #e2e8f0; }

.al-sub-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 16px 8px 36px; 
    background: #f8fafc; /* Level 2: Lebih terang */
    border-bottom: 1px dashed #e2e8f0; /* Garis putus-putus untuk child group */
    cursor: pointer; user-select: none;
    transition: background-color .2s;
}
.al-sub-row:hover { background: #f1f5f9; }

.al-detail-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 16px 8px 58px; /* Level 3: Indentasi lebih dalam masuk ke dalam */
    background: #ffffff; /* Detail murni putih agar mencolok */
    border-bottom: 1px solid #f1f5f9;
    font-size: 12px; color: #475569;
    transition: background-color .15s;
}
.al-detail-row:hover { background: #f8fafc; } /* Tambahan efek hover di baris detail */

/* ── Accordion elements ───────────────────────────────── */
.al-chev { font-size: 11px; color: #94a3b8; width: 12px; flex-shrink: 0; transition: transform .2s ease; }
.al-chev.open { transform: rotate(90deg); color: #0f766e; } /* Icon panah berubah warna kehijauan saat buka */
.al-group-title { font-size: 13px; font-weight: 700; color: #0f172a; flex: 1; letter-spacing: 0.2px; }
.al-sub-title { font-size: 12px; font-weight: 600; color: #334155; flex: 1; }

.al-badge {
    font-size: 10px; padding: 2px 8px; border-radius: 12px;
    font-weight: 600; background: #e0f2fe; color: #0369a1; white-space: nowrap;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.al-meta { font-size: 11px; color: #64748b; white-space: nowrap; }
.al-dot { width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; flex-shrink: 0; }
.al-time { color: #64748b; white-space: nowrap; min-width: 115px; font-size: 11.5px; font-family: monospace; }
.al-qty { font-weight: 700; min-width: 80px; text-align: right; font-size: 12.5px; color: #0f172a; }
.al-note { color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; font-size: 11.5px; font-style: italic; }
.al-cust { font-size: 11.5px; color: #334155; white-space: nowrap; font-weight: 500; }

/* ── Status badges ────────────────────────────────────── */
.al-st { 
    font-size: 10px; padding: 2px 8px; border-radius: 12px; 
    font-weight: 600; white-space: nowrap; 
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); /* Sedikit efek 3D / Timbul */
}
.al-st-0 { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.al-st-1 { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.al-st-2 { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
.al-st-3 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

/* ── Detail Header (Label Kolom) ──────────────────────── */
.al-detail-header {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 16px 6px 58px; /* Indentasi sama dengan .al-detail-row */
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-bottom: 2px solid #e2e8f0;
    font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;
}
.al-dh-time { min-width: 115px; }
.al-dh-qty { min-width: 80px; text-align: right; }
.al-dh-status { width: 80px; text-align: center; } /* Sesuaikan lebar badge */
.al-dh-note { flex: 1; }
.al-dh-cust { min-width: 150px; } /* Khusus By Product */


</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div id="alert-block"></div>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
    <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      @if(session()->has('collect_success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="max-height:300px; overflow-y:auto;">
        <h6>Successful Import</h6>
        <div class="row">
          @foreach(session()->get('collect_success') as $msg)
            <div class="col-12 col-md-6">{{ $msg }}</div>
          @endforeach
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif
    </div>
    <div class="col pr-0">
      @if(session()->has('collect_error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert" style="max-height:300px; overflow-y:auto;">
        <h6>Failed Import</h6>
        <div class="row">
          @foreach(session()->get('collect_error') as $msg)
            <div class="col-12 col-md-6">{{ $msg }}</div>
          @endforeach
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif
    </div>
  </div>
</div>
@endif

<div class="crm-wrapper">
    <div class="card crm-card">
        <div class="card-body">

        {{-- 1. Bagian yang BISA DILIHAT semua user (Audit Log & Title) --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h5 class="fw-bold mb-0">Stock Monitoring</h5>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2 action-toolbar">
                <button class="btn btn-sm" 
                        id="btnOpenAuditLog"
                        data-bs-toggle="modal"
                        data-bs-target="#auditLogModal"
                        style="background:#0f766e; color:#fff; font-size:12px; border-radius:6px; font-weight:600; padding:4px 10px; display:inline-flex; align-items:center; gap:4px;">
                    <i class="fa fa-history"></i> Audit Log
                </button>
            </div>
        </div>

        {{-- 2. Bagian yang HANYA BISA DILIHAT Admin (Aksi Admin) --}}
        @role('Developer|SuperAdmin', 'superuser', 'admin')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 border-top pt-3">
            
            <div class="d-flex align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2 action-toolbar">
                    
                    <form action="{{ route('superuser.gudang.stock.collectStockIn') }}" method="POST" onsubmit="return confirm('Proses collect stock in akan dijalankan. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm"><i class="fa fa-database me-1"></i> Collect Stock In</button>
                    </form>

                    <form action="{{ route('superuser.gudang.stock.collectStockTrans') }}" method="POST" onsubmit="return confirm('Proses collect stock transaksi akan dijalankan. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-truck me-1"></i> Collect Stock Trans</button>
                    </form>

                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#collectStockOutModal">
                        <i class="fa fa-arrow-up me-1"></i> Collect Stock Out
                    </button>

                    <form action="{{ route('superuser.gudang.stock.rebuildStock') }}" method="POST" onsubmit="return confirm('Proses rebuild stock akan menghitung ulang seluruh pergerakan. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="btn btn-dark btn-sm"><i class="fa fa-sync me-1"></i> Rebuild Stock</button>
                    </form>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2 action-toolbar">
                <a href="{{ route('superuser.gudang.stock.import_template') }}" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel me-1"></i> Export Template
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importStockModal">
                    <i class="fa fa-upload me-1"></i> Import Stock
                </button>
            </div>
        </div>
        @endrole

            <!-- FILTER -->
            <div class="row align-items-end mb-3">
                <div class="col-md-3">
                    <select class="js-select2 form-control" id="warehouse">
                        <option value="">Pilih Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="js-select2 form-control" id="brand" disabled>
                        <option value="">Pilih Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_name }}">{{ $brand->brand_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="js-select2 form-control" id="packaging" disabled>
                        <option value="">Pilih Packaging</option>
                        @foreach($packaging as $pack)
                            <option value="{{ $pack->id }}">{{ $pack->pack_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="product_name" placeholder="Nama Product" disabled>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table id="datatable" class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Kemasan</th>
                            <th>Stock</th>
                            <th>KS</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Detail KS di luar tabel -->
<div class="modal fade" id="ksDetailModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            Detail Stock 
            <span id="modalProductInfo" class="ms-2 text-muted fw-normal"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body ks-modal-body" id="ksDetailContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="importStockModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('superuser.gudang.stock.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Stock Opening</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Warehouse: <strong>Gudang Araya</strong></p>
                    <input type="hidden" name="warehouse_id" value="2">

                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" 
                               name="import_file" 
                               class="form-control" 
                               accept=".xls,.xlsx" 
                               required>
                        <small class="text-muted">
                            Format harus sesuai template.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Collect Stock Out -->
<div class="modal fade" id="collectStockOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Collect Stock Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="d-grid gap-3">

                    <!-- Proses dari Database -->
                    <form action="{{ route('superuser.gudang.stock.collectStockOut') }}"
                          method="POST"
                          onsubmit="return confirm('Proses collect dari database akan dijalankan. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fa fa-database"></i> Proses dari Database (SPK)
                        </button>
                    </form>

                    <hr>

                    <!-- Download Template -->
                    <a href="{{ route('superuser.gudang.stock.import_template2') }}"
                       class="btn btn-success w-100">
                        <i class="fa fa-file-excel"></i> Download Template Excel
                    </a>

                    <!-- Import Excel -->
                    <form action="{{ route('superuser.gudang.stock.import2') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-2">
                            <input type="file"
                                   name="import_file"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-upload"></i> Import Stock Out
                        </button>
                    </form>

                </div>

            </div>

        </div>
    </div>
</div>


<!-- ✅ MODAL AUDIT LOG -->
<div class="modal fade" id="auditLogModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="height:82vh; display:flex; flex-direction:column;">

      {{-- HEADER --}}
      <div class="modal-header" style="background:#0f766e; padding:10px 16px; flex-shrink:0;">
        <h5 class="modal-title text-white" style="font-size:14px;">
          <i class="fa fa-history me-2"></i>Audit Log Stock
          <span id="auditLogWarehouseLabel"
                class="ms-2 badge"
                style="background:rgba(255,255,255,.2); font-size:11px; font-weight:400;"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      {{-- FILTER --}}
      <div style="flex:0 0 auto; padding:8px 14px; background:#f8fafc; border-bottom:1px solid #e5e7eb;">
        <div class="row align-items-end g-2">

          <div class="col-auto">
            <label class="form-label">Gudang</label>
            <input type="text" class="form-control form-control-sm" id="auditWarehouseDisplay"
                   readonly style="background:#e9ecef; width:130px; font-size:12px; height:28px;">
            <input type="hidden" id="auditWarehouseId">
          </div>

          <div class="col-auto">
            <label class="form-label">Product</label>
            <select class="form-control form-control-sm" id="auditProductId"
                    style="width:210px; font-size:12px; height:28px;">
              <option value="">-- Semua Product --</option>
            </select>
          </div>

          <div class="col-auto">
            <label class="form-label">Dari</label>
            <input type="date" class="form-control form-control-sm" id="auditDateFrom"
                   style="width:125px; font-size:12px; height:28px;">
          </div>

          <div class="col-auto">
            <label class="form-label">Sampai</label>
            <input type="date" class="form-control form-control-sm" id="auditDateTo"
                   style="width:125px; font-size:12px; height:28px;">
          </div>

          <!-- <div class="col-auto">
            <label class="form-label">Status</label>
            <select class="form-control form-control-sm" id="auditStatus"
                    style="width:120px; font-size:12px; height:28px;">
              <option value="">Semua</option>
              <option value="0">Nonaktif</option>
              <option value="1">Aktif</option>
            </select>
          </div> -->

          <div class="col-auto d-flex align-items-end gap-1">
            <button id="btnAuditFilter" class="btn btn-sm"
                    style="background:#0f766e; color:#fff; height:28px; font-size:12px; padding:0 12px;">
              <i class="fa fa-search"></i> Filter
            </button>
            <button id="btnAuditReset" class="btn btn-sm btn-secondary"
                    style="height:28px; font-size:12px; padding:0 8px;" title="Reset filter">
              <i class="fa fa-undo"></i>
            </button>
          </div>

        </div>
      </div>

      {{-- SUMMARY --}}
      <div style="flex:0 0 auto; padding:5px 14px; background:#f0fdf4; border-bottom:1px solid #dcfce7;
                  display:flex; gap:16px; font-size:11.5px; align-items:center; flex-wrap:wrap;">
       
        <span style="color:#065f46;">Aktif: <strong id="auditCountAktif" style="color:#065f46;">0</strong></span>
        {{-- Ganti bagian button Export di Modal Summary --}}
        <div style="margin-left:auto; display:flex; gap:6px;">
            <button type="button" class="btn btn-sm btn-success" onclick="exportReport('excel')">
                <i class="fa fa-file-excel me-1"></i>Excel
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="exportReport('pdf')">
                <i class="fa fa-file-pdf me-1"></i>PDF
            </button>
        </div>
      </div>

      {{-- TABS --}}
      <div style="flex:0 0 auto; display:flex; border-bottom:1px solid #e5e7eb;
                  background:#fff; padding:0 14px;">
        <button class="al-tab-btn active" id="alTabInvoiceBtn"
                onclick="switchAuditTab('invoice', this)">
          <i class="fa fa-file-invoice me-1"></i>By Invoice
        </button>
        <button class="al-tab-btn" id="alTabProductBtn"
                onclick="switchAuditTab('product', this)">
          <i class="fa fa-boxes me-1"></i>By Product
        </button>
      </div>

      {{-- CONTENT AREA --}}
      <div style="flex:1; overflow-y:auto;" id="auditLogContent">
        <div class="text-center py-5 text-muted">
          <i class="fa fa-database fa-2x d-block mb-2"></i>
          <small>Pilih gudang di halaman utama, lalu klik tombol Audit Log</small>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
// =============================================
// AUDIT LOG
// =============================================
// =============================================
// AUDIT LOG
// =============================================
(function () {

// ── State ────────────────────────────────────────────────────
let _auditData  = [];
let _activeTab  = 'invoice';

// ── Status config ────────────────────────────────────────────
const ST = {
    0: { label: 'Nonaktif',    cls: 'al-st-0' },
    1: { label: 'Aktif',       cls: 'al-st-1' },
    2: { label: 'Done',        cls: 'al-st-2' },
    3: { label: 'Info/Revisi', cls: 'al-st-3' },
};

// ── Helpers ──────────────────────────────────────────────────
function stBadge(status) {
    let s = ST[status] ?? { label: status, cls: 'al-st-0' };
    return `<span class="al-st ${s.cls}">${s.label}</span>`;
}

function fmtQty(qty) {
    return parseFloat(qty).toLocaleString('id-ID', { minimumFractionDigits: 2 });
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Toggle accordion ─────────────────────────────────────────
window.alToggle = function (id) {
    let el   = document.getElementById(id);
    let chev = document.getElementById('chev_' + id);
    if (!el) return;
    let open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    if (chev) chev.classList.toggle('open', !open);
};

// ── Switch tab ───────────────────────────────────────────────
window.switchAuditTab = function (name, btn) {
    _activeTab = name;
    document.querySelectorAll('.al-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderContent();
};

// ── Render dispatcher ────────────────────────────────────────
function renderContent() {
    if (!_auditData.length) {
        $('#auditLogContent').html(
            '<div class="text-center py-5 text-muted">' +
            '<i class="fa fa-inbox fa-2x d-block mb-2"></i>' +
            '<small>Tidak ada data ditemukan</small></div>'
        );
        return;
    }
    if (_activeTab === 'invoice') renderByInvoice();
    else renderByProduct();
}

// ── RENDER: By Invoice ───────────────────────────────────────
function renderByInvoice() {
    let groups = {};
    _auditData.forEach(function (row) {
        let gKey = row.do_id ?? 'no_do';
        if (!groups[gKey]) {
            groups[gKey] = {
                do_code  : row.do_code ?? ('DO-' + row.do_id),
                date     : row.created_at ? row.created_at.substring(0, 10) : '',
                products : {}
            };
        }
        let pKey = row.product_packaging_id ?? 'no_pkg';
        if (!groups[gKey].products[pKey]) {
            groups[gKey].products[pKey] = { label: row.product_label, logs: [] };
        }
        groups[gKey].products[pKey].logs.push(row);
    });

    let html = '';
    let gi   = 0;

    Object.entries(groups).forEach(function ([doId, group]) {
        let gId      = 'gi_' + gi;
        let prodKeys = Object.keys(group.products);
        let totalQty = 0;
        prodKeys.forEach(function (pk) {
            group.products[pk].logs.forEach(function (r) { totalQty += parseFloat(r.qty); });
        });

        html += `
        <div class="al-group-row" onclick="alToggle('${gId}')">
            <span class="al-chev open" id="chev_${gId}">▶</span>
            <span class="al-group-title">${escHtml(group.do_code)}</span>
            <span class="al-badge">${prodKeys.length} produk</span>
            <span class="al-meta">${group.date}</span>
            <span class="al-meta" style="color:#065f46;font-weight:700;">${fmtQty(totalQty)}</span>
        </div>
        <div id="${gId}">`;

        let si = 0;
        Object.entries(group.products).forEach(function ([pkgId, prod]) {
            let sId = gId + '_s' + si;
            html += `
            <div class="al-sub-row" onclick="alToggle('${sId}')">
                <span class="al-chev open" id="chev_${sId}">▶</span>
                <span class="al-sub-title">${escHtml(prod.label)}</span>
                <span class="al-meta">${prod.logs.length} log</span>
            </div>
            <div id="${sId}">`;

            // 👇 INJEKSI HEADER (BY INVOICE) 👇
            if (prod.logs.length > 0) {
                html += `
                <div class="al-detail-header">
                    <div style="width:6px; flex-shrink:0;"></div>
                    <div style="min-width:115px;">WAKTU</div>
                    <div style="min-width:80px; text-align:right;">QTY</div>
                    <div style="width:80px; text-align:center;">STATUS</div>
                    <div style="flex:1;">CATATAN</div>
                </div>`;
            }

            prod.logs.forEach(function (row) {
                html += `
                <div class="al-detail-row">
                    <span class="al-dot"></span>
                    <span class="al-time" style="min-width:115px;">${row.created_at ?? '—'}</span>
                    <span class="al-qty" style="min-width:80px;">${fmtQty(row.qty)}</span>
                    <span style="width:80px; text-align:center;">${stBadge(row.status)}</span>
                    <span class="al-note" title="${escHtml(row.note)}">${escHtml(row.note) || '—'}</span>
                </div>`;
            });

            html += `</div>`;
            si++;
        });

        html += `</div>`;
        gi++;
    });

    $('#auditLogContent').html(html || '<div class="text-center py-4 text-muted"><small>Tidak ada data</small></div>');
}

// ── RENDER: By Product ───────────────────────────────────────
function renderByProduct() {
    let groups = {};
    _auditData.forEach(function (row) {
        let gKey = row.product_packaging_id ?? 'no_pkg';
        if (!groups[gKey]) {
            groups[gKey] = { label: row.product_label, invoices: {} };
        }
        let iKey = row.do_id ?? 'no_do';
        if (!groups[gKey].invoices[iKey]) {
            groups[gKey].invoices[iKey] = {
                do_code: row.do_code ?? ('DO-' + row.do_id),
                logs   : []
            };
        }
        groups[gKey].invoices[iKey].logs.push(row);
    });

    let html = '';
    let gi   = 0;

    Object.entries(groups).forEach(function ([pkgId, group]) {
        let gId     = 'gp_' + gi;
        let invKeys = Object.keys(group.invoices);
        let netQty  = 0;
        invKeys.forEach(function (ik) {
            group.invoices[ik].logs.forEach(function (r) { netQty += parseFloat(r.qty); });
        });

        html += `
        <div class="al-group-row" onclick="alToggle('${gId}')">
            <span class="al-chev open" id="chev_${gId}">▶</span>
            <span class="al-group-title">${escHtml(group.label)}</span>
            <span class="al-badge">${invKeys.length} invoice</span>
            <span class="al-meta" style="color:#065f46;font-weight:700;">net ${fmtQty(netQty)}</span>
        </div>
        <div id="${gId}">`;

        let si = 0;
        Object.entries(group.invoices).forEach(function ([doId, inv]) {
            let sId = gId + '_s' + si;
            html += `
            <div class="al-sub-row" onclick="alToggle('${sId}')">
                <span class="al-chev open" id="chev_${sId}">▶</span>
                <span class="al-sub-title">${escHtml(inv.do_code)}</span>
                <span class="al-meta">${inv.logs.length} log</span>
            </div>
            <div id="${sId}">`;

            // 👇 INJEKSI HEADER (BY PRODUCT) 👇
            if (inv.logs.length > 0) {
                html += `
                <div class="al-detail-header">
                    <div style="width:6px; flex-shrink:0;"></div>
                    <div style="min-width:115px;">WAKTU</div>
                    <div style="min-width:80px; text-align:right;">QTY</div>
                    <div style="flex:1;">CUSTOMER</div>
                    <div style="width:80px; text-align:center;">STATUS</div>
                    <div style="flex:1;">CATATAN</div>
                </div>`;
            }

            inv.logs.forEach(function (row) {
                let cust = escHtml(row.customer_name ?? '—');
                let kota = row.text_kota
                    ? ' <em style="color:#9ca3af;font-style:normal;">· ' + escHtml(row.text_kota) + '</em>'
                    : '';
                html += `
                <div class="al-detail-row">
                    <span class="al-dot"></span>
                    <span class="al-time" style="min-width:115px;">${row.created_at ?? '—'}</span>
                    <span class="al-qty" style="min-width:80px;">${fmtQty(row.qty)}</span>
                    <span class="al-cust" style="flex:1; overflow:hidden; text-overflow:ellipsis;">${cust}${kota}</span>
                    <span style="width:80px; text-align:center;">${stBadge(row.status)}</span>
                    <span class="al-note" title="${escHtml(row.note)}">${escHtml(row.note) || '—'}</span>
                </div>`;
            });

            html += `</div>`;
            si++;
        });

        html += `</div>`;
        gi++;
    });

    $('#auditLogContent').html(html || '<div class="text-center py-4 text-muted"><small>Tidak ada data</small></div>');
}

// ── Load product dropdown ─────────────────────────────────────
function loadAuditProducts(warehouseId) {
    // Reset Select2
    $('#auditProductId').val(null).trigger('change');
    $('#auditProductId').html('<option value="">-- Semua Product --</option>');
    
    if (!warehouseId) return;

    $.get('{{ route("superuser.gudang.stock.auditProducts") }}',
        { warehouse_id: warehouseId },
        function (res) {
            $.each(res.data || [], function (i, item) {
                $('#auditProductId').append(
                    '<option value="' + item.id + '">' + item.label + '</option>'
                );
            });
            // Update Select2 setelah data ditambahkan
            $('#auditProductId').trigger('change');
        }
    );
}

// ── Load audit data ───────────────────────────────────────────
function loadAuditLog() {
    let warehouseId = $('#auditWarehouseId').val();
    if (!warehouseId) {
        alert('Pilih gudang di halaman utama terlebih dahulu!');
        return;
    }

    $('#auditLogContent').html(
        '<div class="text-center py-5">' +
        '<div class="spinner-border" style="color:#0f766e;"></div>' +
        '<p class="mt-2" style="font-size:12px;color:#6b7280;">Memuat data...</p>' +
        '</div>'
    );

    $.get('{{ route("superuser.gudang.stock.auditLogJson") }}', {
        warehouse_id: warehouseId,
        product_id  : $('#auditProductId').val(),
        date_from   : $('#auditDateFrom').val(),
        date_to     : $('#auditDateTo').val(),
        status      : $('#auditStatus').val(),
    }, function (res) {

        _auditData   = res.data   || [];
        let totals   = res.totals || {};

        // Update summary
        $('#auditTotalRecord').text(_auditData.length.toLocaleString('id-ID'));
        $('#auditTotalQty').text(
            parseFloat(totals.total_qty || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 })
        );
        $('#auditCountNonaktif').text(totals.count_nonaktif ?? 0);
        $('#auditCountAktif').text(totals.count_aktif ?? 0);
        $('#auditCountDone').text(totals.count_done ?? 0);
        $('#auditCountInfo').text(totals.count_info ?? 0);

        renderContent();

    }).fail(function () {
        $('#auditLogContent').html(
            '<div class="text-danger text-center p-4">' +
            '<i class="fa fa-exclamation-triangle me-1"></i>Gagal memuat data. Cek koneksi atau route.</div>'
        );
    });
}

// ── Modal open → sync warehouse otomatis ──────────────────────
$('#auditLogModal').on('show.bs.modal', function () {
    let warehouseId   = $('#warehouse').val();
    let warehouseText = $('#warehouse option:selected').text().trim();

    // Inisialisasi Select2 untuk Product & Status di dalam modal
    $('#auditProductId').select2({
        dropdownParent: $('#auditLogModal'), // Penting agar Select2 muncul di atas modal
        placeholder: "-- Semua Product --",
        allowClear: true,
        width: '210px'
    });

    $('#auditStatus').select2({
        dropdownParent: $('#auditLogModal'),
        width: '120px',
        minimumResultsForSearch: -1 // Menyembunyikan search box untuk status yang sedikit opsinya
    });

    if (warehouseId) {
        $('#auditWarehouseId').val(warehouseId);
        $('#auditWarehouseDisplay').val(warehouseText);
        $('#auditLogWarehouseLabel').text('🏭 ' + warehouseText);
        loadAuditProducts(warehouseId);
        loadAuditLog();
    } else {
        $('#auditWarehouseDisplay').val('');
        $('#auditLogWarehouseLabel').text('⚠ Belum ada gudang dipilih');
    }
});

// ── Reset modal state saat ditutup ────────────────────────────
$('#auditLogModal').on('hidden.bs.modal', function () {
    _auditData  = [];
    _activeTab  = 'invoice';
    document.querySelectorAll('.al-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('alTabInvoiceBtn')?.classList.add('active');
});

// ── Event: Filter ─────────────────────────────────────────────
$('#btnAuditFilter').on('click', loadAuditLog);

// ── Event: Reset ──────────────────────────────────────────────
$('#btnAuditReset').on('click', function () {
    $('#auditProductId').val('').trigger('change'); // Tambahkan .trigger('change')
    $('#auditDateFrom, #auditDateTo').val('');
    $('#auditStatus').val('').trigger('change');     // Tambahkan .trigger('change')
    loadAuditLog();
});

// ── Event: Export ───────────────────────────────────────
$('#btnAuditExcel').on('click', function () {
    let params = $.param({
        warehouse_id: $('#auditWarehouseId').val(),
        product_id  : $('#auditProductId').val(),
        date_from   : $('#auditDateFrom').val(),
        date_to     : $('#auditDateTo').val(),
        status      : $('#auditStatus').val(),
        type        : _activeTab, // 'invoice' atau 'product' (Membaca tab yang sedang aktif)
        format      : 'excel'     // Memicu download Excel di Controller
    });
    window.location.href = '{{ route("superuser.gudang.stock.auditLogExport") }}?' + params;
});

window.exportReport = function(format) {
    let params = $.param({
        warehouse_id: $('#auditWarehouseId').val(),
        product_id  : $('#auditProductId').val(),
        date_from   : $('#auditDateFrom').val(),
        date_to     : $('#auditDateTo').val(),
        status      : $('#auditStatus').val(),
        type        : _activeTab,
        format      : format
    });
    window.location.href = '{{ route("superuser.gudang.stock.auditLogExport") }}?' + params;
};

})();

$(document).ready(function(){
    $('.js-select2').select2();
    let datatableUrl = '{{ route("superuser.gudang.stock.json") }}';

    let table = $('#datatable').DataTable({
        processing:true,
        serverSide:false,
        ajax:{
            url: datatableUrl,
            type:'GET',
            data: function(d){
                let warehouseId = $('#warehouse').val();
                if(!warehouseId) return false;
                return {
                    warehouse_id: warehouseId,
                    brand: $('#brand').val(),
                    packaging: $('#packaging').val(),
                    product_name: $('#product_name').val()
                };
            },
            dataSrc:'data'
        },
        dom: '<"d-flex justify-content-between mb-2"B>rt',
        buttons:[
            {
                extend:'excelHtml5',
                text:'<i class="fa fa-file-excel"></i>',
                title:'Stock Monitoring',
                className:'btn btn-success btn-sm',
                exportOptions:{
                    columns: ':visible',
                    modifier:{
                        page:'all'
                    }
                }
            }
        ],
        columns: [
          { data: 'no', width: '40px' },
          { data: 'product_name' },
          { data: 'brand_name', width: '90px' },
          { data: 'pack_name', width: '100px' },
          { data: 'stock', className: 'text-end', width: '65px' },
          { 
              data: 'ks', 
              className:'text-end', 
              width:'65px',
              render: function(data, type, row){
                    return `
                        <a href="#" 
                           class="ks-detail-link"
                           data-encoded="${row.encoded_id}"
                           data-product="${row.product_name}"
                           data-pack="${row.pack_name}">
                           ${data}
                        </a>`;
                }
          },
        ],
        paging: false,
        // pageLength:12,
        lengthChange:false,
        autoWidth:false,
        scrollX:false,
        ordering:false,
        // pagingType:"simple",
        // language:{paginate:{previous:"<", next:">"}},
        // drawCallback: function(settings){
        //     let api = this.api();
        //     let pageInfo = api.page.info();
        //     $('.dataTables_info').hide();

        //     let current = pageInfo.page+1;
        //     let total = pageInfo.pages;

        //     if($('#customPagination').length===0){
        //         $('.dataTables_paginate').html(
        //             `<div id="customPagination" class="erp-pagination">
        //                 <button class="first"><<</button>
        //                 <button class="prev"><</button>
        //                 <span class="page-info">${current}/${total}</span>
        //                 <button class="next">></button>
        //                 <button class="last">>></button>
        //             </div>`
        //         );
        //     } else {
        //         $('.page-info').text(current+'/'+total);
        //     }

        //     $('.first').off().on('click', function(){ api.page('first').draw('page'); });
        //     $('.prev').off().on('click', function(){ api.page('previous').draw('page'); });
        //     $('.next').off().on('click', function(){ api.page('next').draw('page'); });
        //     $('.last').off().on('click', function(){ api.page('last').draw('page'); });
        // }
    });

    function reloadTable(){
        if($('#warehouse').val()){
            table.ajax.reload(null,false);
        }
    }

    $('#warehouse').on('select2:select', function(e){
        $('#brand,#packaging,#product_name').prop('disabled', false);
        reloadTable();
    });
    $('#brand,#packaging,#product_name').on('change', reloadTable);

    $('#datatable').on('click', '.ks-detail-link', function(e){
        e.preventDefault();
    
        let encoded      = $(this).data('encoded');
        let warehouseId  = $('#warehouse').val();
        let productName  = $(this).data('product');
        let packName     = $(this).data('pack');
    
        if(!warehouseId) return alert('Warehouse belum dipilih!');
    
        // Set header info
        $('#modalProductInfo').html(
            `: ${productName} / ${packName}`
        );
    
        let modal = new bootstrap.Modal(document.getElementById('ksDetailModal'));
    
        $('#ksDetailContent').html(
            '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>'
        );
    
        modal.show();
    
        let url = '{{ url("superuser/gudang/stock") }}' + warehouseId + '/detail/' + encoded;
    
        $.ajax({
            url: url,
            type: 'GET',
            success: function(html){
                $('#ksDetailContent').html(html);
            },
            error: function(){
                $('#ksDetailContent').html('<div class="text-danger text-center p-3">Gagal memuat data</div>');
            }
        });
    });
});
</script>
@endpush