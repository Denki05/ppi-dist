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
<nav class="breadcrumb bg-white push">
  <a href="{{ route('superuser.master.product.create') }}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Add Product</a>
  <a href="{{ route('superuser.master.product.cetak') }}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Print Product</a>
  <button type="button" class="btn btn-outline-danger ml-10" onclick="deleteMultiple()">Delete Checked</button>
</nav>
<div class="block">
  {{--<div class="block-content">
    <a href="{{ route('superuser.master.product.create') }}">
      <button type="button" class="btn btn-outline-primary" title="Create Product"><i class="mdi mdi-file-plus"></i></button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

    <button type="button" class="btn btn-outline-danger ml-10" onclick="deleteMultiple()">Delete Checked</button>
    <a class="ml-10" href="{{ route('superuser.master.product.cetak') }}">
      <button type="button" class="btn btn-outline-secondary" title="Print Product"><i class="mdi mdi-printer"></i></button>
    </a>
  </div>--}}
  <div class="block-content block-content-full">
  <table class="table table-striped" id="product_list">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Code</th>
                  <th>Brand</th>
                  <th>Category</th>
                  <th>Name</th>
                  <th>Kemasan</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($product_list as $key => $row)
                    <tr>
                      <td>
                        {{ $loop->iteration }}
                      </td>
                      <td>
                        {{$row->code}}
                      </td>
                      <td>
                        {{$row->brand_name}}
                      </td>
					            <td>
                        {{$row->category->name}}
                      </td>
                      <td>{{ $row->name }}</td>
                      <td>{{ $row->category->packaging->pack_name }}</td>
                      <td>{{ $row->status() }}</td>
                      <td>
                        <a href="{{ route('superuser.master.product.show', $row->id) }}" class="btn btn-primary" role="button"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('superuser.master.product.edit', $row->id) }}" class="btn btn-warning" role="button"><i class="fa fa-edit"></i></a>
                        <a href="{{ route('superuser.master.product.destroy', $row->id) }}" class="btn btn-danger" role="button"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                @endforeach
              </tbody>
            </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.product.import_template'),
  'import_url' => route('superuser.master.product.import'),
  'export_url' => route('superuser.master.product.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('#product_list').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     false,
        "searching" : true,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
  });
});
</script>
@endpush
