@extends('superuser.app')

@section('content')

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order.tutup_so', ) }}" data-type="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="id" value="{{$result->id}}">
  <input type="hidden" name="step" value="{{$step}}">

  <div class="row">
    <div class="col-6">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">#Detail Nota</h3>
        </div>
        <div class="block-content">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="invoice_date">Tanggal Nota</label>
              <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y',strtotime($result->created_at)) }}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="invoice_code">Nomer Nota</label>
              <input type="text" class="form-control" id="invoice_code" value="{{ $result->code }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="type_transaction">Type Transaksi</label>
              <input type="text" name="type_transaction" class="form-control" value="{{$result->type_transaction}}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="note">Catatan</label>
              <!-- <textarea class="form-control" rows="1" readonly>{{ $result->note }}</textarea> -->
              <input type="text" class="form-control" value="{{ $result->note ?? '-' }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="type_transaction">Eksepdisi <span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="ekspedisi">
                <option value="">Pilih Ekspedisi</option>
                @foreach($ekspedisi as $index)
                <option value="{{ $index->id }}">{{ $index->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="note">Rekening <span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="rekening">
                <option value="">Pilih Rekening</option>
                <option value="0">4720 2369 88 - IRWAN LINAKSITA</option>
                <option value="1">7881 0374 95 - IDA ELISA</option>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-sm-2">Bayar ditempat</div>
            <div class="col-sm-10">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="shipping_cost_buyer" name="shipping_cost_buyer">
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="col-6">
      <div class="row">
        <div class="col">
          <div class="block">
            <div class="block-header block-header-default">
              <h3 class="block-title">#Customer Info</h3>
            </div>
            <div class="block-content">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="type_transaction">Customer</label>
                  <input type="text" name="customer_name" class="form-control" value="{{ $result->member->name }} {{$result->member->text_kota}}" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label for="note">Alamat Kirim</label>
                  <textarea class="form-control" rows="1" readonly>{{ $result->member->address }}</textarea>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="customer_city">Kota</label>
                  <input type="text" name="customer_city" class="form-control" value="{{$result->member->text_kota}}" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label for="customer_area">Provinsi</label>
                  <input type="text" name="customer_area" class="form-control" value="{{ $result->member->text_provinsi }} " readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="block">
            <div class="block-content">
              <div class="form-row">
                @if($step == 2)
                <div class="form-group col-md-4">
                  <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
                  <select class="form-control js-select2" style="font-size: 9pt;" name="origin_warehouse_id">
                    <option value="">Pilih Gudang</option>
                    @foreach($warehouse as $index => $row)
                    <option style="font-size: 10pt;" value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                    @endforeach
                  </select>
                </div>
                @endif
                @if($step == 2)
                <div class="form-group col-md-4">
                  <label for="customer_area">Kurs <span class="text-danger">*</span></label>
                  <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $result->idr_rate }}">
                </div>
                @endif
                @if($step == 2)
                <div class="form-group col-md-4">
                  <label for="customer_area">Disc Cash <span class="text-danger">*</span></label>
                  <select class="form-control js-select2 base_disc" id="base_id" onkeyup="countGetUsd()">
                    <option value="0">0</option>
                    <option value="2">$2</option>
                    <option value="4">$4</option>
                  </select>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">#Product Order</h3>
        </div>
        <div class="block-content">
          <table class="table table-hover table-fixed" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>In Stock</th>
                <th>Harga</th>
                <th>Free</th>
                <th>Kemasan</th>
                <th>Disc (USD)</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @if(count($result->so_detail) <= 0)
                <tr>
                  <td colspan="13" align="center">Data tidak ditemukan</td>
                </tr>
              @endif
              @if(count($result->so_detail) > 0)
                @foreach($result->so_detail as $index => $detail)
                  <input type="hidden" name="repeater[{{$index}}][product_packaging_id]" value="{{$detail->product_packaging_id}}">
                  <input type="hidden" name="repeater[{{$index}}][so_qty]" value="{{$detail->qty}}">
                  <input type="hidden" name="repeater[{{$index}}][so_item_id]" value="{{$detail->id}}">
                  <tr class="index{{$index}}" data-index="{{$index}}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $detail->product_pack->code }} - <b>{{ $detail->product_pack->name }}</td>
                    <td>{{$detail->qty}}</td>
                    <td>
                      <input type="number" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" value="{{$detail->qty}}" step="any" min="0" max="{{$detail->qty}}">
                    </td>
                    <td>
                      <input type="text" name="repeater[{{$index}}][price]" class="form-control price" value="@if($detail->free_product == 1) 0 @else {{$detail->product_pack->price}} @endif">
                    </td>
                    <td>
                      <input class="form-check-input free-count" type="checkbox" value="{{$detail->free_product}}" name="repeater[{{$index}}][free_product]" @if($detail->free_product == 1) checked=checked @endif disabled>
                    </td>
                    <td>
                      <input type="text" name="kemasan" class="form-control text-center" readonly value="{{$detail->product_pack->kemasan()->pack_name ?? ''}}">
                      <input type="hidden" name="repeater[{{$index}}][packaging]" class="form-control" readonly value="{{$detail->product_pack->kemasan()->id ?? ''}}">
                    </td>
                    <td>
                      <input type="text" name="repeater[{{$index}}][usd_disc]" class="form-control count count-disc" data-index="{{$index}}" step="any" onchange="countGetUsd()" placeholder="{{$detail->disc_usd}}" />
                    </td>
                    <td>
                      <input type="text" name="repeater[{{$index}}][total]" class="form-control" readonly>
                    </td>
                  </tr>
                @endforeach
              @endif
            </tbody>
            <tfoot>
              <!-- Subtotal -->
              <tr class="row-footer-subtotal">
                <td colspan="8" class="text-right">
                  <b>Subtotal</b>
                </td>
                <td class="text-right">
                  <input type="text" name="sub_total_item" id="sub_total_item" class="form-control" readonly step="any">
                </td>
              </tr>

              <!-- disc % -->
              <tr class="row-footer-disc_percent">
                <td colspan="8" class="text-right">
                  <b>Disc %</b>
                </td>
                <td class="text-right">
                  <div class="row">
                    <div class="col-3">
                      <input class="form-control disc_agen_percent text-right" type="text" name="disc_agen_percent" id="disc_agen_percent" step="any">
                    </div>
                    <div class="col">
                      <input class="form-control disc_amount2_idr" type="text" name="disc_amount2_idr" id="disc_amount2_idr" readonly step="any">
                    </div>
                  </div>
                </td>
              </tr>

              <!-- Disc kemasan -->
              <tr class="row-footer-disc_kemasan">
                <td colspan="8" class="text-right">
                  <b>Disc Kemasan</b>
                </td>
                <td class="text-right">
                  <div class="row">
                    <div class="col-3">
                      <input class="form-control disc_tambahan text-right" type="text" name="disc_tambahan" id="disc_tambahan" step="any">
                    </div>
                    <div class="col">
                      <input class="form-control disc_kemasan_idr" type="text" name="disc_kemasan_idr" id="disc_kemasan_idr" readonly step="any">
                    </div>
                  </div>
                </td>
              </tr>

              <!-- Disc IDR -->
              <tr class="row-footer-disc_idr">
                <td colspan="8" class="text-right">
                  <b>Disc IDR</b>
                </td>
                <td class="text-right">
                  <div class="row">
                    <div class="col">
                      <input type="text" name="disc_idr" id="disc_idr" class="form-control disc_idr " step="any">
                    </div>
                  </div>
                </td>
              </tr>

              <!-- Voucher -->
              <tr class="row-footer-voucher">
                <td colspan="8" class="text-right">
                  <b>Voucher</b>
                </td>
                <td class="text-right">
                  <div class="row">
                    <div class="col">
                      <input type="text" name="voucher_idr" id="voucher_idr" class="form-control count voucher_idr" step="any">
                    </div>
                  </div>
                </td>
              </tr>

              <!-- Ongkir -->
              <tr class="row-footer-ongkir">
                <td colspan="8" class="text-right">
                  <b>Ongkir</b>
                </td>
                <td class="text-right">
                  <div class="row">
                    <div class="col">
                      <input type="text" name="delivery_cost_idr" id="delivery_cost_idr" class="form-control delivery_cost_idr ">
                    </div>
                  </div>
                </td>
              </tr>

              <!-- grand total -->
              <tr class="row-footer-grand_total">
                <td colspan="7" >
                <button type="submit" id="mySubmit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                <button type="button" class="btn btn-danger button_cal" id="button_cal"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>Calculate</button>
                </td>
                <td class="text-right">
                  <b>Total Akhir</b>
                  <br><span class="text-danger">* Click the calculated button to print the grand total</span>
                </td>
                <td>
                  <input type="hidden" class="form-control-plaintext" name="subtotal_2">
                  <input type="text" class="form-control-plaintext" name="grand_total">
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

</form>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2();

    $('#datatables').DataTable({
      paging: false,
      searching: false,
      info: false,
      scrollY: '430px',
      scrollCollapse: true,
    });

    $('.base_disc').on('change', function () {
      countGetUsd();
    })

    function countGetUsd(){
        $('tbody tr').each(function(index,e){
          
          let baseDisc = $('.base_disc').val();
          let freeProduct = $('tr.index'+index+'').find('input[name="repeater['+index+'][free_product]"]').val();
          
          if(freeProduct == 1){
            $('tr.index'+index+'').find('input[name="repeater['+index+'][usd_disc]"]').val(0);
          }else{
            $('tr.index'+index+'').find('input[name="repeater['+index+'][usd_disc]"]').val(baseDisc);
          }
        }) ;
    }

    $(document).on('keyup','.count',function(){
      let index = $(this).attr('data-index');
      count_per_item(index);
    });

    function count_per_item(indx){
      let index = indx;
      let price = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][price]"]').val()); 
      let do_qty = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][do_qty]"]').val()); 
      let so_qty = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][so_qty]"]').val()); 
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
      
      sub_total_item();
    }

    function sub_total_item(){
      let total = 0;
      $('tbody tr').each(function(index,e){
        let sub_total = $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val();
        sub_total = parseFloat(sub_total);

        sub_total = (isNaN(sub_total)) ? 0 : sub_total;
        Math.ceil(total += sub_total);
          
      });

      $('input[name="sub_total_item"]').val(total);
    }

    // input disc % (agen)
    $(document).on('input', "#disc_agen_percent", function(e){
          if($(this).val() != ''){
              let sub_total_item = $('input[name="sub_total_item"]').val();

              sub_total_item = parseFloat(sub_total_item);
              let amount = parseFloat(sub_total_item) * parseFloat($(this).val()) / 100;
              $('input[name="disc_amount2_idr"]').val(amount);
          }else{
              $('input[name="disc_amount2_idr').val(0);
          }
          subtotal();
      });

      // input disc kemasan
      $(document).on('input', "#disc_tambahan", function(e){
          if($(this).val() != ''){
              let sub_total_item = $('input[name="sub_total_item"]').val();
              let disc_percent = $('input[name="disc_amount2_idr"]').val();

              // sub_total_item = parseFloat(sub_total_item.split('.').join(''));
              // disc_percent = parseFloat(disc_percent.split('.').join(''));

              let subAfterDiscPercent = Math.ceil(sub_total_item - disc_percent);

              var amount = parseFloat(subAfterDiscPercent) * parseFloat($(this).val()) / 100;
              $('#disc_kemasan_idr').val(amount);
          }else{
              $('#disc_kemasan_idr').val(0);
          }
          subtotal();
      });

      function subtotal(){
        let sub_total_item = $('input[name="sub_total_item"]').val();
        let disc_agen = $('input[name="disc_amount2_idr"]').val();
        let disc_kemasan = $('input[name="disc_kemasan_idr"]').val();

        sub_total_item = (isNaN(sub_total_item)) ? 0 : sub_total_item;
        disc_agen = (isNaN(disc_agen)) ? 0 : disc_agen;
        disc_kemasan = (isNaN(disc_kemasan)) ? 0 : disc_kemasan;
      
        let subtotal_before = Math.ceil((sub_total_item - disc_agen) - disc_kemasan);

        $('input[name="subtotal_2"]').val(subtotal_before);
      }

      // calculated button after input voucher - disc idr
      $(document).on('click', '#button_cal', function(e) {
        e.preventDefault();

        let subtotal = $('input[name="subtotal_2"]').val();
        let disc_idr = $('input[name="disc_idr"]').val();
        let voucher_idr = $('input[name="voucher_idr"]').val();
        let ongkir = $('input[name="delivery_cost_idr"]').val();

        subtotal = parseFloat(subtotal);
        disc_idr = parseFloat(disc_idr);
        voucher_idr = parseFloat(voucher_idr);
        ongkir = parseFloat(ongkir);

        disc_idr = (isNaN(disc_idr)) ? 0 : disc_idr;
        voucher_idr = (isNaN(voucher_idr)) ? 0 : voucher_idr;
        ongkir = (isNaN(ongkir)) ? 0 : ongkir;

        subFinal = Math.ceil(((subtotal - disc_idr) - voucher_idr) + ongkir);
        $('input[name="grand_total"]').val(subFinal);
      });

      $('#shipping_cost_buyer').change(function(){
        $('input[name="delivery_cost_idr"]').val(($(this).is(':checked')) ? "0" : "");
      })
  })
</script>
@endpush