@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.index') }}">Vendor</a>
  <span class="breadcrumb-item active">{{ $vendor->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Vendor</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Email</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->email }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Owner Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->owner_name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Website</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->website }}</div>
      </div>
    </div>    
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Description</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->description }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->status() }}</div>
      </div>
    </div><div class="row">
      <label class="col-md-3 col-form-label text-right">Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $vendor->type() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.vendor.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($vendor->status != $vendor::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.vendor.destroy', $vendor->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.vendor.edit', $vendor->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

@if($vendor->type == $vendor::TYPE['Non Ekspedisi'])
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Transaction History</h3>

    <a href="#">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Add</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">SKU</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Unit Price (RMB)</th>
          <th class="text-center">Local Freight Cost (RMB)</th>
          <th class="text-center">Komisi (IDR)</th>
          <th class="text-center">Total Price (RMB)</th>
          <th class="text-center">Kurs (RMB)</th>
          <th class="text-center">Total Price (IDR)</th>
          <th class="text-center">Unit Price (IDR)</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
      </tfoot>
    </table>
  </div>
</div>
@endif

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready( function () {
    $('#datatable').DataTable();
  });
</script>
@endpush

