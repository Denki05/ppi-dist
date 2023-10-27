@extends('superuser.app')

@section('content')
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

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order.tutup_so' ) }}" data-type="POST" enctype="multipart/form-data">
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
              <!-- <!-<textarea class="form-control" rows="1" readonly>{{ $result->note }}</textarea> -->
              <input type="text" class="form-control" value="{{ $result->note ?? '-' }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
              <select class="form-control js-select2" style="font-size: 9pt;" name="origin_warehouse_id">
                <option value="">Pilih Gudang</option>
                @foreach($warehouse as $index => $row)
                <option style="font-size: 10pt;" value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Eksepdisi <span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="ekspedisi">
                <option value="">Pilih Ekspedisi</option>
                @foreach($ekspedisi as $index)
                <option value="{{ $index->id }}">{{ $index->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row justify-content-end">
            <div class="form-check-inline">
              <label class="form-check-label">
                <input type="checkbox" class="form-check-input" value="1" id="shipping_cost_buyer" name="shipping_cost_buyer">Bayar ditempat
              </label>
            </div>
          </div>
          <br>
          <br>
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
                  <label for="note">Rekening <span class="text-danger">*</span></label>
                  <select class="form-control js-select2" name="rekening">
                    <option value="">Pilih Rekening</option>
                    <option value="0">4720 2369 88 - IRWAN LINAKSITA</option>
                    <option value="1">7881 0374 95 - IDA ELISA</option>
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
            <aside class="col-lg-9">
                <div class="card border-0">
                    <div class="table-responsive">
                        <table class="table table-borderless" id="datatables" style="white-space:nowrap;width:100%;">
                            <thead class="text-muted">
                                <tr class="small text-uppercase">
                                    <th class="block" style="width:auto">#</th>
                                    <th class="block" style="width:10%">Product</th>
                                    <th class="block" style="width:auto">Qty</th>
                                    <th class="block" style="width:5%">In Stock</th>
                                    <th class="block" style="width:15%">Harga</th>
                                    <th class="block" style="width:auto">Free</th>
                                    <th class="block" style="width:20%">Kemasan</th>
                                    <th class="block" style="width:2%">Disc (USD)</th>
                                    <th class="block" style="width:30%">Total</th>
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
                                      <input type="text" name="repeater[{{$index}}][total]" class="form-control " readonly>
                                    </td>
                                  </tr>
                                @endforeach
                              @endif
                            </tbody>
                            <tfoot>
                              <tr class="row-footer-subtotal">
                                <td colspan="8" class="text-right">
                                  <b>Subtotal</b>
                                </td>
                                <td class="text-right">
                                  <input type="text" name="sub_total_item" id="sub_total_item" class="form-control " readonly step="any">
                                </td>
                              </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </aside>
            <aside class="col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                      <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Disc %</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent">
                        </div>
                        <div class="col-sm-5">
                          <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Disc Kemasan</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent">
                        </div>
                        <div class="col-sm-5">
                          <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Disc IDR</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="disc_tambahan_idr" name="disc_tambahan_idr">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Voucher</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="voucher_idr" name="voucher_idr">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Ongkir</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="delivery_cost_idr" name="delivery_cost_idr">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Grand Total</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control" id="grand_total_idr" name="grand_total_idr" readonly>
                          <input type="hidden" class="form-control" name="subtotal_2" id="subtotal_2">
                        </div>
                      </div>
                      <button type="button" class="btn btn-danger" id="btn_call"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button>
                      <button type="submit" class="btn btn-primary"><i class="fa fa-save  pr-2" aria-hidden="true"></i> Save</button>
                    </div>
                </div>
            </aside>
        </div>
</form>



@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
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
      $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val(formatRupiah(sub_total));
      
      sub_total_item();
    }

    function sub_total_item(){
      let total = 0;
      $('tbody tr').each(function(index,e){
        let sub_total = $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val();
        sub_total = parseFloat(sub_total.split('.').join(''));


        sub_total = (isNaN(sub_total)) ? 0 : sub_total;
        total += sub_total;
          
      });

      $('input[name="sub_total_item"]').val(formatRupiah(total));
    }

    $('#disc_agen_percent').on('keyup', function(e) {
      if($(this).val() != ''){
        let sub_total_item = $('input[name="sub_total_item"]').val();

        sub_total_item = parseFloat(sub_total_item.split('.').join(''));

        let amount = sub_total_item * $(this).val() / 100;

        $('input[name="disc_agen_idr"]').val(formatRupiah(amount));
      }else{
        $('input[name="disc_agen_idr').val(0);
      }
      subtotal();
    })

    $('#disc_kemasan_percent').on('input', function(e){
          if($(this).val() != ''){
              let sub_total_item = $('input[name="sub_total_item"]').val();
              let disc_percent = $('input[name="disc_agen_idr"]').val();

              sub_total_item = parseFloat(sub_total_item.split('.').join(''));
              disc_percent = parseFloat(disc_percent.split('.').join(''));

              let subAfterDiscPercent = sub_total_item - disc_percent;

              var amount = subAfterDiscPercent * $(this).val() / 100;
              $('#disc_kemasan_idr').val(formatRupiah(amount));
          }else{
              $('#disc_kemasan_idr').val(0);
          }
          subtotal();
    });

    function subtotal(){
      let sub_total = $('#sub_total_item').val();
      let disc_agen = $('#disc_agen_idr').val();
      let dics_kemasan = $('#disc_kemasan_idr').val();

      sub_total = parseFloat(sub_total.split('.').join(''));
      disc_agen = parseFloat(disc_agen.split('.').join(''));
      dics_kemasan = parseFloat(dics_kemasan.split('.').join(''));

      if(isNaN(sub_total)){
        sub_total = 0;
      }

      if(isNaN(disc_agen)){
        disc_agen = 0;
      }

      if(isNaN(dics_kemasan)){
        dics_kemasan = 0;
      }

      let sub_total_before = sub_total - disc_agen - dics_kemasan;

      $('#subtotal_2').val(formatRupiah(sub_total_before));
    };

    $('#shipping_cost_buyer').change(function(){
        $('input[name="delivery_cost_idr"]').val(($(this).is(':checked')) ? "0" : "");
    });

    $(document).on('click', '#btn_call', function(e) {
      let subtotal_before = $('#subtotal_2').val();
      let disc_tambahan = $('#disc_tambahan_idr').val();
      let voucher_idr = $('#voucher_idr').val();
      let ongkir = $('#delivery_cost_idr').val();

      subtotal_before = parseFloat(subtotal_before.split('.').join(''));
      disc_tambahan = parseFloat(disc_tambahan);
      voucher_idr = parseFloat(voucher_idr);
      ongkir = parseFloat(ongkir);

      if(isNaN(disc_tambahan)){
        disc_tambahan = 0;
      }

      if(isNaN(voucher_idr)){
        voucher_idr = 0;
      }

      if(isNaN(ongkir)){
        ongkir = 0;
      }
     

      let grand_total_idr = subtotal_before - disc_tambahan - voucher_idr + ongkir;

      $('#grand_total_idr').val(formatRupiah(grand_total_idr));
    });
    

    function formatRupiah(money) {
      return new Intl.NumberFormat('id-ID',
        { style: 'currency', currency: 'IDR' }
      ).formatToParts(money).map(
        p => p.type != 'literal' && p.type != 'currency' ? p.value : ''
      ).join('');
    }

  })
</script>
@endpush