@extends('superuser.app')

@section('content')

<nav class="breadcrumb bg-white push">
  <a href="{{route('superuser.master.customer.create')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

  <button type="button" class="btn btn-outline-secondary ml-10" data-toggle="modal" data-target="#exampleModal">
    Export/Import Employee
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

<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Export / Import Employee</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
              <span class="font-size-h5">Export</span>
              <p>Export this data to excel-like format</p>
              <a href="{{ route('superuser.master.customer_other_address.export') }}">
                <button type="button" class="btn btn-sm btn-noborder btn-info">
                  <i class="fa fa-file-excel-o mr-5"></i> Export
                </button>
              </a>
            </div>

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
              <a href="{{ route('superuser.master.customer_other_address.import_template2') }}">
                <button type="button" class="btn btn-sm btn-noborder btn-secondary">
                  <i class="fa fa-download mr-5"></i> Template
                </button>
              </a>
              <hr>
              <form action="{{ route('superuser.master.customer_other_address.import2') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="import_file" name="import_file" data-toggle="custom-file-input" required>
                  <label class="custom-file-label" for="import_file">Choose file</label>
                </div>
                
                <button type="submit" class="btn mt-10 w-100 btn-alt-success">Import</button>
              </form>
            </div>
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