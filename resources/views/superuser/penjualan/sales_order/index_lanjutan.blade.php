@extends('superuser.app')

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<h4 style="font-weight: bold;">#SALES ORDER LANJUTAN</h4>
@role('Developer', 'superuser')
  <a class="btn btn-primary" href="{{ route('superuser.penjualan.sales_order.updateBrandName') }}" role="button">
    <i class="bi bi-cloud-upload"></i>
  </a>
  
  <a class="btn btn-info" href="{{ route('superuser.penjualan.packing_order.update_header_do') }}" class="btn btn-sm btn-circle btn-alt-success" title="Update Header DO">
    <i class="bi bi-arrow-repeat"></i>
  </a>

  <!-- Kalkulasi DO migrasi -->
  <a class="btn btn-warning" href="{{ route('superuser.penjualan.migrasi_so.prosesKalkulasiDO') }}" class="btn btn-sm btn-circle btn-alt-success" title="Migrasi Data">
    <i class="bi bi-calculator"></i>
  </a>
@endrole
<br>
<br>

<main style="background:#fff">  
  <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
  <label style="padding: 15px 25px;" for="tab1">SO {{ $step_txt }}</label>
    
  <input style="display: none;" id="tab2" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab2">LIST QUEUE</label>
    
  <input style="display: none;" id="tab3" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab3">SO PROGRESS</label>

  <input style="display: none;" id="tab4" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab4">REVISI INTERNAL</label>

    
  <!-- Sales Order Lanjutan -->
  <section id="content1">
    <div class="row mb-30">
            <div class="row">
              <div class="col-6">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Status</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="status_so" id="status_so">
                      <option value="">Pilih Status</option>
                      <option value="LANJUTAN">LANJUTAN</option>
                      <option value="TUTUP">TUTUP</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="form-group row">
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                          <button type="button" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50"><i class="fa fa-search ml-10"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
      <div class="col-12">
        <table class="table table-hover" id="so_lanjutan">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Nota</th>
              <th>Brand</th>
              <th>Customer</th>
              <th>Created By</th>
              <th>Type</th>
              <th>Created At</th>
              <th>Action</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      
    </div>
  </section>
    
  <!-- Packing Order -->
  <section id="content2">
    <div class="alert alert-warning" role="alert" align="left">
      Revisi hanya transaksi <strong>Tempo</strong>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="packing_order">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Customer</th>
              <th>Tanggal Buat</th>
              <th>Refrensi SO</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($packing_order as $index => $row)
              @if($row->status == 2 && in_array(optional($row->so)->payment_status, [0,1]))
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->code ?? '-' }}</td>
                    <td>{{ $row->member->name ?? '-' }} {{ $row->member->text_kota ?? '' }}</td>
                    <td>{{ optional($row->created_at)->format('d-m-Y H:i:s') ?? '-' }}</td>
                    <td>{{ optional($row->so)->code ?? '-' }} / {{ $row->type_transaction }}</td>
                    <td>
                        @php
                            $status = $row->do_status();
                        @endphp
                        <span class="badge badge-{{ $status->class ?? 'secondary' }}">
                            <b>{{ $status->msg ?? '-' }}</b>
                        </span>
                    </td>
                    <td>
                      @if($row->type_transaction == 'CASH' && $row->has_payment == 0)
                          {{-- CASH & belum bayar: HANYA tampilkan tombol konfirmasi pembayaran --}}
                          <button type="button" class="btn btn-warning btn-sm btn-flat btn-confirmed-payment" 
                                  data-url="{{ route('superuser.penjualan.packing_order.confirmed_payment', $row->id) }}">
                              <i class="fa fa-check-circle"></i> Konfirmasi Pembayaran
                          </button>

                      @elseif($row->has_payment == 1 || in_array($row->type_transaction, ['TEMPO', 'COD', 'MARKETPLACE']))
                          {{-- Sudah bayar (CASH) ATAU memang bukan CASH: tampilkan tombol release seperti biasa --}}
                          <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.ready', $row->id) }}')" 
                            class="btn btn-success btn-sm btn-flat" data-id="{{ $row->id }}">
                              <i class="fa fa-send"></i> Release SPK
                          </a>
                      @endif

                      {{-- Tombol Revisi tetap seperti sebelumnya, tidak berubah --}}
                      @if(in_array($row->type_transaction, ['TEMPO', 'COD', 'MARKETPLACE']))
                          <button type="button" class="btn btn-dark btn-sm btn-flat btn-revisi" 
                                  data-url="{{ route('superuser.penjualan.packing_order.revisi', $row->id) }}">
                              <i class="fa fa-edit"></i> Revisi
                          </button>
                      @elseif($row->type_transaction == 'CASH')
                          @role('Developer|Admin|Management')
                              <button type="button" class="btn btn-dark btn-sm btn-flat btn-revisi" 
                                      data-url="{{ route('superuser.penjualan.packing_order.revisi', $row->id) }}">
                                  <i class="fa fa-edit"></i> Revisi
                              </button>
                          @endrole
                      @endif
                  </td>
                </tr>
            @endif
            @endforeach
          </tbody>
        </table>
      </div>
      
    </div>
  </section>
    
  <!-- SO Progress (Custom Table - no DataTables) -->
  <section id="content3" class="sop-scope">
        <div class="row mb-30">
          <div class="col-12">

            {{-- ============ TOOLBAR ============ --}}
            <div class="sop-toolbar">
              <div class="sop-toolbar-row">
                <div class="sop-segmented" id="sop-segmented" role="tablist">
                  <button type="button" data-value="harian" class="{{ $filter_periode == 'harian' ? 'active' : '' }}">Hari Ini</button>
                  <button type="button" data-value="bulanan" class="{{ $filter_periode == 'bulanan' ? 'active' : '' }}">Bulan Ini</button>
                  <!-- <button type="button" data-value="custom" class="{{ $filter_periode == 'custom' ? 'active' : '' }}">Custom</button> -->
                </div>
                <select class="sop-hidden-select" id="filter_periode_progress" name="filter_periode">
                  <option value="harian" {{ $filter_periode == 'harian' ? 'selected' : '' }}>Hari Ini</option>
                  <option value="bulanan" {{ $filter_periode == 'bulanan' ? 'selected' : '' }}>Bulan Berjalan</option>
                  <option value="custom" {{ $filter_periode == 'custom' ? 'selected' : '' }}>Custom Tanggal</option>
                </select>

                <div class="sop-daterange custom-date-range" style="{{ $filter_periode == 'custom' ? '' : 'display:none;' }}">
                  <input type="date" name="tanggal_dari" class="sop-date">
                  <span class="sop-date-sep">–</span>
                  <input type="date" name="tanggal_sampai" class="sop-date">
                  <button type="button" id="btn-filter-progress" class="sop-btn sop-btn-primary">Terapkan</button>
                </div>

                <div class="sop-spacer"></div>

                <div class="sop-search-wrap">
                  <i class="fa fa-search sop-search-icon"></i>
                  <input type="text" id="sop-search" class="sop-search-input" placeholder="Cari referensi, customer, DO code…">
                  <button type="button" class="sop-search-clear" id="sop-search-clear" style="display:none;" title="Hapus pencarian">
                    <i class="fa fa-times"></i>
                  </button>
                </div>

                <select id="sop-page-length" class="sop-select-sm">
                  <option value="10">10 / hal</option>
                  <option value="30">30 / hal</option>
                  <option value="100">100 / hal</option>
                  <option value="-1">Semua</option>
                </select>
              </div>

              <div class="sop-toolbar-row sop-toolbar-meta">
                <span class="sop-chip" id="sop-chip-total">
                  <i class="fa fa-list"></i> <span id="sop-chip-total-count">0</span> DO ditampilkan
                </span>
                <span class="sop-chip sop-chip-warning" id="sop-chip-invalid">
                  <i class="fa fa-exclamation-triangle"></i> <span id="sop-chip-invalid-count">0</span> kurs belum valid
                </span>
              </div>
            </div>

            {{-- ============ BULK ACTION BAR ============ --}}
            <div class="sop-bulkbar d-none" id="bulk-action-bar">
              <div class="sop-bulkbar-left">
                <i class="fa fa-check-square"></i>
                <span><b id="bulk-selected-count">0</b> DO dipilih untuk update kurs</span>
              </div>
              <div class="sop-bulkbar-actions">
                <button type="button" class="sop-btn sop-btn-ghost" id="btn-bulk-clear">Batal</button>
                <button type="button" class="sop-btn sop-btn-warning" id="btn-bulk-update-kurs">
                  <i class="fa fa-money"></i> Update Kurs Massal
                </button>
              </div>
            </div>

            {{-- ============ TABLE ============ --}}
            <div class="sop-card">
              <div class="table-responsive">
                <table class="sop-table" id="sales_order_progress">
                  <thead>
                    <tr>
                      <th class="sop-th-check"><input type="checkbox" id="check-all-kurs"></th>
                      <th data-sort="text" class="sop-th-num text-center">#</th>
                      <th data-sort="text" class="text-center">Referensi SO</th>
                      <th data-sort="text" class="text-center">DO Code</th>
                      <th data-sort="text" class="text-center">Customer</th>
                      <th data-sort="date" class="text-center">Tanggal Buat</th>
                      <th data-sort="text" class="text-center">Type</th>
                      <th data-sort="text" class="text-center">Status DO</th>
                      <th data-sort="text" class="text-center">Status Kurs</th>
                      <th class="text-center sop-th-action">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @include('superuser.penjualan.sales_order.partials._so_progress_rows')
                  </tbody>
                </table>

                <div class="sop-empty" id="sop-empty" style="display:none;">
                  <i class="fa fa-inbox"></i>
                  <p>Tidak ada data yang cocok.</p>
                  <button type="button" class="sop-btn sop-btn-ghost" id="sop-empty-reset">Reset pencarian</button>
                </div>
              </div>

              <div class="sop-footer">
                <div class="sop-info" id="sop-info"></div>
                <div class="sop-pagination" id="sop-pagination"></div>
              </div>
            </div>

          </div>
        </div>
    </section>

    <section id="content4">
      <div class="row mb-30">
        @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
        <div class="col-12 mb-15">
          <a href="{{ route('superuser.penjualan.internal_revision.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-check-circle"></i> Halaman Approval Revisi Internal (Management/Developer)
          </a>
        </div>
        @endif
        <div class="col-12">
          <table class="table table-hover" id="revisi_internal_list">
            <thead>
              <tr>
                <th>#</th>
                <th>DO Code</th>
                <th>Refrensi SO</th>
                <th>Customer</th>
                <th>Status DO</th>
                <th>Status Revisi</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($packing_order as $index => $row)
                @if(in_array($row->status, [5, 6]))
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->do_code }}</td>
                    <td>{{ $row->so->code ?? '-' }}</td>
                    <td>
                      @if($row->member)
                        {{ $row->member->name }} {{ $row->member->text_kota }}
                      @else
                        <span class="text-danger">-</span>
                      @endif
                    </td>
                    <td>{{ $row->do_status()->msg ?? '-' }}</td>
                    <td>
                      @if($row->internal_revision_status == 1)
                        <span class="badge badge-warning">Sedang Diproses</span>
                      @elseif($row->internal_revision_count > 0)
                        <span class="badge badge-success">Sudah Direvisi ({{ $row->internal_revision_count }}x)</span>
                      @else
                        <span class="badge badge-light">-</span>
                      @endif
                    </td>
                    <td>
                      @if($row->internal_revision_status == 1)
                        <button type="button" class="btn btn-sm btn-secondary" disabled>Menunggu Approval</button>
                      @elseif(!empty($row->void_status))
                        <button type="button" class="btn btn-sm btn-secondary" disabled title="Sedang ada pengajuan Void">Terkunci (Void)</button>
                      @elseif($row->internal_revision_count > 0)
                        <button type="button" class="btn btn-sm btn-secondary" disabled title="DO sudah pernah di-revisi internal">Sudah Direvisi</button>
                      @else
                        <a href="{{ route('superuser.penjualan.internal_revision.create', $row->id) }}" class="btn btn-sm btn-outline-warning">
                          <i class="fa fa-edit"></i> Ajukan Revisi
                        </a>
                      @endif
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
</main>

<form method="post" action="{{route('superuser.penjualan.delivery_order.do_edit')}}" id="frmDoEdit">
    @csrf
    <input type="hidden" name="id">
</form>



<!-- view so -->
<div class="modal fade bd-example-modal-xl" id="modalViewSo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">#View SO <span id="so_code_display"></span></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="modal-data">
              <!-- Invoice and Customer Details -->
              <div class="row">
                  <div class="col">
                      <div class="block">
                          <div class="block-header block-header-default">
                              <h3 class="block-title">#Detail Nota</h3>
                          </div>
                          <div class="block-content">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="invoice_date">Tanggal Nota</label>
                                      <input type="text" id="invoice_date" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="invoice_code">Code</label>
                                      <input type="text" id="invoice_code" class="form-control" readonly>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="sales_senior_id">Sales Senior</label>
                                      <input type="text" id="sales_senior_id" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="sales_id">Sales</label>
                                      <input type="text" id="sales_id" class="form-control" readonly>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-4">
                                      <label for="type_transaction">Type Transaksi</label>
                                      <input type="text" id="type_transaction" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-8" id="note-container">
                                      <label for="note">Note</label>
                                      <textarea class="form-control" style="height:auto; min-height:50px; overflow:hidden;" id="note" rows="1" readonly></textarea>
                                  </div>
                                  <div class="form-group col-md-6" id="catatan-container">
                                      <label for="catatan">Catatan</label>
                                      <textarea class="form-control" style="height:auto; min-height:50px; overflow:hidden;" id="catatan" rows="1" readonly></textarea>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col">
                      <div class="block">
                          <div class="block-header block-header-default">
                              <h3 class="block-title">#Customer</h3>
                          </div>
                          <div class="block-content">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="customer_name">Customer</label>
                                      <input type="text" id="customer_name" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="customer_address">Alamat Kirim</label>
                                      <textarea class="form-control" id="customer_address" rows="1" readonly></textarea>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="customer_city">Kota</label>
                                      <input type="text" id="customer_city" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="customer_area">Provinsi</label>
                                      <input type="text" id="customer_area" class="form-control" readonly>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Product Details Table -->
              <div class="row">
                  <div class="col">
                      <table class="table">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Kode</th>
                                  <th>Product</th>
                                  <th>Kemasan</th>
                                  <th>Qty</th>
                                  <th>Harga</th>
                                  <th>Free</th>
                              </tr>
                          </thead>
                          <tbody id="product-details">
                              <!-- Product details will be injected here -->
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>

{{-- ============ MODAL: UPDATE KURS (per DO / bulk) ============ --}}
<div class="modal fade" id="modalUpdateKurs" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-money text-warning"></i> Update Kurs</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info py-10" id="modal-kurs-info" style="font-size:13px;">
          <!-- diisi via JS: "Update 1 DO" atau "Update 5 DO terpilih" -->
        </div>
        <div class="form-group">
          <label>Nilai Kurs</label>
          <input type="text" class="form-control" id="input_kurs_baru" placeholder="cth: 15.800">
          <small class="text-muted">Kurs harus lebih dari Rp 1 agar dianggap valid.</small>
        </div>
        <input type="hidden" id="update_kurs_ids">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" id="btn-submit-update-kurs">
          <i class="fa fa-save"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ============ MODAL: PERMINTAAN VOID ============ --}}
<div class="modal fade" id="modalAjukanVoid" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajukan Void DO</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="void_do_id">
        <input type="hidden" id="void_do_url">
        <div class="form-group">
          <label>Alasan Pengajuan Void</label>
          <textarea class="form-control" id="void_reason" rows="4" placeholder="Jelaskan alasan DO ini perlu dibatalkan total..."></textarea>
        </div>
        <small class="text-muted">Setelah diajukan, DO ini menunggu persetujuan Finance sebelum benar-benar dibatalkan (stok, invoice, dan SO akan dikembalikan).</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btn-submit-ajukan-void">Kirim Pengajuan</button>
      </div>
    </div>
  </div>
</div>

<style>
.sop-scope{
  --sop-primary:#4f46e5; --sop-primary-hover:#4338ca; --sop-primary-soft:#eef2ff;
  --sop-text:#0f172a; --sop-text-muted:#64748b; --sop-border:#e2e8f0;
  --sop-bg:#f8fafc; --sop-surface:#fff;
  --sop-radius-lg:14px; --sop-radius-md:10px; --sop-radius-sm:8px;
  --sop-success:#16a34a; --sop-success-bg:#f0fdf4;
  --sop-warning:#d97706; --sop-warning-bg:#fffbeb;
  --sop-danger:#dc2626; --sop-danger-bg:#fef2f2;
  --sop-info:#0284c7; --sop-info-bg:#f0f9ff;
  --sop-neutral:#475569; --sop-neutral-bg:#f1f5f9;
  --sop-primary-badge:#4f46e5; --sop-primary-badge-bg:#eef2ff;
  font-size:14px; color:var(--sop-text);
}

/* ===== Toolbar ===== */
.sop-toolbar{ background:var(--sop-surface); border:1px solid var(--sop-border); border-radius:var(--sop-radius-lg);
  padding:14px 16px; margin-bottom:14px; display:flex; flex-direction:column; gap:10px; }
.sop-toolbar-row{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.sop-toolbar-meta{ padding-top:8px; border-top:1px dashed var(--sop-border); }

.sop-segmented{ display:inline-flex; background:var(--sop-bg); border:1px solid var(--sop-border);
  border-radius:var(--sop-radius-sm); padding:3px; }
.sop-segmented button{ border:none; background:transparent; padding:6px 14px; font-size:13px; font-weight:500;
  color:var(--sop-text-muted); border-radius:6px; cursor:pointer; transition:.15s; }
.sop-segmented button.active{ background:var(--sop-surface); color:var(--sop-primary); box-shadow:0 1px 3px rgba(15,23,42,.1); }
.sop-segmented button:hover:not(.active){ color:var(--sop-text); }
.sop-hidden-select{ display:none; }

.sop-daterange{ display:flex; align-items:center; gap:8px; }
.sop-date{ border:1px solid var(--sop-border); border-radius:var(--sop-radius-sm); padding:6px 9px; font-size:13px; }
.sop-date-sep{ color:var(--sop-text-muted); }
.sop-spacer{ flex:1; }

.sop-search-wrap{ position:relative; display:flex; align-items:center; }
.sop-search-icon{ position:absolute; left:11px; font-size:12px; color:var(--sop-text-muted); pointer-events:none; }
.sop-search-input{ border:1px solid var(--sop-border); border-radius:var(--sop-radius-sm);
  padding:8px 32px 8px 30px; font-size:13px; width:260px; outline:none; transition:.15s; }
.sop-search-input:focus{ border-color:var(--sop-primary); box-shadow:0 0 0 3px var(--sop-primary-soft); }
.sop-search-clear{ position:absolute; right:6px; border:none; background:none; color:var(--sop-text-muted);
  cursor:pointer; padding:4px 6px; border-radius:50%; }
.sop-search-clear:hover{ background:var(--sop-bg); color:var(--sop-text); }

.sop-select-sm{ border:1px solid var(--sop-border); border-radius:var(--sop-radius-sm); padding:7px 9px;
  font-size:13px; background:#fff; outline:none; }

.sop-chip{ display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--sop-text-muted);
  background:var(--sop-bg); border:1px solid var(--sop-border); padding:5px 11px; border-radius:999px; }
.sop-chip-warning{ background:var(--sop-warning-bg); color:var(--sop-warning); border-color:#fde8bd; }

.sop-btn{ display:inline-flex; align-items:center; gap:6px; border:none; border-radius:var(--sop-radius-sm);
  padding:8px 15px; font-size:13px; font-weight:500; cursor:pointer; transition:.15s; }
.sop-btn-primary{ background:var(--sop-primary); color:#fff; }
.sop-btn-primary:hover{ background:var(--sop-primary-hover); }
.sop-btn-warning{ background:#f4b740; color:#4a3200; }
.sop-btn-warning:hover{ background:#e5a92c; }
.sop-btn-ghost{ background:transparent; color:var(--sop-text-muted); border:1px solid var(--sop-border); }
.sop-btn-ghost:hover{ background:var(--sop-bg); color:var(--sop-text); }

/* ===== Bulk bar ===== */
.sop-bulkbar{ display:flex; align-items:center; justify-content:space-between;
  background:var(--sop-primary-soft); border:1px solid #d6dcfb; border-radius:var(--sop-radius-lg);
  padding:11px 16px; margin-bottom:14px; font-size:13px; color:#3730a3; }
.sop-bulkbar.d-none{ display:none !important; }
.sop-bulkbar-left{ display:flex; align-items:center; gap:9px; }
.sop-bulkbar-actions{ display:flex; gap:8px; }

/* ===== Card / table ===== */
.sop-card{ background:var(--sop-surface); border:1px solid var(--sop-border); border-radius:var(--sop-radius-lg); overflow:hidden; }
.sop-table{ width:100%; border-collapse:collapse; font-size:13.5px; margin:0; }
.sop-table thead th{ background:var(--sop-bg); border-bottom:1px solid var(--sop-border);
  font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--sop-text-muted);
  font-weight:600; padding:12px 14px; text-align:left; white-space:nowrap; cursor:pointer; user-select:none; }
.sop-table thead th.sop-th-check, .sop-table thead th.sop-th-action{ cursor:default; text-align:center; }
.sop-table thead th.sop-sort-asc:after{ content:" \25B2"; font-size:9px; color:var(--sop-primary); }
.sop-table thead th.sop-sort-desc:after{ content:" \25BC"; font-size:9px; color:var(--sop-primary); }
.sop-table tbody td{ padding:11px 14px; border-bottom:1px solid #f1f4f8; color:var(--sop-text); vertical-align:middle; }
.sop-table tbody tr:last-child td{ border-bottom:none; }
.sop-table tbody tr{ transition:background .1s; }
.sop-table tbody tr:hover{ background:var(--sop-bg); }
.sop-th-num{ width:36px; text-align:center; }
input[type="checkbox"]{ accent-color:var(--sop-primary); width:16px; height:16px; cursor:pointer; }

.sop-code{ font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:12.5px; color:#334155; }
.sop-type{ background:var(--sop-neutral-bg); color:var(--sop-neutral); font-size:11px; padding:3px 9px; border-radius:20px; font-weight:500; }

.sop-customer{ display:flex; align-items:center; gap:9px; }
.sop-avatar{ width:30px; height:30px; border-radius:50%; background:var(--sop-primary-soft); color:var(--sop-primary);
  display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; }
.sop-customer-name{ font-weight:500; line-height:1.3; }
.sop-customer-city{ font-size:11.5px; color:var(--sop-text-muted); }

.sop-badge{ display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border-radius:999px; font-size:12px; font-weight:600; }
.sop-badge .sop-dot{ width:6px; height:6px; border-radius:50%; display:inline-block; }
.sop-badge-success{ background:var(--sop-success-bg); color:var(--sop-success); } .sop-badge-success .sop-dot{ background:var(--sop-success); }
.sop-badge-warning{ background:var(--sop-warning-bg); color:var(--sop-warning); } .sop-badge-warning .sop-dot{ background:var(--sop-warning); }
.sop-badge-danger{  background:var(--sop-danger-bg);  color:var(--sop-danger); }  .sop-badge-danger .sop-dot{ background:var(--sop-danger); }
.sop-badge-info{    background:var(--sop-info-bg);    color:var(--sop-info); }    .sop-badge-info .sop-dot{ background:var(--sop-info); }
.sop-badge-secondary,.sop-badge-primary{ background:var(--sop-primary-badge-bg); color:var(--sop-primary-badge); } .sop-badge-secondary .sop-dot,.sop-badge-primary .sop-dot{ background:var(--sop-primary-badge); }

.sop-btn-icon{ border:1px solid var(--sop-border); background:#fff; width:30px; height:30px; border-radius:var(--sop-radius-sm);
  cursor:pointer; color:#64748b; transition:.15s; }
.sop-btn-icon:hover{ background:var(--sop-bg); border-color:#cbd5e1; }
.sop-dropdown{ font-size:13px; box-shadow:0 10px 30px rgba(15,23,42,.1); border:1px solid var(--sop-border); border-radius:10px; padding:6px; }
.sop-dropdown .dropdown-item{ border-radius:6px; padding:8px 10px; }
.sop-dropdown .dropdown-item:hover{ background:var(--sop-bg); }

/* ===== Empty state ===== */
.sop-empty{ text-align:center; padding:56px 20px; color:var(--sop-text-muted); }
.sop-empty i{ font-size:30px; margin-bottom:10px; display:block; opacity:.5; }
.sop-empty p{ margin:0 0 14px; font-size:13.5px; }

/* ===== Footer / pagination ===== */
.sop-footer{ display:flex; align-items:center; justify-content:space-between; padding:11px 14px;
  border-top:1px solid var(--sop-border); background:var(--sop-bg); flex-wrap:wrap; gap:8px; }
.sop-info{ font-size:12.5px; color:var(--sop-text-muted); }
.sop-pagination{ display:flex; gap:4px; }
.sop-pagination button{ border:1px solid var(--sop-border); background:#fff; padding:6px 12px; font-size:12.5px;
  border-radius:var(--sop-radius-sm); cursor:pointer; color:#475569; transition:.15s; }
.sop-pagination button:hover:not(:disabled){ border-color:var(--sop-primary); color:var(--sop-primary); }
.sop-pagination button.active{ background:var(--sop-primary); border-color:var(--sop-primary); color:#fff; }
.sop-pagination button:disabled{ opacity:.4; cursor:not-allowed; }

/* ===== Focus visibility (aksesibilitas) ===== */
.sop-scope button:focus-visible, .sop-scope input:focus-visible, .sop-scope select:focus-visible{
  outline:2px solid var(--sop-primary); outline-offset:1px;
}

/* ===== Mobile: tabel -> card list ===== */
@media (max-width:768px){
  .sop-search-input{ width:100%; }
  .sop-toolbar-row{ flex-direction:column; align-items:stretch; }
  .sop-search-wrap{ width:100%; }
  .sop-daterange{ flex-wrap:wrap; }
  .sop-customer-city{ display:none; }

  .sop-table thead{ display:none; }
  .sop-table, .sop-table tbody, .sop-table tr, .sop-table td{ display:block; width:100%; }
  .sop-table tbody tr{ background:#fff; border:1px solid var(--sop-border); border-radius:var(--sop-radius-md);
    margin:0 0 10px; padding:10px 12px; position:relative; }
  .sop-table tbody tr:last-child{ margin-bottom:0; }
  .sop-table td{ border:none !important; padding:5px 0; display:flex; justify-content:space-between; align-items:center; gap:10px; }
  .sop-table td[data-label]:before{ content:attr(data-label); font-size:10.5px; color:var(--sop-text-muted);
    font-weight:600; text-transform:uppercase; letter-spacing:.03em; flex-shrink:0; }
  .sop-table td.sop-td-check{ position:absolute; top:10px; left:10px; padding:0; width:auto; }
  .sop-table td.sop-td-action{ position:absolute; top:6px; right:6px; padding:0; width:auto; }
  .sop-table tbody tr{ padding-left:38px; padding-top:14px; }
}
</style>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(document).ready(function() {
      let datatableUrl = '{{ route('superuser.penjualan.sales_order.json_lanjutan') }}';
      let firstDatatableUrl = datatableUrl + '?status_so=all';

        $('.js-select2').select2();

        var $currentVoidRow = null;

        var datatable = $('#so_lanjutan').DataTable({
          language: {
            processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
          },
          processing: true,
          serverSide: false,
          searching: true,
          paging: true,
          info: false,
          ajax: {
            "url": datatableUrl,
            "dataType": "json",
            "type": "GET",
            "data":{ _token: "{{csrf_token()}}"}
          },
          columns: [
            {data: 'DT_RowIndex', name: 'id'},
            {data: 'so_code', name: 'penjualan_so.so_code'},
            {data: 'code', name: 'penjualan_so.code'},
            {data: 'nota_brand', name: 'penjualan_so.brand_name'},
            {data: 'customer'},
            {data: 'so_created_by'},
            {data: 'so_transaction', name: 'penjualan_so.type_transaction'},
            {
                data: 'so_created_at',
                render: {
                    _: 'display',
                    sort: 'timestamp'
                }
            },
            {data: 'action'},
          ],
          order: [
            [2, 'asc'],
          ],
          pageLength: 10,
          lengthMenu: [
            [10, 30, 100, -1],
            [10, 30, 100, 'All']
          ], 
        });

        $('#btn-filter').on('click', function(e) {
            e.preventDefault();
            var status = $('#status_so').val();
            let newDatatableUrl = datatableUrl + '?status_so=' + status;
            datatable.ajax.url(newDatatableUrl).load();
        })

        $('#so_lanjutan tbody').on('click', 'button.btn-view', function() {
          var data = datatable.row($(this).parents('tr')).data();
          var id = $(this).data('id'); // Ensure this matches the button's data-id attribute

          var url = "{{ route('superuser.penjualan.sales_order.data_so', ':id') }}";
          url = url.replace(':id', id);

          $.ajax({
              url: url,
              type: 'GET',
              success: function(response) {
                  $('#invoice_date').val(response.created_at);
                  $('#so_code').val(response.so_code);
                  $('#invoice_code').val(response.so_code);
                  $('#so_code_display').text(response.so_code);
                  // $('#sales_senior_id').val(response.sales_senior_id);
                  
                  // $('#sales_id').val(response.sales_id);
                  $('#type_transaction').val(response.type_transaction);
                  
                  if (response.status == 2 || response.status == 4) {
                      $('#note-container').show();
                      $('#note').val(response.note);
                  } else {
                      $('#note-container').hide();
                  }

                  if (response.status == 5) {
                      $('#catatan-container').show();
                      $('#catatan').val(response.catatan);
                  } else {
                      $('#catatan-container').hide();
                  }

                  $('#customer_name').val(response.customer_name);
                  $('#customer_address').val(response.customer_address);
                  $('#customer_city').val(response.customer_kota);
                  $('#customer_area').val(response.customer_provinsi);

                  // Populate product details table
                  var productDetails = '';
                  response.products.forEach(function(product, index) {
                      productDetails += '<tr>';
                      productDetails += '<td>' + (index + 1) + '</td>';
                      productDetails += '<td>' + product.code + '</td>';
                      productDetails += '<td>' + product.name + '</td>';
                      productDetails += '<td>' + product.kemasan + '</td>';
                      productDetails += '<td>' + product.qty + '</td>';
                      productDetails += '<td>' + product.price + '</td>';
                      productDetails += '<td>' + (product.free ? 'Yes' : 'No') + '</td>';
                      productDetails += '</tr>';
                  });
                  $('#product-details').html(productDetails);

                  $('#userModal').css("display", "block"); // Show the modal
              },
              error: function(xhr, status, error) {
                  console.error('Error:', error);
                  alert('An error occurred while fetching the data.');
              }
          });
        });

        $('#packing_order').DataTable( {
          paging:   true,
            orderin: true,
            info:     false,
            searching : true,
            order: [
              [1, 'asc'],
            ],
            pageLength: 10,
            lengthMenu: [
              [10, 30, 100, -1],
              [10, 30, 100, 'All']
            ], 
        });

        $('#revisi_internal_list').DataTable({
          paging: true,
          ordering: true,
          info: false,
          searching: true,
          order: [
              [1, 'asc'],
          ],
          pageLength: 5,
          lengthMenu: [
              [10, 30, 100, -1],
              [10, 30, 100, 'All']
          ],
        });

        // ================= SO PROGRESS: custom table (no DataTables) =================
        var $sopTable    = $('#sales_order_progress');
        var $sopTbody    = $sopTable.find('tbody');
        var allRows      = $sopTbody.find('tr').get();
        var pageLength   = 10;
        var currentPage  = 1;
        var sortState    = { index: null, dir: 'asc' };
        var searchTerm   = '';

        function filterRows() {
            if (!searchTerm) return allRows.slice();
            var term = searchTerm.toLowerCase();
            return allRows.filter(function(tr) {
                return $(tr).text().toLowerCase().indexOf(term) !== -1;
            });
        }

        function sortRows(rows) {
            if (sortState.index === null) return rows;
            var idx = sortState.index;
            var dir = sortState.dir === 'asc' ? 1 : -1;
            return rows.slice().sort(function(a, b) {
                var $a = $(a).find('td').eq(idx);
                var $b = $(b).find('td').eq(idx);
                var av = $a.data('sort-value') !== undefined ? $a.data('sort-value') : $a.text().trim().toLowerCase();
                var bv = $b.data('sort-value') !== undefined ? $b.data('sort-value') : $b.text().trim().toLowerCase();
                if (av < bv) return -1 * dir;
                if (av > bv) return 1 * dir;
                return 0;
            });
        }

        function render() {
            var filtered = sortRows(filterRows());
            var total = filtered.length;
            var perPage = pageLength === -1 ? (total || 1) : pageLength;
            var totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            var start = (currentPage - 1) * perPage;
            var end = pageLength === -1 ? total : start + perPage;
            var pageRows = filtered.slice(start, end);

            $sopTbody.empty();
            if (pageRows.length === 0) {
                $('#sop-empty').show();
                $sopTable.hide();
            } else {
                $('#sop-empty').hide();
                $sopTable.show();
                pageRows.forEach(function(tr) { $sopTbody.append(tr); });
            }

            $('#sop-info').text(
                total === 0
                    ? 'Tidak ada data'
                    : 'Menampilkan ' + (start + 1) + '-' + Math.min(end, total) + ' dari ' + total + ' data'
            );

            renderPagination(totalPages);
            toggleBulkBar();
            sopUpdateChips();
        }

        function renderPagination(totalPages) {
            var $pg = $('#sop-pagination');
            $pg.empty();

            var $prev = $('<button>Previous</button>').prop('disabled', currentPage === 1);
            $prev.on('click', function() { currentPage--; render(); });
            $pg.append($prev);

            var maxButtons = 5;
            var startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
            var endPage = Math.min(totalPages, startPage + maxButtons - 1);
            startPage = Math.max(1, endPage - maxButtons + 1);

            for (var p = startPage; p <= endPage; p++) {
                (function(p) {
                    var $btn = $('<button>' + p + '</button>');
                    if (p === currentPage) $btn.addClass('active');
                    $btn.on('click', function() { currentPage = p; render(); });
                    $pg.append($btn);
                })(p);
            }

            var $next = $('<button>Next</button>').prop('disabled', currentPage === totalPages);
            $next.on('click', function() { currentPage++; render(); });
            $pg.append($next);
        }

        // Search (debounced)
        var searchTimeout;
        $('#sop-search').on('keyup', function() {
            clearTimeout(searchTimeout);
            var val = $(this).val();
            searchTimeout = setTimeout(function() {
                searchTerm = val;
                currentPage = 1;
                render();
            }, 250);
        });

        // Page length
        $('#sop-page-length').on('change', function() {
            pageLength = parseInt($(this).val(), 10);
            currentPage = 1;
            render();
        });

        // Sorting on header click
        $sopTable.find('thead th[data-sort]').each(function() {
            $(this).on('click', function() {
                var idx = $(this).index();
                if (sortState.index === idx) {
                    sortState.dir = sortState.dir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortState.index = idx;
                    sortState.dir = 'asc';
                }
                $sopTable.find('thead th').removeClass('sop-sort-asc sop-sort-desc');
                $(this).addClass(sortState.dir === 'asc' ? 'sop-sort-asc' : 'sop-sort-desc');
                render();
            });
        });

        function sopUpdateChips(){
            $('#sop-chip-total-count').text(allRows.length);
            var invalidCount = allRows.filter(function(tr){ return $(tr).find('.check-kurs-row').length > 0; }).length;
            $('#sop-chip-invalid-count').text(invalidCount);
            $('#sop-chip-invalid').toggle(invalidCount > 0);
        }

        render();

        // Checkbox select all (delegated - baris di-render ulang tiap ganti halaman/search)
        $(document).on('change', '#check-all-kurs', function(){
            $('.check-kurs-row').prop('checked', $(this).is(':checked'));
            toggleBulkBar();
        });

        $(document).on('change', '.check-kurs-row', toggleBulkBar);

        function toggleBulkBar(){
            var count = $('.check-kurs-row:checked').length;
            $('#bulk-selected-count').text(count);
            $('#bulk-action-bar').toggleClass('d-none', count === 0).toggleClass('d-flex', count > 0);
        }

        $(document).on('click','.btn-delete',function(){
            if(confirm("Apakah anda yakin ingin menghapus SO ini ? ")){
            let id = $(this).data('id');
            $('#frmDestroyItem').find('input[name="id"]').val(id);
            $('#frmDestroyItem').submit();
            }
        });

        $(document).on('click','.btn-kembali-ke-awal',function(){
            if(confirm("Apakah anda yakin ingin mengembalikan sales order ini?")){
            let id = $(this).data('id');
            $('#frmKembali').find('input[name="id"]').val(id);
            $('#frmKembali').submit();
            }
        });

        $(document).on('click','.btn-frmedit',function(){
            if(confirm("Apakah anda yakin melakukan Edit?")){
            let id = $(this).data('id');
            $('#frmRevisi').find('input[name="id"]').val(id);
            $('#frmRevisi').submit();
            }
        });

        $(document).on('click','.btn_cancel',function(){
            if(confirm("Apakah anda yakin melakukan Cancel DO?")){
            let id = $(this).data('id');
            $('#frmCancel').find('input[name="id"]').val(id);
            $('#frmCancel').submit();
            }
        });

        $(document).on('click','.btn-frmdoedit',function(){
            if(confirm("Apakah anda yakin melakukan Edit DO?")){
            let id = $(this).data('id');
            $('#frmDoEdit').find('input[name="id"]').val(id);
            $('#frmDoEdit').submit();
            }
        });

        $(document).on('click', '.btn-revisi', function(e) {
          e.preventDefault();
          var url = $(this).data('url');

          Swal.fire({
              title: 'Konfirmasi',
              text: "Apakah anda yakin ingin melakukan Revisi?",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Ya, Revisi!',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if(result.isConfirmed){
                  $.ajax({
                      url: url,
                      type: 'POST', 
                      data: {_token: '{{ csrf_token() }}'},
                      success: function(res){
                          Swal.fire({
                              icon: res.status === 'success' ? 'success' : 'error',
                              title: res.status === 'success' ? 'Berhasil' : 'Gagal',
                              text: res.message,
                              timer: 2000,
                              showConfirmButton: false
                          }).then(() => {
                              if(res.redirect){
                                  window.location.href = res.redirect;
                              }
                          });
                      },
                      error: function(err){
                          Swal.fire('Error', 'Terjadi kesalahan saat memproses request!', 'error');
                          console.log(err);
                      }
                  });
              }
          });
        });

        $(document).on('click', '.btn-revisi-logistik', function(e){
          e.preventDefault();
          var url = $(this).data('url');

          Swal.fire({
              title: 'Konfirmasi',
              text: "DO ini akan ditarik kembali ke List Queue. Lanjutkan?",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Ya, Tarik',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if(result.isConfirmed){
                  $.ajax({
                      url: url,
                      type: 'POST',
                      data: {_token: '{{ csrf_token() }}'},
                      success: function(res){
                          Swal.fire({
                              icon: res.status === 'success' ? 'success' : 'error',
                              title: res.status === 'success' ? 'Berhasil' : 'Gagal',
                              text: res.message,
                              timer: 2000,
                              showConfirmButton: false
                          }).then(() => {
                              if(res.redirect) window.location.href = res.redirect;
                          });
                      }
                  });
              }
          });
      });

        // Toggle input tanggal custom
        function sopFetchData(params) {
            $.get("{{ route('superuser.penjualan.sales_order.so_progress_partial') }}", params, function(html) {
                $('#sales_order_progress tbody').html(html);
                allRows = $('#sales_order_progress tbody tr').get(); // refresh referensi baris
                currentPage = 1;
                sortState = { index: null, dir: 'asc' };
                render(); // panggil ulang fungsi render yang sudah ada di IIFE
                sopUpdateChips();
            });
        }

        $('#filter_periode_progress').on('change', function(){
            var val = $(this).val();
            $('.custom-date-range').toggle(val === 'custom');
            if (val !== 'custom') {
                sopFetchData({ filter_periode: val });
            }
        });

        $('#btn-filter-progress').on('click', function(){
            var dari = $('input[name="tanggal_dari"]').val();
            var sampai = $('input[name="tanggal_sampai"]').val();
            sopFetchData({ filter_periode: 'custom', tanggal_dari: dari, tanggal_sampai: sampai });
        });

        $('#sop-segmented button').on('click', function(){
            var val = $(this).data('value');
            $('#sop-segmented button').removeClass('active');
            $(this).addClass('active');
            $('#filter_periode_progress').val(val);
            if (val !== 'custom') sopFetchData({ filter_periode: val });
        });

        // Update kurs per-DO (dari dropdown Aksi)
        $(document).on('click', '.btn-update-kurs', function(){
            var id = $(this).data('id');
            $('#update_kurs_ids').val(id);
            $('#modal-kurs-info').text('Update kurs untuk 1 DO.');
            $('#input_kurs_baru').val('');
            $('#modalUpdateKurs').modal('show');
        });

        // Update kurs massal (dari bulk action bar)
        $('#btn-bulk-update-kurs').on('click', function(){
            var ids = $('.check-kurs-row:checked').map(function(){ return $(this).val(); }).get();
            $('#update_kurs_ids').val(ids.join(','));
            $('#modal-kurs-info').text('Update kurs untuk ' + ids.length + ' DO terpilih.');
            $('#input_kurs_baru').val('');
            $('#modalUpdateKurs').modal('show');
        });

        $('#btn-bulk-clear').on('click', function(){
            $('.check-kurs-row').prop('checked', false);
            $('#check-all-kurs').prop('checked', false);
            toggleBulkBar();
        });

        // Submit update kurs (endpoint & logic dibuat pada tahap berikutnya)
        $('#btn-submit-update-kurs').on('click', function(){
            var ids = $('#update_kurs_ids').val();
            var kurs = $('#input_kurs_baru').val();

            if (!kurs) {
                Swal.fire('Error', 'Nilai kurs tidak boleh kosong', 'error');
                return;
            }

            $.ajax({
                url: '{{ route("superuser.penjualan.packing_order.update_kurs") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids,
                    idr_rate: kurs
                },
                success: function(res){
                    Swal.fire({
                        icon: res.status === 'success' ? 'success' : 'error',
                        title: res.status === 'success' ? 'Berhasil' : 'Gagal',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        if (res.status === 'success') {
                            $('#modalUpdateKurs').modal('hide');
                            window.location.reload();
                        }
                    });
                },
                error: function(err){
                    Swal.fire('Error', 'Terjadi kesalahan saat memproses request!', 'error');
                    console.log(err);
                }
            });
        });

        $(document).on('click', '.btn-ajukan-void', function(){
            $currentVoidRow = $(this).closest('tr');
            $('#void_do_id').val($(this).data('id'));
            $('#void_do_url').val($(this).data('url'));
            $('#void_reason').val('');
            $('#modalAjukanVoid').modal('show');
        });

        $('#btn-submit-ajukan-void').on('click', function(){
            var url = $('#void_do_url').val();
            var reason = $('#void_reason').val().trim();

            if (!reason || reason.length < 5) {
                Swal.fire('Error', 'Alasan pengajuan wajib diisi (minimal 5 karakter).', 'error');
                return;
            }

            Swal.fire({
                title: 'Yakin ajukan void?',
                text: 'DO ini akan menunggu approval Finance sebelum dibatalkan total.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.value) return;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: reason
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: res.status === 'success' ? 'success' : 'error',
                            title: res.status === 'success' ? 'Berhasil' : 'Gagal',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            if (res.status !== 'success') return;

                            $('#modalAjukanVoid').modal('hide');

                            if ($currentVoidRow && $currentVoidRow.length) {
                                // Badge "Pengajuan Void" di kolom Status DO
                                var $statusCell = $currentVoidRow.find('td[data-label="Status DO"]');
                                if ($statusCell.find('.sop-badge-danger').length === 0) {
                                    $statusCell.append(
                                        '<br><span class="sop-badge sop-badge-danger mt-1"><i class="sop-dot"></i>Pengajuan Void</span>'
                                    );
                                }

                                // Dropdown Aksi -> kunci jadi "Menunggu Approval Void"
                                $currentVoidRow.find('.sop-dropdown').html(
                                    '<span class="dropdown-item text-muted disabled" style="pointer-events:none;">' +
                                    '<i class="fa fa-clock text-warning"></i> Menunggu Approval Void' +
                                    '</span>'
                                );

                                $currentVoidRow = null;
                            }
                        });
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan saat memproses request!', 'error');
                    }
                });
            });
        });

        $(document).on('click', '.btn-confirmed-payment', function(e){
          e.preventDefault();
          var url = $(this).data('url');

          Swal.fire({
              title: 'Konfirmasi Pembayaran',
              text: "Pastikan sudah mengecek bukti transfer/mutasi rekening. Lanjutkan konfirmasi?",
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Sudah Dicek',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if(result.isConfirmed){
                  $.ajax({
                      url: url,
                      type: 'POST',
                      data: {_token: '{{ csrf_token() }}'},
                      success: function(res){
                          Swal.fire({
                              icon: res.success ? 'success' : 'error',
                              title: res.success ? 'Berhasil' : 'Gagal',
                              text: res.message,
                              timer: 2000,
                              showConfirmButton: false
                          }).then(() => {
                              if(res.success) window.location.reload(); // reload supaya tombol Release SPK muncul
                          });
                      }
                  });
              }
          });
      });

        // [TAMBAHAN 2] Search: tombol clear (x) muncul/hilang otomatis
        $('#sop-search').on('input', function(){
            $('#sop-search-clear').toggle($(this).val().length > 0);
        });
        $('#sop-search-clear, #sop-empty-reset').on('click', function(){
            $('#sop-search').val('').trigger('keyup');
            $('#sop-search-clear').hide();
        });
    })
</script>
@endpush