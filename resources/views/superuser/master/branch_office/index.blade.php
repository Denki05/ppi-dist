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

<div class="container-scroller">
  <div class="container-fluid page-body-wrapper full-page-wrapper">
    <div class="main-panel w-100  documentation">
      <div class="content-wrapper">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-header">
                  <button type="button" class="btn btn-primary btn-icon-text" onclick="location.href='{{ route('superuser.master.branch_office.create') }}'">
                    <i class="mdi mdi-plus btn-icon-prepend"></i>
                      Create
                  </button>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover" id="datatable">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Created at</th>
                          <th>Code</th>
                          <th>Name</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
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
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

{{-- @include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.branch_office.import_template'),
  'import_url' => route('superuser.master.branch_office.import'),
  'export_url' => route('superuser.master.branch_office.export')
]) --}}

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.master.branch_office.json') }}';

  $('#datatable').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'code'},
      {data: 'name'},
      {data: 'status'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
  });
});
</script>
@endpush
