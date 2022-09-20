@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Product Type</span>
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
  <div class="block-content">
    <a href="{{ route('superuser.master.product_type.create') }}">
      <button type="button" class="btn btn-outline-primary" title="Create Type"><i class="mdi mdi-file-plus"></i></button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage"><i class="mdi mdi-cloud-upload"></i></button>
    {{--<button type="button" class="btn btn-outline-danger ml-10" onclick="deleteMultiple()">Delete Checked</button>--}}
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-hover">
      <thead>
        <tr>
          <th></th>
          <th><input type="checkbox" onclick="$('input.check-entity').prop('checked', this.checked);" /></th>
          <th>Created at</th>
          <th>Code</th>
          <th>Name</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.product_type.import_template'),
  'import_url' => route('superuser.master.product_type.import'),
  'export_url' => route('superuser.master.product_type.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.master.product_type.json') }}';

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
      {data: 'check', orderable: false, searcable: false},
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

  function deleteMultiple() {
    Swal.fire({
      title: 'Are you sure?',
      type: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      backdrop: false,
    }).then(result => {
      if (result.value) {
        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          allowEnterKey: false,
          backdrop: false,
          onOpen: () => {
            Swal.showLoading()
          }
        })
        const ids = [];
        for (let i = 0; i < $('.check-entity:checked').length; i++) {
          ids.push($('.check-entity:checked')[i].value);
        }
        //ajaxcsrfscript();
        $.ajax({
          url : '{{route('superuser.master.product_type.delete_multiple')}}',
          method : "POST",
          data : {ids:ids},
          dataType : "JSON",
        }).then( response => {
          Swal.fire({
            title: 'Deleted!',
            text: 'Your data has been deleted.',
            type: 'success',
            backdrop: false,
          }).then(() => {
            redirect(response.redirect_to);
          })
        })
        .catch(error => {
          Swal.fire('Error!','Cek Koneksi Internet','error')
        });
      }
    });
  }
</script>
@endpush
