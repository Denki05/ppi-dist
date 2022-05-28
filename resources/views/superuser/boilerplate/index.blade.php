@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Boilerplate</span>
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
<div class="block">
  {{-- <div class="block-header block-header-default">
    <h3 class="block-title">Header</h3>
  </div> --}}
  <div class="block-content">
    <a href="{{ route('superuser.boilerplate.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

    <div class="pull-right">
      <label class="css-control css-control-primary css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="default" checked>
        <span class="css-control-indicator"></span> Default
      </label>
      <label class="css-control css-control-danger css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="trash">
        <span class="css-control-indicator"></span> Trash
      </label>
      <label class="css-control css-control-warning css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="all">
        <span class="css-control-indicator"></span> All
      </label>
    </div>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th>#</th>
          <th>Text</th>
          <th>Textarea</th>
          <th>Select</th>
          <th>Select Multiple</th>
          <th>Image</th>
          <th>Created at</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
  {{-- <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm">
    Footer
  </div> --}}
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.boilerplate.import_template'),
  'import_url' => route('superuser.boilerplate.import'),
  'export_url' => route('superuser.boilerplate.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.boilerplate.json') }}';

  let showControl = $('input[type=radio][name=show-control]');
  showControl.change(function() {
    let newDatatableUrl = datatableUrl+'?show='+this.value;
    $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
  });

  $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {data: 'text'},
      {data: 'textarea'},
      {data: 'select'},
      {data: 'select_multiple'},
      {data: 'image'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [6, 'desc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
    drawCallback: function() {
      $('a.img-lightbox').magnificPopup({
        type: 'image',
        closeOnContentClick: true,
      });
    }
  });
});
</script>
@endpush
