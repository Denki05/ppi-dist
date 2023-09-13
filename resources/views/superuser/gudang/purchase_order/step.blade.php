@extends('superuser.app')

@section('content')

@if ( $purchase_order->status() == 'DRAFT' )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Purchase Order (PO)</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Product</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Purchase Order (PO)</span>
    <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
    <span class="breadcrumb-item active">Edit Product</span>
  </nav>
@endif

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
<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New Purchase Order (PO)</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PO Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">ETD</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($purchase_order->etd)->format('d-m-Y')}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        @if ($purchase_order->status != $purchase_order::STATUS['DRAFT'])
        <a href="{{ route('superuser.gudang.purchase_order.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
        @endif
      </div>
      @if ($purchase_order->status == $purchase_order::STATUS['DRAFT'])
      <div class="col-md-6 text-right">
      <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.gudang.purchase_order.publish', $purchase_order->id) }}" class="btn bg-gd-leaf border-0 text-white" title="Publish">
          Publish <i class="fa fa-check ml-10"></i>
        </a>
      </div>
      @else
      <div class="col-md-6 text-right">
        @if($purchase_order->edit_marker == 0)
        <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        @endif
        @if($purchase_order->edit_marker == 1)
        <a href="{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save']) }}" class="btn bg-gd-corporate border-0 text-white" title="Save">
          Save <i class="fa fa-check ml-10"></i>
        </a>
        @endif
        <a href="{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save-acc']) }}" class="btn bg-gd-leaf border-0 text-white" title="Acc">
          ACC <i class="fa fa-check ml-10"></i>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">{{ ( $purchase_order->status() == 'DRAFT' ? 'Add' : 'Edit' ) }} Product </h3>
    
    <!-- <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button> -->
    
    <a href="{{ route('superuser.gudang.purchase_order.detail.create', [$purchase_order->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
    <!-- <button type="button" class="btn btn-outline-primary min-width-125 pull-right" data-toggle="modal" data-target=".bd-example-modal-lg">Add</button> -->
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Nama Varian</th>
          <th class="text-center">Kode</th>
          <th class="text-center">Qty (KG)</th>
          <th class="text-center">Packaging</th>
          <th class="text-center">Produksi</th>
          <th class="text-center">Repack</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase_order->purchase_order_detail as $row)
          <tr>
            <td class="text-center">{{ $row->id }}</td>
            <td class="text-center">{{ $row->product_pack->name }}</td>
            <td class="text-center">{{ $row->product_pack->code }}</td>
            <td class="text-center">{{ $row->qty }}</td>
            <td class="text-center">{{ $row->product_pack->kemasan()->pack_name }}</td>
            <td class="text-center">{{ $row->note_produksi ?? '-' }}</td>
            <td class="text-center">{{ $row->note_repack ?? '-' }}</td>
            <td class="text-center">
              <a href="{{ route('superuser.gudang.purchase_order.detail.edit', [$purchase_order->id, $row->id]) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                  <i class="fa fa-pencil"></i>
                </button>
              </a>
              <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.purchase_order.detail.destroy', [$purchase_order->id, $row->id]) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                    <i class="fa fa-times"></i>
                </button>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('public/utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()

    $('#datatable').DataTable();
  });
</script>
@endpush
