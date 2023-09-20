@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <a href="{{ route('superuser.master.product.create') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">
  <div class="block-content block-content-full">
  <table class="table table-striped" id="datatables">
      <thead>
        <tr>
          <th>#</th>
          <th>Code</th>
          <th>Brand</th>
          <th>Category</th>
          <th>Name</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($product as $row)
          <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$row->code}}</td>
            <td>{{$row->brand_name}}</td>
            <td>{{$row->category->name}}</td>
            <td>{{$row->name}}</td>
            <td>{{$row->status()}}</td>
            <td>
              @if($row->status == '1')
                <a href="{{ route('superuser.master.product.show', base64_encode($row->id))}}">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Show">
                    <i class="mdi mdi-eye"></i>
                  </button>
                </a>
                <a href="{{ route('superuser.master.product.edit', base64_encode($row->id)) }}">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Edit">
                    <i class="mdi mdi-pencil"></i>
                  </button>
                </a>
                <a href="javascript:deleteConfirmation('{{ route('superuser.master.product.destroy', $row->id) }}')">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Delete">
                    <i class="mdi mdi-delete"></i>
                  </button>
                </a>
              @elseif($row->status == '0')
                <a href="">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Show">
                    <i class="mdi mdi-eye"></i>
                  </button>
                </a>
              @endif
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
  let datatableUrl = '{{ route('superuser.master.product.json') }}';

  var table = $('#datatables').DataTable({});
});
</script>
@endpush
