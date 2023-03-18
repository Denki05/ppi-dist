@extends('superuser.app')

@section('content')

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show SO {{ $step_txt }}</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <div class="col-6">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Code</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{ $result->code }}</div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="type_transaction">Type Transaction</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{$result->so_type_transaction()->scalar ?? ''}}</div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{$result->origin_warehouse->name ?? ''}}</div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{$result->ekspedisi->name ?? '-'}}</div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{$result->member->name ?? ''}}</div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="address">Address</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{$result->member->address ?? ''}}</div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{$result->member->phone ?? ''}}</div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="phone">IDR Rate</label>
          <div class="col-md-7">
            <div class="form-control-plaintext">{{number_format($result->idr_rate ?? 0,0,',','.')}}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      <div class="col-md-6 text-right">
        @foreach ($result->proforma as $index => $row)
        <a class="btn btn-primary btn-print" href="{{ route('superuser.finance.proforma.print_proforma', [$row->id]) }}" role="button"><i class="fa fa-print" aria-hidden="true"></i> Print Proforma</a>
        <a class="btn btn-danger btn-cancel" data-id="{{$row->id}}" href="#" role="button"><i class="fa fa-ban" aria-hidden="true"></i> Cancel</a>
        <!-- <a href="#">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a> -->
        @endforeach
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
</div>

<form method="post" action="{{route('superuser.finance.proforma.cancel')}}" id="frmCancel">
  @csrf
  <input type="hidden" name="id">
</form>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({})
  });

  $(document).on('click','.btn-cancel',function(){
      if(confirm("Apakah anda yakin ingin 'Cancel/Revisi' Proforma ini!")){
        let id = $(this).data('id');
      $('#frmCancel').find('input[name="id"]').val(id);
      $('#frmCancel').submit();
    }
  })
</script>
@endpush