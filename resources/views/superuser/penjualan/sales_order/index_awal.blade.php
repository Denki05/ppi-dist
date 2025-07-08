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
      <div class="form-group row">
      <div class="col-md-9">
        <h4 style="font-weight: bold;">#SALES ORDER {{ $step_txt }}</h4>
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="customer_name">Customer :</label>
              <div class="col-md-4">
                <select class="form-control js-select2" id="customer_name" name="customer_name" data-placeholder="Cari Customer">
                  <option value="">All</option>
                  @foreach($other_address as $key)
                  <option value="{{ $key->id }}">{{ $key->name }} {{$key->text_kota}}</option>
                  @endforeach
                </select>
              </div>
              <label class="col-md-2 col-form-label text-left" for="status_so">Status :</label>
              <div class="col-md-4">
                <select class="form-control js-select2" name="status_so" id="status_so">
                  <option value="">Pilih Status</option>
                  <option value="AWAL">AWAL</option>
                  <option value="REVISI">REVISI</option>
                  <option value="TUTUP">TUTUP</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <div class="col-md-12 text-center">
                <!-- <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a> -->
                <button class="btn bg-gd-corporate border-0 text-white pl-50 pr-50" id="btn-filter"><i class="fa fa-search ml-10"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
          <i class="fa fa-plus mr-10"></i> Add SO
        </button>

        @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
          <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Export</button>
        @endif

        <!-- import migrasi SO -->
        <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

        <!-- <button type="button" class="btn btn-outline-info"><i class="fa fa-print"></i> GET SO</button> -->

        <br>
        <br>
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

<!-- modal add so -->
<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">#Add SO {{$step_txt}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formAddSO">
          @csrf
          <div class="row">
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Customer </b> <span class="text-danger">*</span></span>
                <select class="js-select2 form-control account_member" id="account_member" name="member_name" style="width:100%;" data-placeholder="Cari Customer" required>
                  <option value="">Pilih Customer</option>
                  @foreach($other_address as $row)
                  <option value="{{$row->id}}">{{$row->name}}  {{$row->text_kota}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Brand </b> <span class="text-danger">*</span></span>
                <select class="js-select2 form-control" id="merek_ppi" name="brand_name" style="width:100%;" data-placeholder="Pilih Brand" required>
                  <option value="">Pilih Brand</option>
                  @foreach($brand as $row)
                  <option value="{{$row->brand_name}}">{{$row->brand_name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Type Transaksi </b> <span class="text-danger">*</span></span>
                <select class="form-control js-select2" name="so_type" id="so_type" style="width:100%;" required>
                  <option value="">Pilih Transaksi Type </option>
                  @foreach(App\Entities\Penjualan\SalesOrder::TYPE_TRANSACTION as $row => $value)
                  <option value="{{$value}}">{{$value}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Indent </b> <span class="text-danger">*</span></span>
                <select class="form-control js-select2" name="so_indent" id="indent_so" style="width:100%;" required>
                  <option value="">Pilih status indent</option>
                  <option value="0">NO</option>
                  <option value="1">YES</option>
                </select>
              </div>
            </div>
          </div>

            <div class="row">
              <div class="col">
                <div class="form-group">
                  <label class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="approval_spv" id="approval_spv" value="1">
                  <span class="form-label"><b>Approval </b> <span class="text-danger">*</span></span>
                  </label>
                </div>
                <div class="form-group" id="kurs-group" style="display: none;">
                  <span class="form-label"><b>Kurs </b> <span class="text-danger">*</span></span>
                  <!-- <input class="form-control" type="text" name="kurs" id="kurs"> -->
                   <input class="form-control" type="text" name="kurs" id="kurs" pattern="^\d+$" title="Hanya angka tanpa titik/koma" placeholder="Masukkan angka bulat, contoh: 15500" required>
                </div>
                <div class="form-group" id="disc-percent-group" style="display: none;">
                  <span class="form-label"><b>Disc % </b> <span class="text-danger">*</span></span>
                  <input class="form-control" type="text" name="disc_percent" id="disc_percent">
                </div>
              </div>
              <div class="col">
                <div class="form-group">
                  <span class="form-label"><b>Note </b> <span class="text-danger">*</span></span>
                  <textarea class="form-control" name="note_so" id="editor" rows="4" col="10"></textarea>
                  <br>
                  <a class="btn btn-info" id="test" href="javascript:void(0);" title="">click</a>
                </div>
              </div>
            </div>
          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <a href="#" id="addSO" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Add</a>
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
            <div class="col-md-6">
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
            <div class="col-md-6">
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
            searching: false,
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

        $('#addSO').on('click', function (e) {
          e.preventDefault();                 // cegah anchor
          const form = document.getElementById('formAddSO');

          if (!form.checkValidity()) {        // validasi HTML5
            form.reportValidity();            // munculkan pesan bawaan
            return;                           // batalkan AJAX
          }

          var kurs = $('#kurs').val() || 1;

          if (kurs.includes('.') || kurs.includes(',')) {
              alert('Input "Kurs" tidak boleh mengandung titik atau koma. Masukkan angka bulat saja, contoh: 15500');
              $('#kurs').focus();
              return;
          }

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
            var kurs = $('#kurs').val() || 1;
            var approval_spv = $('#approval_spv').is(':checked') ? 1 : 0;
            var disc_percent = $('#disc_percent').val() || 0;

            var url = '{{ route('superuser.penjualan.sales_order.create',  [":step", ":member", ":brand", ":type", ":indent", ":approval", ":note", ":kurs", ":disc_percent"]) }}';
            url = url.replace(':member', customer); 
            url = url.replace(':brand', merek); 
            url = url.replace(':type', type_so);
            url = url.replace(':indent', indent_so);
            url = url.replace(':step', step_so);
            url = url.replace(':approval', approval_spv);
            url = url.replace(':kurs', kurs);
            url = url.replace(':note', encodeURIComponent(note));
            url = url.replace(':disc_percent', disc_percent);

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
          // Remove any data associated with the modal
          $(this).removeData();

          // Reset the form fields
          $(this).find('form')[0].reset();

          // Clear and reinitialize the Select2 elements
          $(this).find('.js-select2').val(null).trigger('change');
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
