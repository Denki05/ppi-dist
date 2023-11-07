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
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->note }}</div>
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
        <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.publish', $purchase_order->id) }}')">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Publish <i class="fa fa-check ml-10"></i>
          </button>
        </a>
      </div>
      @else
      <div class="col-md-6 text-right">
        <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save']) }}')">
          <button type="button" class="btn bg-gd-corporate border-0 text-white">
            Save <i class="fa fa-check ml-10"></i>
          </button>
        </a>
        @role('Developer|SuperAdmin', 'superuser')
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.unpublish', $purchase_order->id) }}')">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              Unpublish   <i class="fa fa-times mr-10"></i>
            </button>
          </a>
          <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save-acc']) }}')">
            <button type="button" class="btn bg-gd-leaf border-0 text-white">
              ACC <i class="fa fa-check ml-10"></i>
            </button>
          </a>
        @endrole
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">{{ ( $purchase_order->status() == 'DRAFT' ? 'Add' : 'Edit' ) }} Product </h3>

    @if($purchase_order->status == $purchase_order::STATUS['DRAFT'])
    
    <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button>
    
    <a href="{{ route('superuser.gudang.purchase_order.detail.create', [$purchase_order->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
    @endif
   
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Kode</th>
          <th class="text-center">Nama Varian</th>
          <th class="text-center">Qty (KG)</th>
          <th class="text-center">Packaging</th>
          <th class="text-center">Notes</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase_order->purchase_order_detail as $row)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->product_pack->code }}</td>
            <td class="text-center">{{ $row->product_pack->name }}</td>
            <td class="text-center">{{ $row->quantity }}</td>
            <td class="text-center">{{ $row->product_pack->kemasan()->pack_name }}</td>
            <td class="text-center">{{ $row->note_produksi ?? '-' }}</td>
            <td class="text-center">{{ $row->note_repack ?? '-' }}</td>
            <td class="text-center">
              @if($purchase_order->status == $purchase_order::STATUS['DRAFT'])
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
              @endif
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

@section('modal')
  @include('superuser.component.modal-manage-purchase-order-detail', [
    'import_template_url' => route('superuser.gudang.purchase_order.import_template'),
    'import_url' => route('superuser.gudang.purchase_order.import', $purchase_order->id),
    // 'export_url' => route('superuser.gudang.purchase_order.export')
  ])
@endsection

@push('scripts')
<script src="{{ asset('public/utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()

    $('#datatable').DataTable();
  });
</script>
@endpush
