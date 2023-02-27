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
                    <input type="text" name="discount_1" class="form-control count" value="{{$result->do_cost->discount_1 ?? 0}}">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Voucher</label>
                  <div class="col-md-6">
                    <input type="text" name="voucher_idr" class="form-control count formatRupiah" value="{{number_format($result->do_cost->voucher_idr ?? 0,0,',','.')}}">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Subtotal</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any" readonly>
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
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Ongkir</label>
                  <div class="col-md-6">
                    <input type="text" name="delivery_cost_idr" class="form-control count formatRupiah" {{ $result->status == 1 ? '' : 'readonly' }} value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}">
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
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
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
                <button type="button" class="btn btn-danger "><i class="fas fa-calculator pr-2" aria-hidden="true"></i>Calculate</button>
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
            <table id="datatable" class="table table-striped">
              <thead>
                <tr>
                  <th class="text-center" width="2%">#</th>
                  <th class="text-center" width="10%">Code</th>
                  <th class="text-center" width="15%">Product</th>
                  <th class="text-center" width="2%">Acuan(USD)</th>
                  <th class="text-center" width="5%">Quantity</th>
                  <th class="text-center" width="5%">In Stock</th>
                  <th class="text-center" width="10%">Packaging</th>
                  <th class="text-center" width="13%">Harga</th>
                  <th class="text-center" width="5%">Disc</th>
                  <th class="text-center" width="13%">jumlah</th>
                </tr>
              </thead>
              <tbody>
                @foreach($result->so_detail as $index => $detail)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" name="sku[]" data-placeholder="Select SKU" style="width:100%" required><option value="{{ $detail->product_id }}">{{ $detail->product->code }}</option></select></td>
                    <td><span class="name">{{ $detail->product->name }} - <strong>{{ $detail->product->category->name }}</strong></span></td>
                    <td><input type="number" class="form-control price" id="price" style="text-align: center;" name="price" value="{{ $detail->product->selling_price }}" readonly></td>
                    <td><input type="number" class="form-control qty" id="qty" style="text-align: center;" name="qty" value="{{ $detail->qty }}" readonly></td>
                    <td><input type="number" class="form-control in_stock" id="in_stock" style="text-align: center;" name="in_stock"  required></td>
                    <td><span class="packaging">{{ $detail->packaging_txt()->scalar }}</span></td>
                    <td><input type="number" class="form-control harga" id="harga" style="text-align: right;" name="harga" readonly value="{{ $detail->harga }}"></td>
                    <td><input type="number" class="form-control disc-cash" name="disc-cash" required></td>
                    <td><input type="number" class="form-control netto" style="text-align: right;" name="netto" readonly value="{{ $detail->total }}"></td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                  <tr class="row-footer-subtotal">
                    <td colspan="9" class="text-right"><span><b>Subtotal</b></span></td>
                    <td class="text-right">
                      <input class="form-control sub_total_label" style="text-align: right;" id="sub_total_label" readonly>
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

    function formatRupiah(angka, prefix){
      angka = angka.toString();
      var number_string = angka.replace(/[^,\d]/g, '').toString(),
      split       = number_string.split(','),
      sisa        = split[0].length % 3,
      rupiah        = split[0].substr(0, sisa),
      ribuan        = split[0].substr(sisa).match(/\d{3}/gi);
    
      // tambahkan titik jika yang di input sudah menjadi angka ribuan
      if(ribuan){
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }
    
      rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
      return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    };

    $(document).on('keyup','.formatRupiah',function(){
      let val = $(this).val();
      $(this).val(formatRupiah(val));
    })

    $('.js-select2').select2();

    $(document.body).on('change',".base_disc",function (e) {
      const baseDisc = $(".base_disc option:selected").val();
      $('input[name="disc-cash"]').val(baseDisc);
    });

    $('#datatable tbody').on( 'keyup', 'input[name="in_stock"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price"]').val();
      var disc = $(this).parents('tr').find('input[name="disc-cash"]').val();
      let totalBeforeDisc = 0;
      let totalAfterDisc = 0;
      totalBeforeDisc = $(this).val() * price;

      if($('#idr_rate').val() > 1){
        totalBeforeDisc = parseFloat($('#idr_rate').val()) * parseFloat(totalBeforeDisc);
        $(this).parents('tr').find('input[name="harga"]').val(totalBeforeDisc);
        $(this).parents('tr').find('input[name="harga"]').change();

        totalAfterDisc = parseFloat(totalBeforeDisc) - (parseFloat($('#idr_rate').val()) * disc);
        $(this).parents('tr').find('input[name="netto"]').val(totalAfterDisc);
        $(this).parents('tr').find('input[name="netto"]').change();
      }
      else{
        $(this).parents('tr').find('input[name="harga"]').val(totalBeforeDisc);
        $(this).parents('tr').find('input[name="harga"]').change();

        $(this).parents('tr').find('input[name="netto"]').val(totalAfterDisc);
        $(this).parents('tr').find('input[name="netto"]').change();
      }
    });

    // SUM subtotal in product list
    $('#datatable tbody').on( 'change', 'input[name="netto"]', function (e) {
      var subtotal = 0;
      $('input[name="netto"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#sub_total_label').val(subtotal);
    });

    // change disc cash
    
  })
</script>
@endpush