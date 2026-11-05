@extends('superuser.app')

@section('content')

<nav class="breadcrumb bg-white push">
  <a href="{{route('superuser.master.customer.create')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

  <button type="button" class="btn btn-outline-secondary ml-10" data-toggle="modal" data-target="#exampleModal">
    Import Employee
  </button>
</nav>

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

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        @foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
    <div class="col pr-0">
      <div class="alert alert-danger alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Failed Import</h3>
        @foreach (session()->get('collect_error') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="customers">Search Type :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="type_search" name="type_search" data-placeholder="Select Search Type">
                  <option value="all">All</option>
                  <option value="0">Store</option>
                  <option value="1">Member</option>
                </select>
              </div>
              <label class="col-md-2 col-form-label text-left" for="search_name">Name :</label>
              <div class="col-md-4">
                <!-- <input type="text" class="form-control" id="search_name" name="search_name"> -->
                <select class="form-control js-select2" name="search_name" id="search_name">
                  <option value="all">All</option>
                  @foreach($other_address AS $row)
                  <option value="{{ $row->name }}">{{ $row->name }} {{ $row->text_kota }}</option>
                  @endforeach
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
                <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<div class="block">
  <div class="block-content block-content-full">
    <div class="form-group row">
    <table class="datatable table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Store</th>
            <th>Category</th>
            <th>Region</th>
            <th>Tempo</th>
            <th>Action</th>
            <th>Action 2</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Import Employee</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        @if(isset($import_custom_message))
        <div class="alert alert-info mb-20">
          <b>Note :</b><br>
          {!! $import_custom_message !!}
        </div>
        @endif

        <div class="mb-25">
          <span class="badge badge-primary mr-5">1</span>
          <b>Download template</b>
          <p class="text-muted mb-10">
            Semua member aktif sudah terisi otomatis: <b>id, kategori, zona, customer, kota, provinsi</b>.
            Jangan ubah atau hapus baris header dan kolom tersebut.
          </p>
          <a href="{{ route('superuser.master.customer_other_address.import_template2') }}" class="btn btn-sm btn-noborder btn-info">
            <i class="fa fa-download mr-5"></i> Download Template
          </a>
        </div>

        <div class="mb-25">
          <span class="badge badge-primary mr-5">2</span>
          <b>Isi kolom yang ingin diubah</b>
          <p class="text-muted mb-5">
            Hanya isi kolom <b>sm/am, asm, pic, officer</b> pada baris yang ingin diperbarui.
            Kolom yang dibiarkan kosong tidak akan mengubah data lama.
          </p>
          <p class="text-muted mb-0">
            <b>is_default_member</b>: 1 = YES, 0 = NO. Isi 1 bila sm/am, asm, dan pic juga harus ikut
            diperbarui di master customer. Biarkan kosong jika tidak ingin mengubahnya.
          </p>
        </div>

        <div>
          <span class="badge badge-primary mr-5">3</span>
          <b>Upload &amp; import</b>
          <p class="text-muted mb-10">Simpan file dalam format Excel (.xlsx), lalu unggah di sini.</p>
          <form action="{{ route('superuser.master.customer_other_address.import2') }}" method="POST" enctype="multipart/form-data" id="form-import-employee">
            @csrf

            <div class="custom-file mb-10">
              <input type="file" class="custom-file-input" id="import_employee_file" name="import_file" accept=".xls,.xlsx" data-toggle="custom-file-input" required>
              <label class="custom-file-label" for="import_employee_file">Choose file</label>
            </div>

            <button type="submit" id="btn-import-employee" class="btn w-100 btn-alt-success" disabled>
              <i class="fa fa-upload mr-5"></i> Import
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.customer_other_address.import_template'),
  'import_url' => route('superuser.master.customer_other_address.import'),
])

@endsection

@push('scripts')
<script type="text/javascript">
  function format(d) {
      return d['detail'];
  }

  $(document).ready(function () {
    $('.js-select2').select2({});

    // Import Employee: aktifkan tombol import setelah file dipilih.
    $('#import_employee_file').on('change', function () {
      var file = this.files && this.files[0];
      $('#btn-import-employee').prop('disabled', !file);
      $(this).next('.custom-file-label').text(file ? file.name : 'Choose file');
    });

    $('#form-import-employee').on('submit', function () {
      $('#btn-import-employee')
        .prop('disabled', true)
        .html('<i class="fa fa-spinner fa-spin mr-5"></i> Mengimpor...');
    });

    let datatableUrl = '{{ route('superuser.master.customer.json') }}';
    let firstDatatableUrl = datatableUrl + '?type_search=all&search_name=all';
    var datatable = $('.datatable').DataTable({
      language: {
          processing: "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                           </span>",
      },
      processing: true,
      serverSide: false,
      ajax: {
          "url": firstDatatableUrl,
          "dataType": "json",
          "type": "GET",
          "data": {
            _token: "{{ csrf_token() }}"
          }
      },
      columns: [
          {
            "class": "details-control",
            "orderable": false,
            "data": null,
            "defaultContent": '<button class="btn btn-secondary btn-sm"><i class="fas fa-plus"></i></button>',
            searchable: false
          },
          {
            data: 'store_name_city'
          },          
          {
            data: 'category_name',
            name: 'master_customer_categories.name'
          },
          {
            data: 'store_provinsi',
            name: 'master_customers.text_provinsi'
          },
          {
            data: 'store_tempo',
            name: 'master_customers.has_tempo'
          },
          {
            data: 'action',
            searchable: false
          },
          {
            data: 'action2',
            searchable: false
          },
          {
            data: 'detail',
            "visible": false,
            searchable: false
          },
        ],
        order: [
          [1, 'asc']
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100]
        ],
        dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });

    $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var type_search = $('#type_search').val();
        var name_search = $('#search_name').val();
        let newDatatableUrl = datatableUrl + '?type_search=' + type_search + '&search_name=' + name_search;
        datatable.ajax.url(newDatatableUrl).load();
    });

    $('.datatable tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = datatable.row(tr);
        if (row.child.isShown()) {
          // This row is already open - close it
          row.child.hide();
          tr.removeClass('shown');
        } else {
          // Open this row
          row.child(format(row.data())).show();
          tr.addClass('shown');
        }
    });
    
  });
</script>
@endpush