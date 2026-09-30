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


<div class="row">
  <div class="col-12">
    <div class="block">
      <div class="block-content block-content-full">

        <h4 class="so-title mb-3">#SALES ORDER {{ $step_txt }}</h4>

        {{-- ================= BARIS 1: tombol kiri, tab + search + filter kanan ================= --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:8px;">

          <div class="d-flex flex-wrap" style="gap:8px;">
            <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#exampleModal">
              <i class="fa fa-plus mr-1"></i> Add SO
            </button>

            <div class="dropdown">
              <button class="btn btn-outline-info dropdown-toggle shadow-sm" type="button" id="btnSOOptions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-cog mr-1"></i> Options
              </button>
              <div class="dropdown-menu shadow" aria-labelledby="btnSOOptions" style="font-size: 13px;">
                @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
                  <a class="dropdown-item py-2" href="javascript:void(0)" data-toggle="modal" data-target="#modal-manage">
                    <i class="fa fa-file-excel-o fa-fw mr-2 text-success"></i> Export & Manage
                  </a>
                @endif

                <a class="dropdown-item py-2" href="{{ route('superuser.penjualan.migrasi_so.prosesMigrasi') }}">
                  <i class="fa fa-exchange fa-fw mr-2 text-info"></i> Migrasi SO
                </a>

                @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item py-2 text-warning font-weight-bold" href="{{ route('superuser.penjualan.sales_order.archive_awal') }}">
                    <i class="fa fa-archive fa-fw mr-2"></i> Riwayat Archive
                  </a>
                @endif
              </div>
            </div>
          </div>

          <div class="soa-cmdbar">
            <ul class="nav nav-tabs" id="soAwalTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#so-tab-draft" role="tab" data-tab="draft">
                  <i class="fa fa-pencil"></i>Draft<span class="soa-tabcount" id="cnt-draft">0</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#so-tab-submit" role="tab" data-tab="submit">
                  <i class="fa fa-send"></i>Submit<span class="soa-tabcount" id="cnt-submit">0</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#so-tab-selesai" role="tab" data-tab="selesai">
                  <i class="fa fa-check-circle"></i>Terkirim<span class="soa-tabcount" id="cnt-selesai">0</span>
                </a>
              </li>
            </ul>

            <div class="soa-cmd-right">
              <div class="soa-searchwrap">
                <i class="fa fa-search"></i>
                <input type="text" id="soa-search" placeholder="Cari kode, nota, customer, brand…" autocomplete="off">
              </div>
              <button type="button" class="btn btn-filter-toggle" id="soa-filter-toggle">
                <i class="fa fa-sliders"></i>Filter<span class="soa-filter-count" id="soa-filter-count" style="display:none;">0</span>
              </button>
            </div>
          </div>
        </div>

        {{-- ================= BARIS 2: keterangan cakupan + chip filter aktif ================= --}}
        <div class="soa-scopebar">
          <span class="soa-scope" id="soa-scope"><i class="fa fa-info-circle"></i><span id="soa-scope-text"></span></span>
          <span id="soa-chips"></span>
          <a href="javascript:void(0)" id="soa-chips-reset" style="display:none;">Reset filter</a>
        </div>

        <div class="tab-content pt-10">

          {{-- ================= TAB DRAFT: AWAL + REVISI, semua yang belum disubmit ================= --}}
          <div class="tab-pane fade show active" id="so-tab-draft" role="tabpanel">
            <div class="table-responsive soa-table-card">
              <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal_draft">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Nota</th>
                    <th>Approval</th>
                    <th>Brand</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Status Approval</th>
                    <th>Action</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          {{-- ================= TAB SUBMIT: sudah dilanjutkan / diproses, belum ada resi (read-only) ================= --}}
          <div class="tab-pane fade" id="so-tab-submit" role="tabpanel">
            <div class="table-responsive soa-table-card">
              <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal_submit">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Nota</th>
                    <th>Approval</th>
                    <th>Brand</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Status Approval</th>
                    <th>Action</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          {{-- ================= TAB TERKIRIM: sudah update resi, terbaru dulu (read-only) ================= --}}
          <div class="tab-pane fade" id="so-tab-selesai" role="tabpanel">
            <div class="table-responsive soa-table-card">
              <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal_selesai">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Nota</th>
                    <th>Approval</th>
                    <th>Brand</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Status Approval</th>
                    <th>Action</th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Filter -->
<div class="modal fade" id="soaFilterModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light">
        <h5 class="modal-title font-weight-bold" style="font-size: 15px;">
          <i class="fa fa-sliders text-primary mr-2"></i>Filter <span id="soa-modal-tabname" class="text-primary">DRAFT</span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body text-left p-4">

        <!-- 1 filter bersama untuk semua tab (tab-nya sendiri = status) -->
        <div id="fsec-shared">
          <div class="form-group text-left mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_customer">Customer</label>
            <select class="form-control js-select2" id="f_customer" data-placeholder="Semua Customer" style="width: 100%;">
              <option value="">Semua</option>
              @foreach($other_address as $key)
              <option value="{{ $key->name }}">{{ $key->name }} {{$key->text_kota}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group text-left mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_brand">Brand</label>
            <select class="form-control js-select2" id="f_brand" data-placeholder="Semua Brand" style="width: 100%;">
              <option value="">Semua</option>
              @foreach($brand as $br)
              <option value="{{ $br->brand_name }}">{{ $br->brand_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group text-left mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 12px;" id="f_date_label">Tanggal</label>
            <input type="text" class="form-control soa-flatrange" id="f_date" placeholder="Pilih rentang tanggal" readonly>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light d-flex justify-content-end">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-times mr-1"></i>Batal</button>
        <button type="button" class="btn btn-sm btn-outline-danger" id="soa-apply-reset"><i class="fa fa-undo mr-1"></i>Reset</button>
        <button type="button" class="btn btn-sm btn-primary" id="soa-apply"><i class="fa fa-search mr-1"></i>Terapkan</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('modal')
<!-- modal add so : Layout Super Padat -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-add-so" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light py-2">
        <h5 class="modal-title font-weight-bold" id="exampleModalLabel" style="font-size: 15px;"><i class="fa fa-plus-circle text-primary mr-2"></i>#Add SO {{ $step_txt }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="padding: 0.5rem 1rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="formAddSO" autocomplete="off" class="d-flex flex-column h-100">
        @csrf
        <div class="modal-body bg-light p-2">
          <div class="row m-0">

            {{-- KOLOM KIRI --}}
            <div class="col-12 col-lg-6 px-1 d-flex flex-column">

              <div class="so-card shadow-sm mb-2">
                <div class="so-card-head"><i class="fa fa-user"></i><span>A. Informasi Umum</span></div>
                <div class="form-group row align-items-center mb-0">
                  <label class="col-sm-4 col-form-label text-left" for="account_member">Customer <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <select class="js-select2 account_member" id="account_member" name="member_name" style="width:100%;" required>
                      <option value="">Pilih Customer</option>
                      @foreach($other_address as $row)
                      <option value="{{$row->id}}">{{$row->name}} {{$row->text_kota}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="so-card shadow-sm mb-2">
                <div class="so-card-head"><i class="fa fa-cube"></i><span>B. Korelasi Produk</span></div>
                <div class="form-group row align-items-center mb-1">
                  <label class="col-sm-4 col-form-label text-left" for="merek_ppi">Brand <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <select class="js-select2" id="merek_ppi" name="brand_name" style="width:100%;" required>
                      <option value="">Pilih Brand</option>
                      @foreach($brand as $row)
                      <option value="{{$row->brand_name}}">{{$row->brand_name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group row align-items-center mb-0">
                  <label class="col-sm-4 col-form-label text-left" for="packaging_id">Kemasan <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <select class="js-select2" id="packaging_id" name="packaging_id" style="width:100%;" required>
                      <option value="">Pilih Kemasan</option>
                      @foreach($packaging as $row)
                      <option value="{{$row->id}}">{{$row->pack_name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <!-- C. DISKON DISUSUN 3 KE BAWAH -->
              <div class="so-card shadow-sm mb-lg-0 flex-grow-1">
                <div class="so-card-head"><i class="fa fa-tags"></i><span>C. Pengaturan Diskon</span></div>
                <div class="px-1">

                  <div class="form-group row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label text-left" for="disc_usd">Disc USD</label>
                    <div class="col-sm-8">
                      <select class="js-select2" name="disc_usd" id="disc_usd" style="width:100%;">
                        <option value="0">0</option>
                        <option value="2">2</option>
                        <option value="4">4</option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group row align-items-center mb-2" id="disc-percent-group" style="display: none;">
                    <label class="col-sm-4 col-form-label text-left compact-label pr-0" for="disc_percent">Disc %</label>
                    <div class="col-sm-8">
                      <div class="input-group input-group-sm">
                        <input class="form-control" type="number" step="any" name="disc_percent" id="disc_percent" placeholder="0">
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label text-left compact-label pr-0" for="disc_kemasan">Disc Kemasan</label>
                    <div class="col-sm-8">
                      <input class="form-control form-control-sm" type="number" step="any" name="disc_kemasan" id="disc_kemasan" placeholder="0">
                    </div>
                  </div>

                  <div class="form-group row align-items-center mb-0">
                    <label class="col-sm-4 col-form-label text-left compact-label pr-0" for="disc_idr">Disc IDR</label>
                    <div class="col-sm-8">
                      <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input class="form-control" type="number" step="any" name="disc_idr" id="disc_idr" placeholder="0">
                      </div>
                    </div>
                  </div>

                </div>
              </div>

            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-12 col-lg-6 px-1 d-flex flex-column">

              <div class="so-card shadow-sm mb-2">
                <div class="so-card-head"><i class="fa fa-cogs"></i><span>D. Mekanisme SO</span></div>

                <div class="form-group row align-items-center mb-1">
                  <label class="col-sm-4 col-form-label text-left" for="so_type">Type <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <select class="js-select2" name="so_type" id="so_type" style="width:100%;" required>
                      <option value="">Pilih Transaksi Type</option>
                      @foreach(App\Entities\Penjualan\SalesOrder::TYPE_TRANSACTION as $row => $value)
                      <option value="{{$row}}">{{$row}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="form-group row align-items-center mb-1">
                  <label class="col-sm-4 col-form-label text-left" for="indent_so">Indent <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <select class="js-select2" name="so_indent" id="indent_so" style="width:100%;" required>
                      <option value="">Pilih</option>
                      <option value="0">NO</option>
                      <option value="1">YES</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row align-items-center mb-1">
                  <label class="col-sm-4 col-form-label text-left">Approval</label>
                  <div class="col-sm-8">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="approval_spv" id="approval_spv" value="1">
                      <label class="custom-control-label font-weight-bold text-muted" for="approval_spv" style="font-size: 12px; padding-top: 2px;">Membutuhkan Approval SPV</label>
                    </div>
                  </div>
                </div>

                {{-- Kurs hanya wajib jika Approval SPV dicentang (required diatur lewat JS) --}}
                <div class="form-group row align-items-center mb-1" id="kurs-group" style="display: none;">
                  <label class="col-sm-4 col-form-label text-left" for="kurs">Kurs <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                      <input class="form-control" type="text" name="kurs" id="kurs" inputmode="numeric" placeholder="15.500">
                    </div>
                  </div>
                </div>

              </div>

              <div class="so-card shadow-sm mb-lg-0 flex-grow-1 d-flex flex-column">
                <div class="so-card-head"><i class="fa fa-sticky-note-o"></i><span>E. Catatan</span></div>
                <div class="form-group mb-0 flex-grow-1 d-flex flex-column">
                  <textarea class="form-control flex-grow-1" name="note" id="editor" placeholder="Tulis catatan di sini..." style="resize: none; min-height: 100px;"></textarea>
                  <div class="text-right mt-2">
                    <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" id="test" style="font-size: 11px;">
                      <i class="fa fa-list-ol mr-1"></i>List No.
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-times mr-1"></i>Batal</button>
          <button type="button" id="addSO" class="btn btn-sm btn-primary"><i class="fa fa-save mr-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Migrasi -->
<div class="modal fade" id="modal-manage" tabindex="-1" role="dialog" aria-labelledby="modal-manage" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Manage</h3>
        </div>
        <div class="block-content pb-20">
          <div class="row">
            <div class="col-12 col-md-6">
              <span class="font-size-h5">Import</span>
              <p>
                Import your data with the template provided below.<br>
                <span class="text-danger"><b>Don't</b></span> remove / change the header (first row).<br>
                Only fill in the column provided, the additional columns will not be processed.
              </p>
              @if(isset($import_custom_message))
              <div class="mb-15">
                <b>Note :</b> <br>
                {!! $import_custom_message !!}
              </div>
              @endif
              <a href="{{ $import_template_url ?? '' }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-download mr-5"></i> Template
                </button>
              </a>
              <hr>
              <form action="{{ route('superuser.penjualan.migrasi_so.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label><strong>Import SO Header (.xlsx)</strong></label>
                <div class="custom-file mb-10">
                  <input type="file" class="custom-file-input" id="import_header" name="header" data-toggle="custom-file-input" required>
                  <label class="custom-file-label" for="import_header">Choose SO Header file</label>
                </div>
                <label><strong>Import SO List (.xlsx)</strong></label>
                <div class="custom-file mb-10">
                  <input type="file" class="custom-file-input" id="import_list" name="list" data-toggle="custom-file-input" required>
                  <label class="custom-file-label" for="import_list">Choose SO List file</label>
                </div>
                <button type="submit" class="btn mt-10 w-100 btn-alt-primary">
                  <i class="fa fa-upload mr-5"></i> Import
                </button>
              </form>
            </div>
            <div class="col-12 col-md-6">
              <span class="font-size-h5">Export</span>
              <p>Export this data to excel-like format</p>
              <a href="{{ $export_url ?? '' }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-file-excel-o mr-5"></i> Export
                </button>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-alt-secondary" data-dismiss="modal">
          <i class="fa fa-close"></i>
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* ================= Modal Add SO : kompak ================= */
  #exampleModal .modal-dialog { width: 98%; max-width: 1050px; }

  .so-card {
    border: 1px solid #e7eaee;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 8px;
    background: #ffffff;
  }
  .so-card-head {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    color: #0b5ed7;
    padding-bottom: 6px;
    margin-bottom: 8px;
    border-bottom: 1px solid #eef1f4;
  }
  .modal-add-so .col-form-label {
    color: #495057;
    font-weight: 600;
    font-size: 12px;
    padding-top: 4px;
    padding-bottom: 4px;
  }
  .modal-add-so .compact-label {
    color: #495057;
    font-weight: 600;
    font-size: 11px;
    margin-bottom: 2px;
    display: block;
  }
  .modal-add-so .form-control-sm,
  .modal-add-so .input-group-sm > .form-control,
  .modal-add-so .input-group-sm > .input-group-prepend > .input-group-text,
  .modal-add-so .input-group-sm > .input-group-append > .input-group-text {
    height: 30px;
    padding: 2px 8px;
    font-size: 12px;
  }
  .modal-add-so textarea.form-control { font-size: 12px; padding: 6px 8px; }
  .modal-add-so .select2-container .select2-selection--single {
    border: 1px solid #ced4da;
    border-radius: 4px;
    height: 30px;
    display: flex;
    align-items: center;
  }
  .modal-add-so .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #495057;
    font-size: 12px;
    padding-left: 8px;
    line-height: 28px;
  }
  .modal-add-so .select2-container--default .select2-selection--single .select2-selection__arrow { height: 28px; }
  .modal-add-so.modal-dialog-scrollable .modal-content { max-height: calc(100vh - 2rem); }

  /* ================= Judul ================= */
  .so-title { font-weight: bold; margin: 0; white-space: nowrap; }

  /* ================= Tab segmented + jumlah data ================= */
  #soAwalTabs {
    display: inline-flex;
    flex-wrap: nowrap;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
    border-bottom: none;
    margin-bottom: 0;
  }
  #soAwalTabs .nav-item { margin-bottom: 0; flex: 0 0 auto; }
  #soAwalTabs .nav-link {
    border: none;
    border-radius: 7px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    letter-spacing: .02em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: auto;
  }
  #soAwalTabs .nav-link:hover { color: #0f172a; }
  #soAwalTabs .nav-link.active { background: #fff; color: #4f46e5; box-shadow: 0 1px 3px rgba(15,23,42,.12); }
  .soa-tabcount {
    min-width: 22px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    padding: 3px 7px;
    border-radius: 999px;
    background: #e2e8f0;
    color: #475569;
  }
  #soAwalTabs .nav-link.active .soa-tabcount { background: #eef2ff; color: #4f46e5; }

  /* ================= Command bar ================= */
  .soa-cmdbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .soa-cmdbar #soAwalTabs { flex-shrink: 0; }
  .soa-cmd-right { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; margin-left: auto; }
  .soa-searchwrap { position: relative; flex: 1 1 100px; max-width: 340px; display: flex; align-items: center; }
  .soa-searchwrap > i { position: absolute; left: 12px; font-size: 12px; color: #94a3b8; pointer-events: none; }
  .soa-searchwrap input {
    width: 100%; min-width: 0; border: 1px solid #e2e8f0; border-radius: 999px; background: #fff;
    padding: 8px 14px 8px 33px; font-size: 13px; outline: none; transition: .15s;
  }
  .soa-searchwrap input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
  .btn-filter-toggle {
    border: 1px solid #e2e8f0; background: #fff; color: #475569; border-radius: 999px;
    padding: 8px 16px; font-size: 13px; font-weight: 600; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 7px;
  }
  .btn-filter-toggle:hover, .btn-filter-toggle.on { border-color: #4f46e5; color: #4f46e5; }
  .soa-filter-count {
    min-width: 18px; text-align: center; font-size: 11px; font-weight: 700; line-height: 1;
    padding: 3px 6px; border-radius: 999px; background: #4f46e5; color: #fff;
  }

  /* ================= Keterangan cakupan + chip filter ================= */
  .soa-scopebar { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin: 12px 0 0; min-height: 26px; }
  .soa-scope { font-size: 12px; color: #64748b; display: inline-flex; align-items: center; gap: 6px; }
  .soa-scope i { color: #94a3b8; }
  #soa-chips { display: inline-flex; flex-wrap: wrap; gap: 6px; }
  .soa-chip {
    display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;
    padding: 3px 10px; border-radius: 999px; background: #eef2ff; color: #4f46e5; cursor: pointer;
  }
  .soa-chip i { font-size: 11px; }
  .soa-chip:hover { background: #e0e7ff; }
  #soa-chips-reset { font-size: 12px; color: #64748b; text-decoration: underline; }
  #soa-chips-reset:hover { color: #0f172a; }

  /* ================= Tabel modern ================= */
  .soa-table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
  .soa-table-card .table { margin-bottom: 0; font-size: 13px; }
  .soa-table-card thead th {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    font-weight: 700;
    padding: 10px 12px;
    white-space: nowrap;
  }
  .soa-table-card tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f4f8; vertical-align: middle; }
  .soa-table-card tbody tr:last-child td { border-bottom: none; }
  .soa-table-card tbody tr:hover { background: #f8fafc; }

  .soa-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
  .soa-pill .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
  .soa-pill-info { background: #f0f9ff; color: #0284c7; } .soa-pill-info .dot { background: #0284c7; }
  .soa-pill-warn { background: #fffbeb; color: #d97706; } .soa-pill-warn .dot { background: #d97706; }
  .soa-pill-primary { background: #eef2ff; color: #4f46e5; } .soa-pill-primary .dot { background: #4f46e5; }
  .soa-pill-success { background: #f0fdf4; color: #16a34a; } .soa-pill-success .dot { background: #16a34a; }
  .soa-pill-muted { background: #f1f5f9; color: #64748b; } .soa-pill-muted .dot { background: #64748b; }

  .soa-cust { display: flex; align-items: center; gap: 8px; }
  .soa-avatar {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800;
  }
  .soa-sub { font-size: 11px; color: #94a3b8; font-weight: 400; margin-top: 1px; }
  .soa-code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; font-weight: 700; color: #334155; }
  .soa-dash { color: #94a3b8; }

  /* Search bawaan DataTables disembunyikan (diganti search global) */
  .soa-table-card .dataTables_filter { display: none; }
  .soa-table-card .dataTables_length { font-size: 12px; color: #64748b; margin-bottom: 8px; }
  .soa-table-card .dataTables_length select { border: 1px solid #e2e8f0; border-radius: 6px; padding: 3px 6px; font-size: 12px; }
  .soa-table-card .dataTables_paginate .paginate_button { border-radius: 6px !important; font-size: 12px; }
  .soa-table-card .dataTables_paginate .paginate_button.current { background: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important; }

  /* Select2 di dalam modal filter tidak boleh menciut */
  #soaFilterModal .select2-container { width: 100% !important; }

  /* ================= Responsive ================= */
  @media (max-width: 1100px) {
    .soa-cmd-right { margin-left: 0; width: 100%; }
    .soa-searchwrap { flex: 1 1 auto; max-width: none; }
  }
  @media (max-width: 767.98px) {
    .soa-table-card table { white-space: nowrap; }
    .modal-dialog.modal-lg { max-width: 100%; margin: 0.5rem; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      text-align: left !important;
      float: none !important;
      width: 100%;
      margin-bottom: 10px;
    }
    .modal-footer { flex-wrap: wrap; }
    .modal-footer .btn { width: 100%; margin: 4px 0 !important; }
  }
  @media (max-width: 640px) {
    .soa-cmdbar { gap: 8px; width: 100%; }
    #soAwalTabs { width: 100%; }
    #soAwalTabs .nav-item { flex: 1; }
    #soAwalTabs .nav-link { width: 100%; padding: 7px 6px; font-size: 12px; gap: 5px; }
    .soa-table-card thead th, .soa-table-card tbody td { padding: 7px 8px; font-size: 12px; }
  }
</style>
@endpush

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.flatpickr')

@push('scripts')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('approval_spv').addEventListener('change', function () {
      const isChecked = this.checked;

      // Gunakan flex karena parent element menggunakan class bootstrap .row
      document.getElementById('kurs-group').style.display = isChecked ? 'flex' : 'none';
      document.getElementById('disc-percent-group').style.display = isChecked ? 'flex' : 'none';

      // Kurs hanya wajib jika Approval SPV dicentang
      document.getElementById('kurs').required = isChecked;

      // Kosongkan value jika checkbox dimatikan
      if (!isChecked) {
        document.getElementById('kurs').value = '';
        document.getElementById('disc_percent').value = '';
      }
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function() {

    // ============================================================
    //  DATATABLES: Draft / Submit / Terkirim
    // ============================================================
    var datatableUrl = '{{ route('superuser.penjualan.sales_order.json_awal') }}';
    var spinnerHtml = "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>";

    var soaTabList = ['draft', 'submit', 'selesai'];
    var soaTabName = { draft: 'DRAFT', submit: 'SUBMIT', selesai: 'TERKIRIM' };
    // 1 filter bersama untuk semua tab (tab-nya sendiri = status).
    var soaDateLabel = { draft: 'Tanggal Buat', submit: 'Tanggal (Buat/Submit)', selesai: 'Tanggal Resi' };
    var soaTables = {};
    var soaFilter = {};
    var soaKeyword = '';
    var activeTab = 'draft';

    function esc(s) {
      return $('<div>').text(s == null ? '' : s).html();
    }

    function pill(text, cls) {
      return '<span class="soa-pill ' + cls + '"><span class="dot"></span>' + esc(text) + '</span>';
    }

    function soAwalColumns(withResi) {
      var cols = [
        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
        {
          data: 'so_code',
          name: 'penjualan_so.so_code',
          render: function(data, type) {
            if (type !== 'display') return data;
            return '<span class="soa-code">' + esc(data || '-') + '</span>';
          }
        },
        {data: 'code', name: 'penjualan_so.code'},
        {data: 'approval_mou', name: 'penjualan_so.approval_mou', searchable: false},
        {data: 'nota_brand', name: 'penjualan_so.brand_name'},
        {
          data: 'customer',
          name: 'customer',
          orderable: false,
          render: function(data, type, row) {
            if (type !== 'display') return data;
            var txt = (data || '').trim() || '-';
            var ch = txt.charAt(0).toUpperCase() || '-';
            var info = row.tgl_info ? '<div class="soa-sub">' + esc(row.tgl_info) + '</div>' : '';
            return '<span class="soa-cust"><span class="soa-avatar">' + esc(ch) + '</span><span>' + esc(txt) + info + '</span></span>';
          }
        },
        {data: 'sales'},
        {data: 'so_created_by'},
        {
          data: 'status_label',
          name: 'status_label',
          orderable: false,
          searchable: false,
          render: function(data, type) {
            if (type !== 'display') return data;
            var cls = 'soa-pill-muted';
            if (data === 'AWAL') cls = 'soa-pill-muted';
            else if (data === 'REVISI') cls = 'soa-pill-warn';
            else if (data === 'SUBMIT') cls = 'soa-pill-primary';
            else if (data === 'DIPROSES') cls = 'soa-pill-info';
            else if (data === 'TERKIRIM') cls = 'soa-pill-success';
            return pill(data || '-', cls);
          }
        },
        {
          data: 'approval_mou_status',
          render: function(data, type) {
            if (type !== 'display') return data;
            if (data === 'APPROVED') return pill(data, 'soa-pill-success');
            if (data === 'NOT APPROVED') return pill(data, 'soa-pill-warn');
            return '<span class="soa-dash">-</span>';
          }
        },
        {data: 'action', orderable: false, searchable: false},
        // Kolom tersembunyi: dipakai untuk pengurutan default.
        {
          data: 'so_created_at',
          visible: false,
          render: {
            _: 'display',
            sort: 'timestamp'
          }
        }
      ];
      if (withResi) {
        cols.push({data: 'resi_at', name: 'resi_at', visible: false, searchable: false});
      }
      return cols;
    }

    function soAwalUrl(tab, params) {
      var clean = {};
      $.each(params || {}, function(k, v) {
        if (v !== '' && v != null) clean[k] = v;
      });
      return datatableUrl + '?' + $.param($.extend({ tab: tab }, clean));
    }

    function soAwalTable(selector, tab, withResi, order) {
      return $(selector).DataTable({
        language: { processing: spinnerHtml },
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        info: false,
        scrollX: false,
        autoWidth: false,
        ajax: {
          "url": soAwalUrl(tab, {}),
          "dataType": "json",
          "type": "GET",
          "data": { _token: "{{csrf_token()}}" }
        },
        columns: soAwalColumns(withResi),
        order: [order],
        pageLength: 10,
        lengthMenu: [
          [10, 20, 50],
          [10, 20, 50]
        ]
      });
    }

    // Semua tabel diinit di awal (anti bug kolom kosong saat pindah tab).
    // Indeks 11 = so_created_at (tersembunyi), 12 = resi_at (tersembunyi, khusus Terkirim).
    soaTables.draft = soAwalTable('#sales_order_awal_draft', 'draft', false, [11, 'asc']);
    soaTables.submit = soAwalTable('#sales_order_awal_submit', 'submit', false, [11, 'asc']);
    soaTables.selesai = soAwalTable('#sales_order_awal_selesai', 'selesai', true, [12, 'desc']);

    // Jumlah data per tab (badge) mengikuti hasil tiap tabel.
    soaTabList.forEach(function(tab) {
      soaTables[tab].on('draw.dt', function() {
        try {
          $('#cnt-' + tab).text(soaTables[tab].page.info().recordsDisplay);
        } catch (e) { /* abaikan sebelum draw pertama */ }
      });
    });

    function soaReload(tab) {
      soaTables[tab].ajax.url(soAwalUrl(tab, soaFilter)).load();
    }

    // ============================================================
    //  FILTER: flatpickr, chip, keterangan cakupan
    // ============================================================
    if (window.flatpickr) {
      $('.soa-flatrange').each(function() {
        flatpickr(this, { mode: 'range', dateFormat: 'Y-m-d', allowInput: false });
      });
    }

    function soaRangeOf(inputId) {
      var v = ($('#' + inputId).val() || '').trim();
      if (!v) return { from: '', to: '' };
      var p = v.split(' to ');
      return { from: (p[0] || '').trim(), to: ((p[1] || p[0] || '').trim()) };
    }

    function fmtYmd(s) {
      var p = (s || '').split('-');
      return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : s;
    }

    function soaRangeText(f) {
      return fmtYmd(f.date_from) + ' - ' + fmtYmd(f.date_to);
    }

    function soaScopeText(tab) {
      var f = soaFilter;
      var hasRange = !!(f.date_from && f.date_to);

      if (tab === 'draft') {
        return hasRange
          ? 'Draft dibuat ' + soaRangeText(f)
          : 'Semua draft yang belum disubmit, semua tanggal';
      }
      if (tab === 'submit') {
        if (hasRange) return 'Order dibuat atau disubmit ' + soaRangeText(f);
        if (soaKeyword) return 'Order submit dari semua tanggal';
        return 'Order dibuat atau disubmit hari ini';
      }
      if (hasRange) return 'Order terkirim, resi ' + soaRangeText(f);
      if (soaKeyword) return 'Order terkirim dari semua tanggal resi';
      return 'Order terkirim, resi bulan ini';
    }

    function soaRenderScopeAndChips() {
      var f = soaFilter;
      var chips = [];

      if (f.customer_name) chips.push({ key: 'customer_name', label: 'Customer: ' + f.customer_name });
      if (f.brand_name) chips.push({ key: 'brand_name', label: 'Brand: ' + f.brand_name });
      if (f.date_from && f.date_to) chips.push({ key: 'date', label: 'Tanggal: ' + soaRangeText(f) });

      var html = '';
      chips.forEach(function(c) {
        html += '<span class="soa-chip" data-key="' + c.key + '" title="Hapus filter">' + esc(c.label) + '<i class="fa fa-times"></i></span>';
      });
      $('#soa-chips').html(html);
      $('#soa-chips-reset').toggle(chips.length > 0);
      $('#soa-filter-count').text(chips.length).toggle(chips.length > 0);
      $('#soa-filter-toggle').toggleClass('on', chips.length > 0);
      $('#soa-scope-text').text(soaScopeText(activeTab));
    }

    $('#soa-chips').on('click', '.soa-chip', function() {
      var key = $(this).data('key');
      if (key === 'date') {
        delete soaFilter.date_from;
        delete soaFilter.date_to;
        var fp = ($('#f_date')[0] && $('#f_date')[0]._flatpickr) || null;
        if (fp) fp.clear();
        else $('#f_date').val('');
      } else {
        delete soaFilter[key];
      }
      soaReload(activeTab);
      soaRenderScopeAndChips();
    });

    $('#soa-chips-reset').on('click', function() {
      soaFilter = {};
      soaFillForm();
      soaReload(activeTab);
      soaRenderScopeAndChips();
    });

    // ---- Search global: satu kotak, berlaku ke semua tab (jumlah tab ikut berubah) ----
    var soaSearchTimer = null;
    $('#soa-search').on('keyup', function() {
      clearTimeout(soaSearchTimer);
      var v = $(this).val();
      soaSearchTimer = setTimeout(function() {
        soaKeyword = (v || '').trim();
        soaTabList.forEach(function(tab) {
          soaTables[tab].search(v).draw();
        });
        soaRenderScopeAndChips();
      }, 300);
    });

    // ---- Modal filter ----
    $('#soaFilterModal .js-select2').each(function() {
      $(this).select2({ dropdownParent: $('#soaFilterModal'), width: '100%' });
    });

    function soaFillForm() {
      $('#f_customer').val(soaFilter.customer_name || '').trigger('change');
      $('#f_brand').val(soaFilter.brand_name || '').trigger('change');
      var el = $('#f_date')[0];
      var fp = (el && el._flatpickr) || null;
      if (fp) {
        if (soaFilter.date_from && soaFilter.date_to) fp.setDate([soaFilter.date_from, soaFilter.date_to], false);
        else fp.clear();
      } else if (el) {
        $(el).val(soaFilter.date_from && soaFilter.date_to ? soaFilter.date_from + ' to ' + soaFilter.date_to : '');
      }
    }

    function soaCollect() {
      var r = soaRangeOf('f_date');
      var raw = {
        customer_name: $('#f_customer').val() || '',
        brand_name: $('#f_brand').val() || '',
        date_from: r.from,
        date_to: r.to
      };
      var out = {};
      $.each(raw, function(k, v) {
        if (v) out[k] = v;
      });
      return out;
    }

    $('#soa-filter-toggle').on('click', function() {
      $('#soa-modal-tabname').text(soaTabName[activeTab]);
      $('#f_date_label').text(soaDateLabel[activeTab]);
      soaFillForm();
      $('#soaFilterModal').modal('show');
    });

    $('#soa-apply').on('click', function() {
      soaFilter = soaCollect();
      soaReload(activeTab);
      soaRenderScopeAndChips();
      $('#soaFilterModal').modal('hide');
    });

    $('#soa-apply-reset').on('click', function() {
      soaFilter = {};
      soaFillForm();
      soaReload(activeTab);
      soaRenderScopeAndChips();
      $('#soaFilterModal').modal('hide');
    });

    $('#soAwalTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
      activeTab = $(e.target).data('tab') || 'draft';
      // Samakan ulang lebar kolom tiap pindah tab (anti tabel kosong/geser).
      soaTables[activeTab].columns.adjust();
      soaRenderScopeAndChips();
    });

    soaRenderScopeAndChips();

    // ============================================================
    //  MODAL ADD SO
    // ============================================================
    function formatRibuan(angka) {
      var numberString = angka.replace(/[^\d]/g, '');
      if (!numberString) return '';
      return numberString.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    $(document).on('input', '#kurs', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatRibuan(this.value);
      var newPos = this.value.length - cursorFromEnd;
      this.setSelectionRange(newPos, newPos);
    });

    // Select2 di dalam modal Add SO butuh dropdownParent supaya search box berfungsi
    $('#exampleModal .js-select2').each(function () {
      $(this).select2({
        dropdownParent: $('#exampleModal'),
        width: '100%'
      });
    });

    $('#addSO').on('click', function (e) {
      e.preventDefault();
      const form = document.getElementById('formAddSO');

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      var kurs = ($('#kurs').val() || '').replace(/\./g, '') || '1';

      if (!/^\d+$/.test(kurs)) {
        alert('Input "Kurs" hanya boleh berupa angka bulat positif.');
        $('#kurs').focus();
        return;
      }

      var customer = $('#account_member').val();
      var merek = $('#merek_ppi').val();
      var type_so = $('#so_type').val();
      var indent_so = $('#indent_so').val();
      var step_so = 1;
      var note = $('#editor').val() || '-';
      var approval_spv = $('#approval_spv').is(':checked') ? 1 : 0;

      // Tangkap 4 Inputan Diskon
      var disc_percent = $('#disc_percent').val() || 0;
      var disc_idr = $('#disc_idr').val() || 0;
      var disc_usd = $('#disc_usd').val() || 0;
      var disc_kemasan = $('#disc_kemasan').val() || 0;

      var packaging_id = $('#packaging_id').val() || '';

      if (!packaging_id) {
        Swal.fire('Perhatian', 'Kemasan wajib dipilih sebelum melanjutkan.', 'warning');
        $('#packaging_id').select2('open');
        return;
      }

      // Update template URL route
      var url = '{{ route('superuser.penjualan.sales_order.create',  [":step", ":member", ":brand", ":type", ":indent", ":approval", ":note", ":kurs", ":disc_percent", ":disc_idr", ":disc_usd", ":disc_kemasan", ":packaging"]) }}';

      url = url.replace(':member', customer);
      url = url.replace(':brand', merek);
      url = url.replace(':type', type_so);
      url = url.replace(':indent', indent_so);
      url = url.replace(':step', step_so);
      url = url.replace(':approval', approval_spv);
      url = url.replace(':kurs', kurs);
      url = url.replace(':note', encodeURIComponent(note));
      url = url.replace(':disc_percent', disc_percent);
      url = url.replace(':disc_idr', disc_idr);
      url = url.replace(':disc_usd', disc_usd);
      url = url.replace(':disc_kemasan', disc_kemasan);
      url = url.replace(':packaging', packaging_id);

      $.ajax({
        url: url,
        type: 'GET',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(data) {
          window.location.href = url;
        }
      });
    });

    $('#exampleModal').on('hidden.bs.modal', function (e) {
      $(this).removeData();
      $(this).find('form')[0].reset();
      $(this).find('.js-select2').val(null).trigger('change');

      // UX: Sembunyikan kembali inputan opsional
      document.getElementById('kurs-group').style.display = 'none';
      document.getElementById('disc-percent-group').style.display = 'none';
      document.getElementById('kurs').required = false;
    });

    $('#exampleModal').on('shown.bs.modal', function () {
      $('#so_type').trigger('change');

      // UX: Fokus otomatis ke dropdown customer setelah modal animasi selesai
      setTimeout(function() {
        $('#account_member').select2('open');
      }, 100);
    });

    $("#test").on("click", function(e) {
      e.preventDefault();
      addListItem();
    });

    function addListItem() {
      var text = document.getElementById('editor').value;
      var listNumberRegex = /^[0-9]+(?=\.)/gm;
      var existingNums = [];
      var num;

      while ((num = listNumberRegex.exec(text)) !== null) {
        existingNums.push(num);
      }

      existingNums.sort();

      var addListItemNum;
      if (existingNums.length > 0) {
        addListItemNum = parseInt(existingNums[existingNums.length - 1], 10) + 1;
      } else {
        addListItemNum = 1;
      }

      var exp = '\n' + addListItemNum + '.\xa0';
      text = text.concat(exp);
      document.getElementById('editor').value = text;
    }
  })
</script>
@endpush