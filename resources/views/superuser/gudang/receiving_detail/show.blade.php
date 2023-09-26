@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
  <span class="breadcrumb-item">{{ $receiving_detail->id }}</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Information</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Product Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving_detail->product_pack->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Product Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving_detail->product_pack->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->warehouse->name }}</div>
      </div>
    </div>
    
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        @if ($receiving->status == $receiving::STATUS['ACTIVE'])
        <a href="{{ route('superuser.gudang.receiving.step', $receiving->id) }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
        @else
        <a href="{{ route('superuser.gudang.receiving.show', $receiving->id) }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Barcode ({{ $receiving_detail->collys->count() }})</h3>
    @if ($receiving->status == $receiving::STATUS['ACTIVE'])
      <a href="{{ route('superuser.gudang.receiving.detail.colly.create', [$receiving->id, $receiving_detail->id]) }}">
        <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Add Barcode</button>
      </a>
    @endif
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Code Colly</th>
          <th class="text-center">Colly</th>
          <th class="text-center">Qty</th>
          @if ($receiving->status == $receiving::STATUS['ACTIVE'])
          <th class="text-center">Action</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach($receiving_detail->collys as $detail)
        <tr {{ $detail->is_reject ? 'style=background-color:#f7a6a4;' : '' }}>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $detail->code }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->quantity_colly) }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->quantity_ri) }}</td>
          @if ($receiving->status == $receiving::STATUS['ACTIVE'])
          <td class="text-center">
            <a href="{{ route('superuser.gudang.receiving.detail.colly.edit', [$receiving->id, $receiving_detail->id, $detail->id]) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                <i class="fa fa-pencil"></i>
              </button>
            </a>
            <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.receiving.detail.colly.destroy', [$receiving->id, $receiving_detail->id, $detail->id]) }}')">
              <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-times"></i>
              </button>
            </a>
          </td>
          @endif
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