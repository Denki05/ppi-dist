@extends('superuser.app')

@section('content')

<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Receiving</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PBM Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->pbm_date ? date('d/m/Y', strtotime($receiving->pbm_date)) : '' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">No Countainer</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->no_container }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->description }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->status() }}</div>
      </div>
    </div>

    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.gudang.receiving.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Detail ({{ $receiving->details->count() }})</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
        <th class="text-center">#</th>
          <th class="text-center">PO Number</th>
          <th class="text-center">Product</th>
          <th class="text-center">Kemasan</th>
          
          <th class="text-center">PO Quantity</th>
          <th class="text-center">RI Quantity</th>
          <th class="text-center">Colly Quantity</th>
          <th class="text-center">NO SJ</th>
          <th class="text-center">Note</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($receiving->details as $detail)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $detail->purchase_order->code }}</td>
          <td class="text-center">{{ $detail->product_pack->code }} - <b>{{ $detail->product_pack->name }}</b></td>
          <td class="text-center">{{ $detail->product_pack->kemasan()->pack_name }}</b></td>
          <td class="text-center">{{ $receiving->price_format($detail->quantity) }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->total_quantity_ri) }}{{ $detail->total_reject_ri($detail->id) ? ' [RE '.$receiving->price_format($detail->total_reject_ri($detail->id)).']' : '' }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->total_quantity_colly) }}{{ $detail->total_reject_colly($detail->id) ? ' [RE '.$receiving->price_format($detail->total_reject_colly($detail->id)).']' : '' }}</td>
          <td class="text-center">{{ $detail->sj_po }}</td>
          <td class="text-center">{{ $detail->description }}</td>
          <td class="text-center">
            <a href="{{ route('superuser.gudang.receiving.detail.show', [$receiving->id, $detail->id]) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View Detail">
                <i class="fa fa-eye"></i>
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
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({
      "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    })

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
  })
</script>
@endpush