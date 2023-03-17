@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">SO {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show SO {{ $step_txt }}</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $result->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{$result->origin_warehouse->name ?? ''}}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="sales_senior">Sales Senior</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{$result->sales_senior->name ?? ''}}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="sales">Sales</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{$result->sales->name ?? ''}}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $result->member->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="address">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $result->member->address }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{$result->ekspedisi->name ?? ''}}</div>
      </div>
    </div>
    <hr class="my-20">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="type_transaction">Type Transaction</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{$result->so_type_transaction()->scalar ?? ''}}</div>
      </div>
    </div>
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}">
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
    <h3 class="block-title">Product</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">Code</th>
          <th class="text-center">Product</th>
          <th class="text-center">Brand | Category</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Packaging</th>
        </tr>
      </thead>
      <tbody>

              @if(count($result->so_detail) > 0)
                @foreach($result->so_detail as $index => $row)
                  <tr>
                    <td>{{$row->product->code ?? ''}}</td>
                    <td>{{$row->product->name ?? ''}}</td>
                    <td>{{$row->product->category->brand_name ?? ''}} | {{ $row->product->category->name }}</td>
                    <td>
                      @if($row->status <> 4)
                        {{$row->qty ?? '0'}}
                      @else
                        {{$row->qty_worked ?? '0'}}
                      @endif
                    </td>
                    <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                  </tr>
                @endforeach
              @else
              <tr>
                <td colspan="5" class="text-center">Data tidak ditemukan</td>
              </tr>
              @endif
      </tbody>
    </table>
  </div>
  <div class="block-header block-header-default">
    <div class="container">
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="subtotal">IDR Sub Total</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">-</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="tax">PPN</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">-</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="discount">IDR Discount</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">-</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="shipping_fee">Courier</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">-</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">-</div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({})
  });
</script>
@endpush