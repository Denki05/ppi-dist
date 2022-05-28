<?php
  $sub_total = 0;
  $idr_sub_total = 0;
?>
@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.packing_order.index') }}">Packing Order</a>
  <span class="breadcrumb-item active">Detail Packing Order ({{$result->do_type_transaction()->scalar ?? ''}})</span>
</nav>
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
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Data Pesanan</h5>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Warehouse</label>
          <div class="col-md-8">
            <input type="text" class="form-control"  value="{{$result->warehouse->name ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Customer</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{$result->customer->name ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Address</label>
          <div class="col-md-8">
            <textarea class="form-control" readonly name="address" rows="1">{{$result->customer->address ?? ''}}</textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Delivery</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{$result->customer_other_address->label ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Delivery Address</label>
          <div class="col-md-8">
            <textarea class="form-control" readonly name="delivery_address" rows="1">{{$result->customer_other_address->address ?? ''}}</textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">IDR Rate</label>
          <div class="col-md-8">
            <input type="number" name="idr_rate" class="form-control" value="{{number_format($result->idr_rate,0,',','.')}}" readonly>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Transaction</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{$result->do_type_transaction()->scalar ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Ekspedisi</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{$result->ekspedisi->name ?? ''}}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Note</label>
          <div class="col-md-8">
            <textarea class="form-control summernote" name="note" readonly>{!!$result->note!!}</textarea>
          </div>
        </div>
        <?php
          $account_receivable = 0;
        ?>
        @foreach($result->customer->do as $index => $row)
          @if($row->invoicing)
            <?php
              $total_invoicing = $row->invoicing->grand_total_idr ?? 0;
              $payable = $row->invoicing->payable_detail->sum('total');
              $account_receivable += $total_invoicing - $payable;
            ?>
          @endif
        @endforeach
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Customer Account Receivable</label>
          <div class="col-md-8">
            <input type="text" class="form-control" value="{{number_format($account_receivable,0,',','.')}}" readonly>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Item</h5>
        <div class="table table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <th>Code</th>
              <th>Product</th>
              <th>Packaging</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Discount</th>
              <th>Sub Total</th>
              <th>Note</th>
            </thead>
            @if(count($result->do_detail) == 0)
              <tr><td colspan="8" align="center">Data tidak ditemukan</td></tr>
            @endif
            @foreach($result->do_detail as $index => $row)
              <?php
                $sub_total += floatval($row->total) ?? 0; 
                $idr_sub_total += ceil((($row->price * $result->idr_rate) * $row->qty) - ($row->total_disc * $result->idr_rate));
              ?>
              <tr>
                <td>{{$row->product->code ?? ''}}</td>
                <td>{{$row->product->name ?? ''}}</td>
                <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->price ?? ''}}</td>
                <td>{{$row->total_disc ?? ''}}</td>
                <td>{{$row->total ?? ''}}</td>
                <td>{{$row->note ?? ''}}</td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row mb-30">
      <div class="col-12">
        <h5>#Cost</h5>
        <div class="row">
          <div class="col-lg-3"></div>
          <div class="col-lg-9 float-right">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">IDR Total</label>
              <div class="col-md-8">
                <input type="text" name="idr_sub_total" class="form-control" readonly value="{{number_format($idr_sub_total,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount 1 (%)</label>
              <div class="col-md-8">
                <input type="text" name="discount_1" class="form-control count" value="{{$result->do_cost->discount_1 ?? 0}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount 2 (%)</label>
              <div class="col-md-8">
                <input type="text" name="discount_2" class="form-control count" value="{{$result->do_cost->discount_2 ?? 0}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount IDR</label>
              <div class="col-md-8">
                <input type="text" name="discount_idr" class="form-control count" value="{{number_format($result->do_cost->discount_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Total Discount (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="discount_total" class="form-control" readonly value="{{number_format($result->do_cost->total_discount_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">PPN</label>
              <div class="col-md-8">
                <input type="text" name="ppn" class="form-control" readonly value="{{number_format($result->do_cost->ppn ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Voucher (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="voucher_idr" class="form-control count" value="{{number_format($result->do_cost->voucher_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Cashback (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="cashback_idr" class="form-control count" value="{{number_format($result->do_cost->cashback_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Purchase Total (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="purchase_total_idr" class="form-control" readonly value="{{number_format($result->do_cost->purchase_total_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Delivery Cost (IDR)</label>
              <div class="col-md-4">
                <input type="text" name="delivery_cost_note" class="form-control count" value="{{$result->do_cost->delivery_cost_note ?? ''}}" readonly>
              </div>
              <div class="col-md-4">
                <input type="text" name="delivery_cost_idr" class="form-control count" value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Other Cost (IDR)</label>
              <div class="col-md-4">
                <input type="text" name="other_cost_note" class="form-control count" value="{{$result->do_cost->other_cost_note ?? ''}}" readonly>
              </div>
              <div class="col-md-4">
                <input type="text" name="other_cost_idr" class="form-control count" value="{{number_format($result->do_cost->other_cost_idr ?? 0,0,',','.')}}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Grand Total (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="grand_total_idr" class="form-control" readonly value="{{number_format($result->do_cost->grand_total_idr ?? 0,0,',','.')}}">
              </div>
            </div>

          </div>
        </div>
        <a href="{{route('superuser.penjualan.packing_order.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
  $(function(){
    summernote = $('.summernote').length;
    if(summernote > 0){
      $('.summernote').summernote({
          toolbar: [
             ['style', ['style']],
               ['font', ['bold', 'italic', 'underline', 'clear']],
               ['fontname', ['fontname']],
               ['color', ['color']],
               ['para', ['ul', 'ol', 'paragraph']],
          ],
      });
      $('.summernote').summernote('disable');
    }
  })
</script>
@endpush