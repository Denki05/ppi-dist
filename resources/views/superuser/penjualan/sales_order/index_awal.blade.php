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
      <div class="form-group so-title-filter mb-0">
        <h4 class="so-title">#SALES ORDER {{ $step_txt }}</h4>
      </div>
    </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:8px;">
        
        <!-- COMMAND BAR OPTIONS (Diperbarui agar lebih rapi) -->
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
          <ul class="nav nav-tabs soa-segmented" id="soAwalTabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#so-tab-default" role="tab" data-tab="default"><i class="fa fa-inbox mr-5"></i>DEFAULT</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#so-tab-proses" role="tab" data-tab="proses"><i class="fa fa-spinner mr-5"></i>PROSES</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#so-tab-history" role="tab" data-tab="history"><i class="fa fa-history mr-5"></i>HISTORY</a>
            </li>
          </ul>
          <div class="soa-cmd-right">
            <div class="soa-searchwrap">
              <i class="fa fa-search"></i>
              <input type="text" id="soa-search" placeholder="Cari kode, nota, customer, brand…" autocomplete="off">
            </div>
            <button type="button" class="btn btn-filter-toggle" id="soa-filter-toggle"><i class="fa fa-sliders mr-5"></i>Filter<span class="dot" id="soa-filter-dot" style="display:none;"></span></button>
            <span class="soa-count" id="soa-count"><i class="fa fa-list mr-5"></i><span id="soa-count-num">0</span> data</span>
          </div>
        </div>
        </div>
        @php
          $bulanList = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
          $tahunNow = (int) date('Y');
          $tahunList = range($tahunNow - 4, $tahunNow);
        @endphp
        <div class="tab-content pt-10">
          {{-- ================= TAB DEFAULT: draft hari ini + advanced search ================= --}}
          <div class="tab-pane fade show active" id="so-tab-default" role="tabpanel">
            <div class="table-responsive soa-table-card">
            <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal_default">
              <thead>
                <th>#</th>
                <th>Code</th>
                <th>Nota</th>
                <th>Approval</th>
                <th>Brand</th>
                <th>Customer</th>
                <th>Sales</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Status Approval</th>
                <th>Action</th>
              </thead>
              <tbody>
              </tbody>
            </table>
            </div>
          </div>
          {{-- ================= TAB PROSES:roso lewat hari + lanjutan belum resi ================= --}}
          <div class="tab-pane fade" id="so-tab-proses" role="tabpanel">
            <div class="table-responsive soa-table-card">
            <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal_proses">
              <thead>
                <th>#</th>
                <th>Code</th>
                <th>Nota</th>
                <th>Approval</th>
                <th>Brand</th>
                <th>Customer</th>
                <th>Sales</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Status Approval</th>
                <th>Action</th>
              </thead>
              <tbody>
              </tbody>
            </table>
            </div>
          </div>
          {{-- ================= TAB HISTORY: sudah update resi, terbaru dulu ================= --}}
          <div class="tab-pane fade" id="so-tab-history" role="tabpanel">
            <div class="table-responsive soa-table-card">
            <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal_history">
              <thead>
                <th>#</th>
                <th>Code</th>
                <th>Nota</th>
                <th>Approval</th>
                <th>Brand</th>
                <th>Customer</th>
                <th>Sales</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Tgl Resi</th>
                <th>Status</th>
                <th>Status Approval</th>
                <th>Action</th>
              </thead>
              <tbody>
              </tbody>
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
  <!-- HAPUS 'modal-dialog-centered' agar modal muncul standar di atas -->
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light">
        <h5 class="modal-title font-weight-bold" style="font-size: 15px;">
          <i class="fa fa-sliders text-primary mr-2"></i>Filter <span id="soa-modal-tabname" class="text-primary">DEFAULT</span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <!-- TAMBAH 'text-left' agar label tidak lari ke tengah -->
      <div class="modal-body text-left p-4">
        
        <!-- FORM DEFAULT -->
        <div id="fsec-default">
          <div class="form-group text-left mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_customer_default">Customer</label>
            <select class="form-control js-select2" id="f_customer_default" data-placeholder="Semua Customer" style="width: 100%;">
              <option value="">Semua</option>
              @foreach($other_address as $key)
              <option value="{{ $key->name }}">{{ $key->name }} {{$key->text_kota}}</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_status_default">Status</label>
              <select class="form-control js-select2" id="f_status_default" style="width: 100%;">
                <option value="">Semua</option>
                <option value="AWAL">AWAL</option>
                <option value="REVISI">REVISI</option>
              </select>
            </div>
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_brand_default">Brand</label>
              <select class="form-control js-select2" id="f_brand_default" data-placeholder="Semua Brand" style="width: 100%;">
                <option value="">Semua</option>
                @foreach($brand as $br)
                <option value="{{ $br->brand_name }}">{{ $br->brand_name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group text-left mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 12px;">Tanggal Buat</label>
            <div class="row">
              <div class="col-6"><input type="date" class="form-control" id="f_from_default"></div>
              <div class="col-6"><input type="date" class="form-control" id="f_to_default"></div>
            </div>
          </div>
        </div>

        <!-- FORM PROSES -->
        <div id="fsec-proses" style="display:none;">
          <div class="row">
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_customer_proses">Customer</label>
              <select class="form-control js-select2" id="f_customer_proses" data-placeholder="Semua Customer" style="width: 100%;">
                <option value="">Semua</option>
                @foreach($other_address as $key)
                <option value="{{ $key->name }}">{{ $key->name }} {{$key->text_kota}}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_status_proses">Status</label>
              <select class="form-control js-select2" id="f_status_proses" style="width: 100%;">
                <option value="">Semua</option>
                <option value="AWAL">AWAL</option>
                <option value="REVISI">REVISI</option>
                <option value="LANJUTAN">LANJUTAN</option>
                <option value="TUTUP">TUTUP</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_bulan_proses">Bulan</label>
              <select class="form-control js-select2" id="f_bulan_proses" style="width: 100%;">
                <option value="">Semua</option>
                @foreach($bulanList as $num => $nama)
                <option value="{{ $num }}">{{ $nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_tahun_proses">Tahun</label>
              <select class="form-control js-select2" id="f_tahun_proses" style="width: 100%;">
                <option value="">Semua</option>
                @foreach($tahunList as $th)
                <option value="{{ $th }}">{{ $th }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group text-left mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 12px;">Tanggal Buat</label>
            <div class="row">
              <div class="col-6"><input type="date" class="form-control" id="f_from_proses"></div>
              <div class="col-6"><input type="date" class="form-control" id="f_to_proses"></div>
            </div>
          </div>
        </div>

        <!-- FORM HISTORY -->
        <div id="fsec-history" style="display:none;">
          <div class="form-group text-left mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_customer_history">Customer</label>
            <select class="form-control js-select2" id="f_customer_history" data-placeholder="Semua Customer" style="width: 100%;">
              <option value="">Semua</option>
              @foreach($other_address as $key)
              <option value="{{ $key->name }}">{{ $key->name }} {{$key->text_kota}}</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_bulan_history">Bulan Resi</label>
              <select class="form-control js-select2" id="f_bulan_history" style="width: 100%;">
                @foreach($bulanList as $num => $nama)
                <option value="{{ $num }}" {{ $num == date('n') ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6 text-left mb-3">
              <label class="font-weight-bold text-dark" style="font-size: 12px;" for="f_tahun_history">Tahun Resi</label>
              <select class="form-control js-select2" id="f_tahun_history" style="width: 100%;">
                @foreach($tahunList as $th)
                <option value="{{ $th }}" {{ $th == date('Y') ? 'selected' : '' }}>{{ $th }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Area Tombol -->
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
  <!-- Pastikan ada class "modal-dialog-scrollable" agar footer selalu terlihat -->
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
            
            {{-- KOLOM KIRI : Selesaikan dari atas ke bawah --}}
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
                  
                  <!-- ID ditambahkan di sini untuk trigger toggle javascript -->
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

            {{-- KOLOM KANAN : Langkah Terakhir sebelum Simpan --}}
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
                
                <!-- ID ditambahkan di sini untuk trigger toggle javascript -->
                <div class="form-group row align-items-center mb-1" id="kurs-group" style="display: none;">
                  <label class="col-sm-4 col-form-label text-left" for="kurs">Kurs <span class="text-danger">*</span></label>
                  <div class="col-sm-8">
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                      <input class="form-control" type="text" name="kurs" id="kurs" inputmode="numeric" placeholder="15.500" required>
                    </div>
                  </div>
                </div>

              </div>

              <!-- CATATAN MENDAPATKAN RUANG LEBIH LUAS -->
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
  /* === Modal Add SO : Sangat Kompak & Padat === */
  #exampleModal .modal-dialog {
    width: 98%;
    max-width: 1050px;
  }
  
  /* Desain Kartu (Card) */
  .so-card {
    border: 1px solid #e7eaee;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 8px;
    background: #ffffff;
  }
  
  /* Header Kartu */
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
  
  /* Tipografi Label */
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
  
  /* Memperkecil tinggi Input Standard & Textarea */
  .modal-add-so .form-control-sm, 
  .modal-add-so .input-group-sm > .form-control,
  .modal-add-so .input-group-sm > .input-group-prepend > .input-group-text,
  .modal-add-so .input-group-sm > .input-group-append > .input-group-text {
    height: 30px;
    padding: 2px 8px;
    font-size: 12px;
  }
  .modal-add-so textarea.form-control {
    font-size: 12px;
    padding: 6px 8px;
  }
  
  /* Select2 Styling Fix - Diperpendek */
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
  .modal-add-so .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px;
  }

  /* Memaksa batas maksimal tinggi modal jika layar sangat kecil (agar bisa discroll) */
  .modal-add-so.modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 2rem);
  }

  /* === Judul + filter 1 garis (dengan jarak) === */
  .so-title-filter {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
  }
  .so-title-filter .so-title {
    font-weight: bold;
    margin: 0 auto 0 0;
    white-space: nowrap;
  }
  .so-title-filter .so-flabel {
    margin: 0;
    font-weight: 600;
    white-space: nowrap;
  }
  #customer_name + .select2-container {
    flex: 0 0 auto;
    min-width: 0;
    max-width: 100%;
  }
  #status_so + .select2-container {
    flex: 0 1 180px;
    min-width: 150px;
  }
  .so-title-filter #btn-filter {
    flex: 0 0 auto;
    padding-left: 28px;
    padding-right: 28px;
  }

  /* ===== SO AWAL modern: segmented tabs ===== */
  #soAwalTabs {
    display: inline-flex;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
    border-bottom: none;
    margin-bottom: 12px;
  }
  #soAwalTabs .nav-item { margin-bottom: 0; }
  #soAwalTabs .nav-link {
    border: none;
    border-radius: 7px;
    padding: 7px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    letter-spacing: .02em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  #soAwalTabs .nav-link:hover { color: #0f172a; }
  #soAwalTabs .nav-link.active {
    background: #fff;
    color: #4f46e5;
    box-shadow: 0 1px 3px rgba(15,23,42,.12);
  }

  /* ===== Modal filter (1 popup semua tab) ===== */
  .soa-modal { border: 0; border-radius: 14px; overflow: hidden; }
  .soa-modal .modal-header { background: #f2f5fa; border-bottom: 1px solid #e6eaf1; padding: 13px 18px; }
  .soa-modal .modal-title { font-weight: 800; font-size: 15px; color: #1c2733; }
  .soa-modal .modal-title #soa-modal-tabname { color: #4f46e5; }
  .soa-modal .modal-body { padding: 16px 18px; }
  .soa-modal .so-flabel {
    display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: #7a8494; margin-bottom: 4px;
  }
  .soa-modal .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 13px; }
  .soa-modal .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
  .soa-modal .btn { border-radius: 8px; }
  .soa-modal .btn-reset { border: 1px solid #e2e8f0; background: #fff; color: #64748b; }
  .soa-modal .btn-reset:hover { background: #f8fafc; color: #0f172a; }

  /* ===== Tabel modern ===== */
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

  /* ===== Command bar ramping: tab + search global + filter + count ===== */
  .soa-cmdbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
  .soa-cmdbar #soAwalTabs { margin-bottom: 0; flex-shrink: 0; }
  
  /* UBAH DI SINI: flex-wrap: nowrap agar tidak turun baris */
  .soa-cmd-right { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; margin-left: auto; }
  
  @media (max-width: 1100px) {
    .soa-cmd-right { margin-left: 0; width: 100%; }
    .soa-searchwrap { flex: 1 1 auto; max-width: none; }
  }
  @media (max-width: 640px) {
    .soa-cmdbar { gap: 8px; }
    #soAwalTabs { width: 100%; }
    #soAwalTabs .nav-item { flex: 1; }
    #soAwalTabs .nav-link { width: 100%; justify-content: center; padding: 7px 6px; font-size: 12px; }
    .soa-table-card thead th, .soa-table-card tbody td { padding: 7px 8px; font-size: 12px; }
    .soa-count { margin-left: auto; }
  }
  
  /* UBAH DI SINI: flex basis diperkecil ke 100px agar search box mau mengalah (menyusut) saat layar sempit */
  .soa-searchwrap { position: relative; flex: 1 1 100px; max-width: 340px; display: flex; align-items: center; }
  .soa-searchwrap > i { position: absolute; left: 12px; font-size: 12px; color: #94a3b8; pointer-events: none; }
  
  /* UBAH DI SINI: tambahkan min-width: 0 agar input text tidak memaksakan lebar saat terdesak */
  .soa-searchwrap input {
    width: 100%; border: 1px solid #e2e8f0; border-radius: 999px; background: #fff;
    padding: 8px 14px 8px 33px; font-size: 13px; outline: none; transition: .15s;
    min-width: 0; 
  }
  .soa-searchwrap input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
  .btn-filter-toggle {
    border: 1px solid #e2e8f0; background: #fff; color: #475569; border-radius: 999px;
    padding: 8px 16px; font-size: 13px; font-weight: 600; position: relative; white-space: nowrap;
  }
  .btn-filter-toggle:hover, .btn-filter-toggle.on { border-color: #4f46e5; color: #4f46e5; }
  .btn-filter-toggle .dot {
    position: absolute; top: 6px; right: 8px; width: 8px; height: 8px; border-radius: 50%;
    background: #f59e0b; border: 2px solid #fff;
  }
  .soa-count {
    display: inline-flex; align-items: center; gap: 2px; font-size: 12px; color: #64748b;
    background: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 13px; border-radius: 999px; white-space: nowrap;
  }
  .soa-count #soa-count-num { font-weight: 800; color: #0f172a; }

  /* ===== Sel tabel: avatar customer + kode mono ===== */
  .soa-cust { display: flex; align-items: center; gap: 8px; }
  .soa-avatar {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800;
  }
  .soa-code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; font-weight: 700; color: #334155; }

  /* Sembunyikan search bawaan DataTables (diganti search global) */
  .soa-table-card .dataTables_filter { display: none; }
  .soa-table-card .dataTables_length { font-size: 12px; color: #64748b; margin-bottom: 8px; }
  .soa-table-card .dataTables_length select { border: 1px solid #e2e8f0; border-radius: 6px; padding: 3px 6px; font-size: 12px; }
  .soa-table-card .dataTables_paginate .paginate_button { border-radius: 6px !important; font-size: 12px; }
  .soa-table-card .dataTables_paginate .paginate_button.current { background: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important; }
  /* === Sejajarkan filter Customer - Status - tombol Search === */
  #customer_name + .select2-container .select2-selection--single {
    height: 38px;
    display: flex;
    align-items: center;
    border-color: #ced4da;
  }
  #customer_name + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 1.2;
    padding-top: 0;
    padding-bottom: 0;
  }
  #customer_name + .select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px;
    top: 0;
  }
  #status_so {
    height: 38px;
  }
  #btn-filter {
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  /* Label filter tanpa padding atas-bawah agar teksnya tepat di tengah select */
  label[for="customer_name"],
  label[for="status_so"] {
    padding-top: 0;
    padding-bottom: 0;
  }
  /* === Responsive untuk HP === */
  @media (max-width: 767.98px) {
    #sales_order_awal {
      white-space: nowrap;
    }
    .modal-dialog.modal-lg {
      max-width: 100%;
      margin: 0.5rem;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      text-align: left !important;
      float: none !important;
      width: 100%;
      margin-bottom: 10px;
    }
    .modal-footer {
      flex-wrap: wrap;
    }
    .modal-footer .btn {
      width: 100%;
      margin: 4px 0 !important;
    }

    /* Fix Select2 menciut di dalam tab filter yang disembunyikan */
  #soaFilterModal .select2-container {
    width: 100% !important;
  }
  
  /* Styling tambahan untuk Modal Filter agar rapi */
  .soa-modal .modal-body { padding: 20px 24px; }
  .soa-modal .so-flabel {
    display: block; 
    font-size: 11px; 
    font-weight: 700; 
    color: #495057; 
    margin-bottom: 6px;
    text-transform: none;
    letter-spacing: normal;
  }
  }
</style>
@endpush

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('approval_spv').addEventListener('change', function () {
      const isChecked = this.checked;
      
      // Gunakan flex karena parent element menggunakan class bootstrap .row
      document.getElementById('kurs-group').style.display = isChecked ? 'flex' : 'none';
      document.getElementById('disc-percent-group').style.display = isChecked ? 'flex' : 'none';
      
      // Kosongkan value jika checkbox dimatikan
      if(!isChecked) {
          document.getElementById('kurs').value = '';
          document.getElementById('disc_percent').value = '';
      }
    });    
  });
</script>

<script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2();

      let datatableUrl = '{{ route('superuser.penjualan.sales_order.json_awal') }}';
      let spinnerHtml = "<span class='fa-stack fa-lg'>\n\
                                        <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                                </span>";

      function soAwalColumns(withResi) {
          let cols = [
              {data: 'DT_RowIndex', name: 'id'},
              {
                  data: 'so_code',
                  name: 'penjualan_so.so_code',
                  render: function(data, type) {
                      if (type !== 'display') return data;
                      return '<span class="soa-code">' + (data || '-') + '</span>';
                  }
              },
              {data: 'code', name: 'penjualan_so.code'},
              {data: 'approval_mou', name: 'penjualan_so.approval_mou'},
              {data: 'nota_brand', name: 'penjualan_so.brand_name'},
              {
                  data: 'customer',
                  render: function(data, type) {
                      if (type !== 'display') return data;
                      let txt = data || '-';
                      let ch = txt.trim().charAt(0).toUpperCase() || '-';
                      return '<span class="soa-cust"><span class="soa-avatar">' + ch + '</span><span>' + txt + '</span></span>';
                  }
              },
              {data: 'sales'},
              {data: 'so_created_by'},
              {
                  data: 'so_created_at',
                  render: {
                      _: 'display',
                      sort: 'timestamp'
                  }
              },
          ];
          if (withResi) {
              cols.push({data: 'resi_at', name: 'resi_at'});
          }
          cols.push({
              data: 'status_so',
              render: function(data, type) {
                  if (type !== 'display') return data;
                  let cls = 'soa-pill-muted';
                  if (data === 'AWAL') cls = 'soa-pill-info';
                  else if (data === 'REVISI') cls = 'soa-pill-warn';
                  else if (data === 'LANJUTAN') cls = 'soa-pill-primary';
                  else if (data === 'TUTUP') cls = 'soa-pill-success';
                  return '<span class="soa-pill ' + cls + '"><span class="dot"></span>' + (data || '-') + '</span>';
              }
          });
          cols.push({
              data: 'approval_mou_status',
              render: function(data, type) {
                  if (type !== 'display') return data;
                  let cls = 'soa-pill-muted';
                  if (data === 'APPROVED') cls = 'soa-pill-success';
                  else if (data === 'NOT APPROVED') cls = 'soa-pill-warn';
                  return '<span class="soa-pill ' + cls + '"><span class="dot"></span>' + (data || '-') + '</span>';
              }
          });
          cols.push({data: 'action'});
          return cols;
      }

      function soAwalTable(selector, tab, withResi, defaultOrder, extraParams) {
        return $(selector).DataTable({
            language: { processing: spinnerHtml },
            processing: true,
            serverSide: true,
            searching: true,
            paging: true,
            info: false,
            
            scrollX: false, // <--- UBAH DI SINI: Matikan scrollX bawaan DataTables
            
            autoWidth: false,
            ajax: {
                "url": soAwalUrl(tab, extraParams || {}),
                "dataType": "json",
                "type": "GET",
                "data": { _token: "{{csrf_token()}}" }
            },
            columns: soAwalColumns(withResi),
            order: [defaultOrder],
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50],
                [10, 20, 50]
            ],
        });
    }

      function soAwalUrl(tab, params) {
          let q = $.param($.extend({ tab: tab }, params));
          return datatableUrl + '?' + q;
      }

      // Semua tabel diinit di awal (anti bug kolom kosong saat pindah tab).
      var datatableDefault = soAwalTable('#sales_order_awal_default', 'default', false, [8, 'asc']);
      var datatableProses = soAwalTable('#sales_order_awal_proses', 'proses', false, [8, 'desc']);
      var datatableHistory = soAwalTable('#sales_order_awal_history', 'history', true, [9, 'desc'], {
          bulan: $('#f_bulan_history').val(),
          tahun: $('#f_tahun_history').val()
      });

      // ===== Command bar global: 1 search + popup filter untuk semua tab =====
      var activeTab = 'default';

      function soaActiveTable() {
          if (activeTab === 'proses') return datatableProses;
          if (activeTab === 'history') return datatableHistory;
          return datatableDefault;
      }

      function soaUpdateCount() {
          let t = soaActiveTable();
          if (!t) { $('#soa-count-num').text('0'); return; }
          try {
              let info = t.page.info();
              $('#soa-count-num').text(info.recordsDisplay);
          } catch (e) { /* abaikan sebelum draw pertama */ }
      }

      function soaSyncSearch() {
          let t = soaActiveTable();
          $('#soa-search').val(t ? t.search() : '');
      }

      var soaSearchTimer = null;
      $('#soa-search').on('keyup', function() {
          clearTimeout(soaSearchTimer);
          let v = $(this).val();
          soaSearchTimer = setTimeout(function() {
              let t = soaActiveTable();
              if (t) t.search(v).draw();
          }, 300);
      });

      // Popup filter: tampilkan seksi sesuai tab aktif.
      $('#soa-filter-toggle').on('click', function() {
          $('#fsec-default, #fsec-proses, #fsec-history').hide();
          $('#fsec-' + activeTab).show();
          $('#soa-modal-tabname').text(activeTab.toUpperCase());
          $('#soaFilterModal').modal('show');
      });

      // Select2 di dalam modal butuh dropdownParent agar tidak terpotong.
      $('#soaFilterModal .js-select2').each(function() {
          $(this).select2({ dropdownParent: $('#soaFilterModal'), width: '100%' });
      });

      function soaCollectParams(tab) {
          if (tab === 'proses') {
              return {
                  customer_name: $('#f_customer_proses').val(),
                  status_so: $('#f_status_proses').val(),
                  bulan: $('#f_bulan_proses').val(),
                  tahun: $('#f_tahun_proses').val(),
                  date_from: $('#f_from_proses').val(),
                  date_to: $('#f_to_proses').val()
              };
          }
          if (tab === 'history') {
              return {
                  customer_name: $('#f_customer_history').val(),
                  bulan: $('#f_bulan_history').val(),
                  tahun: $('#f_tahun_history').val()
              };
          }
          return {
              customer_name: $('#f_customer_default').val(),
              status_so: $('#f_status_default').val(),
              brand_name: $('#f_brand_default').val(),
              date_from: $('#f_from_default').val(),
              date_to: $('#f_to_default').val()
          };
      }

      $('#soa-apply').on('click', function() {
          let t = soaActiveTable();
          if (!t) return;
          t.ajax.url(soAwalUrl(activeTab, soaCollectParams(activeTab))).load();
          soaRefreshDot(activeTab);
          $('#soaFilterModal').modal('hide');
      });

      $('#soa-apply-reset').on('click', function() {
          let t = soaActiveTable();
          if (activeTab === 'history') {
              let mm = String(new Date().getMonth() + 1);
              let yyyy = String(new Date().getFullYear());
              $('#f_customer_history').val('').trigger('change');
              $('#f_bulan_history').val(mm).trigger('change');
              $('#f_tahun_history').val(yyyy).trigger('change');
              if (t) t.ajax.url(soAwalUrl('history', { bulan: mm, tahun: yyyy })).load();
          } else {
              $('#fsec-' + activeTab).find('input[type="date"]').val('');
              $('#fsec-' + activeTab).find('select').val('').trigger('change');
              if (t) t.ajax.url(soAwalUrl(activeTab, {})).load();
          }
          soaRefreshDot(activeTab);
      });

      function soaRefreshDot(tab) {
          let has = false;
          $('#fsec-' + tab).find('select[id*="customer"], select[id*="status"], select[id*="brand"], input[type="date"]').each(function() {
              if ($(this).val()) has = true;
          });
          if (activeTab === tab) $('#soa-filter-dot').toggle(has);
      }

      $('#soAwalTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
          activeTab = $(e.target).data('tab') || 'default';
          // Samakan ulang lebar kolom tiap pindah tab (anti tabel kosong/geser).
          let t = soaActiveTable();
          if (t) t.columns.adjust();
          soaSyncSearch();
          soaUpdateCount();
          soaRefreshDot(activeTab);
      });

      ['default', 'proses', 'history'].forEach(function(tab) {
          $('#so-tab-' + tab).on('draw.dt', '#sales_order_awal_' + tab, soaUpdateCount);
      });

        $('.js-select2').select2();

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
              success:function(data)
              {
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
        });

        $('#exampleModal').on('shown.bs.modal', function () {
            $('#so_type').trigger('change');
            
            // UX: Fokus otomatis ke dropdown customer setelah modal animasi selesai
            setTimeout(function() {
                $('#account_member').select2('open');
            }, 100);
        });

        $("#test").on("click",function(e){
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