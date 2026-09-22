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
        <label class="so-flabel" for="customer_name">Customer :</label>
        <select class="form-control js-select2" id="customer_name" name="customer_name" data-placeholder="Cari Customer">
          <option value="">All</option>
          @foreach($other_address as $key)
          <option value="{{ $key->id }}">{{ $key->name }} {{$key->text_kota}}</option>
          @endforeach
        </select>
        <label class="so-flabel" for="status_so">Status :</label>
        <select class="form-control js-select2" name="status_so" id="status_so">
          <option value="">Pilih Status</option>
          <option value="AWAL">AWAL</option>
          <option value="REVISI">REVISI</option>
          <option value="TUTUP">TUTUP</option>
        </select>
        <button class="btn bg-gd-corporate border-0 text-white" id="btn-filter"><i class="fa fa-search ml-10"></i> <span class="d-inline d-md-none">Filter</span></button>
      </div>
    </div>

        <div class="d-flex flex-wrap" style="gap:8px;">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-plus mr-10"></i> Add SO
          </button>

          @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
            <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#modal-manage">Export</button>
          @endif

          <!-- import migrasi SO -->
          <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#modal-manage">Manage</button>

          <a href="{{ route('superuser.penjualan.migrasi_so.prosesMigrasi') }}" class="btn btn-outline-info">Migrasi SO</a>

          @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
            <a href="{{ route('superuser.penjualan.sales_order.archive_awal') }}" class="btn btn-outline-warning">
              <i class="fa fa-archive mr-10"></i> Riwayat Archive
            </a>
          @endif
        </div>
        <br>
        <div class="table-responsive">
        <table class="table table-bordred table-striped" style="width:100%" id="sales_order_awal">
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

                  <!-- DISC USD DIPINDAH KE BAWAH KURS -->
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
                  
                  <div class="form-group row align-items-center mb-2">
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
                
                <div class="form-group row align-items-center mb-1">
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
      document.getElementById('kurs-group').style.display = isChecked ? 'block' : 'none';
      document.getElementById('disc-percent-group').style.display = isChecked ? 'block' : 'none';
    });    
  });
</script>
<script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2();

      let datatableUrl = '{{ route('superuser.penjualan.sales_order.json_awal') }}';
      let firstDatatableUrl = datatableUrl + '?status_so=all' + '&customer_name=all';

      var datatable =  $('#sales_order_awal').DataTable( {
            language: {
                processing: "<span class='fa-stack fa-lg'>\n\
                                        <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                                </span>",
            },
            processing: true,
            serverSide: true,
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
                {data: 'approval_mou', name: 'penjualan_so.approval_mou'},
                {data: 'nota_brand', name: 'penjualan_so.brand_name'},
                {data: 'customer'},
                {data: 'sales'},
                {data: 'so_created_by'},
                {
                    data: 'so_created_at',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }
                },
                {data: 'status_so'},
                {data: 'approval_mou_status'},
                {data: 'action'},
            ],
            order: [
                [8, 'asc'],
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50],
                [10, 20, 50]
            ],
        });

        $('#btn-filter').on('click', function(e) {
            e.preventDefault();
            var status = $('#status_so').val();
            var customer = $('#customer_name').val();
            let newDatatableUrl = datatableUrl + '?status_so=' + status + '&customer_name=' + customer;
            datatable.ajax.url(newDatatableUrl).load();
        })

        $('.js-select2').select2();

        // Lebarkan select Customer pas selebar opsi terpanjang
        function autoSizeCustomerSelect() {
          var $sel = $('#customer_name');
          var $container = $('#customer_name + .select2-container');
          if (!$sel.length || !$container.length) {
            return;
          }
          var $tester = $('<span>').css({
            position: 'absolute',
            visibility: 'hidden',
            whiteSpace: 'nowrap',
            fontSize: '13px',
            fontFamily: $container.css('font-family')
          }).appendTo('body');
          var max = 0;
          $sel.find('option').each(function () {
            $tester.text($(this).text());
            max = Math.max(max, $tester.width());
          });
          $tester.remove();
          $container.css({ flex: '0 0 auto', width: Math.ceil(max + 72) + 'px', maxWidth: '100%' });
        }
        autoSizeCustomerSelect();

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
        });

        $('#exampleModal').on('shown.bs.modal', function () {
            $('#so_type').trigger('change');
        });

        $('a[href^="#"]').on('click', function(event) {
            var target = $( $(this).attr('href') );
            target.fadeToggle(100);
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
