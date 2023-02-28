<?php
  $sub_total = 0;
  $idr_sub_total = 0;
?>
@foreach($result->so_detail as $index => $row)
  @php $row->product->name @endphp
@endforeach

@extends('superuser.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-4">
      <div class="card mb-2 border-0">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="row">
                <label style="font-size: 10pt;" class="col-xs-4 col-sm-4 col-md-4 control-label" for="textinput">Code</label>
                <div class="col-xs-6 col-sm-6 col-md-6">
                  <p style="font-size: 9pt;">{{ $result->code }}</p>
                </div>
              </div>
              </div>
              <div class="col">
                <div class="row">
                  <label style="font-size: 10pt;" class="col-xs-6 col-sm-6 col-md-6 control-label" for="textinput">Tanggal</label>
                  <div class="col-xs-6 col-sm-6 col-md-6">
                    <p style="font-size: 9pt;">{{ date('d-m-Y',strtotime($result->created_at)) }}</p>
                  </div>
                </div>
              </div>
          </div>
        </div>
      </div>

      <div class="card mb-2 border-0">
        <div class="card-body" >
          <div class="row">
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Customer</label>
                <p style="font-size: 9pt;">{{ $result->member->name }}</p>
              </div>
            </div>
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Address</label>
                <p style="font-size: 9pt;">{{ $result->member->address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-2 border-0">
        <div class="card-body" style="padding: 2px 5px 2px;">
          <div class="row">
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Plafon Piutang</label>
                <p style="font-size: 9pt;">{{ $result->customer->plafon_piutang }}</p>
              </div>
            </div>
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Saldo</label>
                <p style="font-size: 9pt;">{{ $result->customer->saldo }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-md-8">
      <div class="row">
        <div class="card mb-2 border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Gudang<span class="text-danger">*</span></label>
                      <div class="col-8">
                        <select class="form-control js-select2" style="font-size: 9pt;" name="origin_warehouse_id">
                          <option value="">Pilih Gudang</option>
                          @foreach($warehouse as $index => $row)
                          <option style="font-size: 10pt;" value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                          @endforeach
                        </select>
                      </div>
                  </div>
                  @endif
              </div>
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right" style="font-size: 10pt;">Kurs<span class="text-danger">*</span></label>
                    <div class="col-5">
                    @if ( $result->idr_rate > 1 )
                      <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $result->idr_rate }}" readonly>
                    @else
                      <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="1">
                    @endif
                    <!-- <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $result->idr_rate }}"> -->
                    </div>
                  </div>
                  @endif
              </div>
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Disc Cash</label>
                      <div class="col-5">
                        <select class="form-control js-select2 base_disc" id="base_id">
                            <option value="0">0</option>
                            <option value="2">$2</option>
                            <option value="4">$4</option>
                        </select>
                      </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="card mb-2 border-0">
          <div class="card-body">
            <div class="row">
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Disc %</label>
                  <div class="col-md-3">
                    <input type="text" name="disc_amount2_percent" id="disc_amount2_percent" class="form-control text-center disc_amount2_percent" value="{{$result->do_cost->discount_1 ?? 0}}">
                  </div>
                  <div class="col-md-5">
                    <input type="text" name="disc_amount2_idr" id="disc_amount2_idr" class="form-control disc_amount2_idr text-center" readonly>
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Voucher</label>
                  <div class="col-md-6">
                    <input type="text" name="voucher_idr" id="voucher_idr" class="form-control count voucher_idr" value="{{number_format($result->do_cost->voucher_idr ?? 0,0,',','.')}}">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Subtotal</label>
                  <div class="col-md-6">
                    <input type="number" id="subtotal_2" name="subtotal_2" class="form-control text-center subtotal_2" step="any" readonly>
                  </div>
                </div>
                @endif
              </div>
            </div>

            <div class="row">
              <div class="col">
                @if($step == 2)
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Disc Kemasan</label>
                    <div class="col-md-3">
                      <input type="text" name="disc_kemasan_percent" id="disc_kemasan_percent" class="form-control disc_kemasan_percent text-center" value="{{$result->do_cost->discount_1 ?? 0}}">
                    </div>
                    <div class="col-md-5">
                      <input type="text" name="disc_kemasan_idr" id="disc_kemasan_idr" class="form-control disc_kemasan_idr text-center" readonly>
                    </div>
                  </div>
                  @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Ongkir</label>
                  <div class="col-md-6">
                    <input type="text" name="delivery_cost_idr" class="form-control count value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Grand Total</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any" readonly>
                  </div>
                </div>
                @endif
              </div>
            </div>

            <div class="row">
              <div class="col-4">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Disc IDR</label>
                  <div class="col-md-6">
                    <input type="number" name="disc_idr" id="disc_idr" class="form-control text-center disc_idr" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col-4">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Resi Ongkir</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col-4">
                <button type="button" class="btn btn-danger"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>Calculate</button>
                <button type="button" class="btn btn-info"><i class="fas fa-save pr-2" aria-hidden="true"></i>Save</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <br>

  <!-- Detail Pesanan -->
  <div class="row">
    <form method="POST" action="">  
      <div class="col-12">
        <div class="card mb-2 border-0">
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>In Stock</th>
                <th>Price</th>
                <th>Packaging</th>
                <th>Disc (USD)</th>
                <th>Total</th>
              </thead>
              <tbody>
                
                @foreach($result->so_detail as $index => $detail)
                <tr class="index{{$index}}" data-index="{{$index}}">
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $detail->product->code }} - <b>{{ $detail->product->name }}</td>
                  <td>{{$detail->qty}}</td>
                  
                  <td>
                    <input type="text" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" step="any">
                  </td>
                  
                  <td>
                    <input type="text" name="repeater[{{$index}}][price]" class="form-control" readonly value="{{$detail->product->selling_price ?? 0}}">
                  </td>
                  <td>
                    <input type="text" name="repeater[{{$index}}][packaging]" class="form-control" readonly value="{{$detail->packaging_txt()->scalar ?? ''}}">
                  </td>
                  <td>
                    <input type="text" name="repeater[{{$index}}][usd_disc]" class="form-control count count-disc" data-index="{{$index}}" step="any">
                  </td>
                  
                  <td>
                    <input type="text" name="repeater[{{$index}}][total]" class="form-control" readonly>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot>
                  <tr class="row-footer-subtotal">
                    <td colspan="7" class="text-right">
                      <b>Subtotal</b>
                      <br><span class="text-danger">*Subtotal After Disc(USD)</span>
                    </td>
                    <td class="text-right">
                      <input type="text" name="total" id="total" class="form-control" readonly>
                    </td>
                  </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    $('#tableDetailPesanan').DataTable({
        scrollY: '430px',
        scrollCollapse: true,
        paging: false,
        bFilter: false,
        "aoColumnDefs": [
             { "bSortable": false, "aTargets": [ 1, 4, 5, 6, 7, 8, 9, 10, 11 ] }
        ] 
    });

    $('.js-select2').select2();

    $(document).on('change', '.base_disc'  ,function (e) {
      let val = $(this).val();
      // alert(val);
      $('.count-disc').val(val);
    });

    

    // $(document).on('click','.select_per_item',function(){
    //   total();
    // })

    $(document).on('keyup','.count',function(){
      let index = $(this).attr('data-index');
      count_per_item(index);
      total();
    })

    // function isSelected(){
    //   $('tbody').find('input[type="checkbox"]').attr('checked','checked');
    //   $('tbody').find('input[type="checkbox"]').prop('checked', true);
    // }
    // function removeSelected(){
    //   $('tbody').find('input[type="checkbox"]').removeAttr('checked');
    //  $('tbody').find('input[type="checkbox"]').prop('checked', false);
    // }

    function count_per_item(indx){
      let index = indx;
      let price = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][price]"]').val()); 
      let do_qty = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][do_qty]"]').val()); 
      let val_usd_disc = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][usd_disc]"]').val());
      let val_percent_disc = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][percent_disc]"]').val());
      let kurs = $('#idr_rate').val();

      if(isNaN(val_usd_disc)){
        val_usd_disc = 0;
      }
      if(isNaN(val_percent_disc)){
        val_percent_disc = 0;
      }

      let total_disc = (val_usd_disc + ((price - val_usd_disc) * (val_percent_disc/100))) * do_qty;
      
      let sub_total  = parseFloat((do_qty * price) - total_disc) * kurs;

      if(isNaN(total_disc)){
        total_disc = 0;
      }

      if(isNaN(sub_total)){
        sub_total = 0;
      }

      $('tr.index'+index+'').find('input[name="repeater['+index+'][total_disc]"]').val(total_disc);
      $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val(sub_total);
      
      sub_total1();
    }

    function sub_total1(){
      let total = 0;
      $('tbody tr').each(function(index,e){
        let sub_total = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val());
        // alert(kurs);
        if(isNaN(sub_total)){
          sub_total = 0;
        }
        total += sub_total;
      }) ;

      $('input[name="total"]').val(total);
    }

    // input disc % (agen)
    $(document).on('input', "#disc_amount2_percent", function(e){
        if($(this).val() != ''){
            var subtotal = $('input[name="total"]').val();
            var amount = parseFloat(subtotal) * parseFloat($(this).val()) / 100;
            $('input[name="disc_amount2_idr"]').val(amount);
        }else{
            $('input[name="disc_amount2_idr').val(0);
        }

        sub_total2();
    });


    // input disc kemasan
    $(document).on('input', "#disc_kemasan_percent", function(e){
        if($(this).val() != ''){
            var subtotal = $('input[name="subtotal_2"]').val();
            var amount = parseFloat(subtotal) * parseFloat($(this).val()) / 100;
            $('input[name="disc_kemasan_idr"]').val(amount);
        }else{
            $('input[name="disc_kemasan_idr').val(0);
        }

        sub_total2();
    });

    $("#disc_idr").on('input', function() {
      sub_total2();
    });

    $("#voucher_idr").on('input', function() {
      sub_total2();
    });

    function sub_total2(){
      var subtotal = $('input[name="total"]').val();
      var disc_agen = $('input[name="disc_amount2_idr"]').val();
      var disc_kemasan = $('input[name="disc_kemasan_idr"]').val();
      var disc_idr = $('#disc_idr').val();
      var voucher_idr = $('#voucher_idr').val();
      let subtotal_2 = (((subtotal - disc_agen) - disc_kemasan) - disc_idr) - voucher_idr;

      $('input[name="subtotal_2"]').val(subtotal_2);
    }

    
  })
</script>
@endpush