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

    <a href="{{ route('superuser.master.vendor.detail.create', [$vendor->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Add</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Transaction Date</th>
          <th class="text-center">Transaction Name</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Unit</th>
          <th class="text-center">Grand Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($vendor->details as $detail)
        <tr>
          <td class="text-center">{{ date('d-m-Y', strtotime($detail->created_at)) }}</td>
          <td class="text-center">{{ $detail->transaction }}</td>
          <td class="text-center">{{ $detail->quantity }}</td>
          <td class="text-center">{{ $detail->satuan() }}</td>
          <td class="text-center">Rp. {{ number_format($detail->grand_total, 2) }}</td>
        </tr>
        @endforeach
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

