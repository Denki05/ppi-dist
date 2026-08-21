@extends('superuser.app')

@section('content')
{{-- Pesan Error --}}
@php
    $allErrors = collect([]);
    if($errors->any()){
        $allErrors = $allErrors->merge($errors->all());
    }
    if(session('errors') && count(session('errors')) > 0){
        $allErrors = $allErrors->merge(session('errors'));
    }
@endphp

@if($allErrors->count() > 0)
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">Error</h5>
    <ul class="mb-0">
        @foreach($allErrors->unique() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Pesan Warning --}}
@if(session('warnings') && count(session('warnings')) > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">Warning</h5>
    <ul class="mb-0">
        @foreach(session('warnings') as $warning)
            <li>{{ $warning }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Pesan Sukses --}}
@if(session()->has('message'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">Success</h5>
    <p class="mb-0">{{ session('message') }}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order.tutup_so' ) }}" data-type="POST" enctype="multipart/form-data" id="formSO">
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
              <label for="so_date">Tanggal Nota</label>
              <input type="date" name="so_date" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Type Transaksi</label>
              <input type="text" name="type_transaction" class="form-control" value="{{ $result->type_transaction }}" readonly>
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
              <select class="form-control js-select2" name="ekspedisi" required>
                <option value="">Pilih Ekspedisi</option>
                @foreach($ekspedisi as $index)
                <option value="{{ $index->id }}">{{ $index->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="sales_senior_id">Sales Senior <span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="sales_senior_id" required>
                <option value="">Pilih Sales Senior</option>
                @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $senior_value)
                <option value="{{ $senior_value }}">{{ $sales_senior }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="sales_id">Sales <span class="text-danger">*</span></label>
              <select class="form-control js-select2" name="sales_id" required>
                <option value="">Pilih Sales</option>
                @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                <option value="{{ $sales_value }}">{{ $sales }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="note">Catatan</label>
              <!-- <input type="text" class="form-control" value="{{ $result->note ?? '-' }}" readonly> -->
              <textarea class="form-control" name="note" rows="1" readonly>{{ $result->note }}</textarea>
            </div>
            <div class="form-check-inline">
              <label class="form-check-label">
                <input type="checkbox" class="form-check-input" value="1" id="shipping_cost_buyer" name="shipping_cost_buyer">Bayar ditempat
              </label>
            </div>
            @if($result->count_rev == 1)
              <div class="form-check-inline">
                <label class="form-check-label">
                  <input type="checkbox" class="form-check-input" value="1" id="keep_old_code" name="keep_old_code">Previous Code
                </label>
              </div>
            @endif
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
                  <label for="note">Rekening <span class="text-danger">*</span></label>
                  <select class="form-control js-select2 required-field" name="rekening">
                    <option value="">Pilih Rekening</option>
                    @foreach($rekening as $key)
                    <option value="{{$key->id}}">{{$key->name}} - {{$key->number_card}}</option>
                    @endforeach
                  </select>
                </div>
                @endif
                @if($step == 2)
                <div class="form-group col-md-4">
                  <label for="customer_area">Kurs <span class="text-danger">*</span></label>
                  @if($result->approval_mou == 1)
                    <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $result->idr_rate }}" readonly>
                  @else
                    <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $result->idr_rate }}">
                  @endif    
                </div>
                @endif
                @if($step == 2)
                <div class="form-group col-md-4">
                  <label for="customer_area">Disc Cash <span class="text-danger">*</span></label>
                  <select class="form-control js-select2 base_disc" id="base_id">
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

                    <button type="button" class="btn btn-warning mb-2" id="btn_add_product" style="width: 20%;">
                      <i class="fas fa-plus"></i> Tambah / Edit List
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatables" style="white-space:nowrap;width:100%;">
                            <thead class="text-muted">
                                <tr class="small text-uppercase">
                                    <th class="block" style="width:auto"></th>
                                    <th class="block" style="width:auto">#</th>
                                    <th class="block" style="width:10%">Product</th>
                                    <th class="block" style="width:auto">Stock</th>
                                    <th class="block" style="width:auto">Stock <br> Order</th>
                                    <th class="block" style="width:4%">In <br> Stock</th>
                                    <th class="block" style="width:20%">Harga</th>
                                    <th class="block" style="width:auto">Free</th>
                                    <th class="block" style="width:20%">Kemasan</th>
                                    <th class="block" style="width:2%">Disc <br> (USD)</th>
                                    <th class="block" style="width:30%">Total</th>
                                </tr>
                            </thead>
                            <tbody id="product-list-body">
                              <tr>
                                <td colspan="11" align="center">Klik tombol “Tambah Produk” untuk mulai kalkulasi</td>
                              </tr>
                            </tbody>
                            <tfoot>
                              <tr class="row-footer-subtotal">
                                <td colspan="10" class="text-right">
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
                        @if($result->approval_mou == 1)
                            <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" value="{{ $result->catatan }}" readonly>
                          @else
                            <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" placeholder="{{ $result->catatan }}" required>
                          @endif
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
                          <input type="text" class="form-control required-field" id="grand_total_idr" name="grand_total_idr" readonly>
                          <input type="hidden" class="form-control" name="subtotal_2" id="subtotal_2">
                        </div>
                      </div>
                      <button type="button" class="btn btn-warning" id="btn_call"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button>
                      <button type="submit" id="btn_save_so" class="btn btn-primary"><i class="fa fa-save  pr-2" aria-hidden="true" ></i> Save</button>
                    </div>
                </div>
            </aside>
        </div>

        <div class="row pt-30 mb-15">
          <div class="col-md-6">
            <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          {{--<div class="col-md-6 text-right">
            <button type="button" class="btn bg-gd-corporate border-0 text-white" data-toggle="modal" data-target="#exampleModal">
              <i class="fa fa-times mr-10"></i> Kembali SO awal
            </button>
          </div>--}}
        </div>
</form>

<!-- Modal Kalkulasi Produk (Full List) -->
<div class="modal fade" id="productCalcModal" tabindex="-1" role="dialog" aria-labelledby="productCalcModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productCalcModalLabel">Kalkulasi Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered" id="modal-product-table">
            <thead>
              <tr>
                <th></th>
                <th>#</th>
                <th>Product</th>
                <th>Stock</th>
                <th>Stock<br>Order</th>
                <th>In<br>Stock</th>
                <th>Harga</th>
                <th>Free</th>
                <th>Kemasan</th>
                <th>Disc USD</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($result->so_detail as $index => $detail)
              @php
                  $price = $detail->price > 0 ? $detail->price : $detail->product_pack->price;
                  $stock = optional($detail->product_pack->min_stock->first())->quantity ?? 0;
                  $usdDisc = $detail->disc_usd ?? 0;
              @endphp
              <tr data-index="{{$index}}">
                <input type="hidden" id="product_packaging_id" name="repeater[{{$index}}][product_packaging_id]" value="{{$detail->product_packaging_id}}">
                <input type="hidden" id="so_qty" name="repeater[{{$index}}][so_qty]" value="{{$detail->qty}}">
                <input type="hidden" id="so_item_id" name="repeater[{{$index}}][so_item_id]" value="{{$detail->id}}">
                <input type="hidden" id="kemasan_id" name="repeater[{{$index}}][packaging]" value="{{$detail->product_pack->packaging->id}}">
                <input type="hidden" id="productName" name="repeater[{{$index}}][product_name]" value="{{$detail->product_pack->name}}">
                <input type="hidden" id="productKode" name="repeater[{{$index}}][product_code]" value="{{$detail->product_pack->code}}">
                <input type="hidden" id="packName" name="repeater[{{$index}}][pack_name]" value="{{$detail->product_pack->packaging->pack_name}}">

                <td><input type="checkbox" class="minus-check" {{$detail->qty > $stock ? 'checked' : ''}}></td>
                <td>{{$index+1}}</td>
                <td>{{$detail->product_pack->code}} - {{$detail->product_pack->name}}</td>
                <td class="stock-value">{{$stock}}</td>
                <td class="stock-order2">{{$detail->qty}}</td>
                <td>
                  <input type="number"
                      class="form-control qty"
                      value="{{$detail->qty}}"
                      min="0"
                      max="{{$detail->qty}}">
                </td>
                <td>
                  <input type="text" class="form-control price" value="{{ $detail->free_product == 1 ? 0 : $price }}">
                </td>
                <td>
                  <input class="form-check-input free-count" type="checkbox" value="{{$detail->free_product}}" name="repeater[{{$index}}][free_product]" @if($detail->free_product == 1) checked=checked @endif disabled>
                </td>
                <td>{{ $detail->product_pack->packaging->pack_name }}</td>
                <td>
                  <input type="text"
                    class="form-control disc_usd"
                    placeholder="{{ $detail->disc_usd }}">
                </td>
                <td><input type="text" class="form-control total" readonly></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="modal_save" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Modal balik ke so awal + catatan -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Kembali ke so awal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form class="ajax" data-action="{{route('superuser.penjualan.sales_order.kembali_hold', $result->id)}}" data-type="POST" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label for="catatan_kembali">Catatan <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="catatan_kembali" required>
          </div>   
          <button type="submit" class="btn mt-10 w-100 btn-alt-info">Save</button>
        </form>
      </div>

    </div>
  </div>
</div>



@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    let approval_mou = {{ $result->approval_mou ?? 0 }};

    $('.js-select2').select2();

    $('.base_disc').on('input change', function () {
      let baseDisc = parseFloat($(this).val()) || 0;

      $('#product-list-body tr').each(function(){
          let row = $(this);
          let index = row.data('index');
          let freeProduct = row.find('input[name="repeater['+index+'][free_product]"]').is(':checked');

          if(freeProduct){
              row.find('input[name="repeater['+index+'][usd_disc]"]').val(0);
          }else{
              row.find('input[name="repeater['+index+'][usd_disc]"]').val(baseDisc);
          }

          // hitung ulang per item
          count_per_item(index);
      });

      // hitung ulang subtotal
      sub_total_item();
      hitungDiscAgen();
      subtotal();
    });

    function countGetUsd() {

      let baseDisc = $('.base_disc').val();

      if(baseDisc === '0' || baseDisc === null){
          // jika disc cash belum dipilih, jangan override disc
          return;
      }

      baseDisc = parseFloat(baseDisc) || 0;

      // UPDATE MODAL TABLE
      $('#modal-product-table tbody tr').each(function(){

          let row = $(this);
          let freeProduct = row.find('.free-count').is(':checked');

          if(freeProduct){
              row.find('.disc_usd').val(0);
          }else{
              row.find('.disc_usd').val(baseDisc);
          }

      });

      recalcModalTable();

      // UPDATE MAIN TABLE
      $('#product-list-body tr').each(function(index){

          let row = $(this);
          let freeProduct = row.find('input[name="repeater['+index+'][free_product]"]').val();

          if(freeProduct == 1){
              row.find('input[name="repeater['+index+'][usd_disc]"]').val(0);
          }else{
              row.find('input[name="repeater['+index+'][usd_disc]"]').val(baseDisc);
          }

          count_per_item(index);
      });

      hitungDiscAgen();
    }

    $(document).on('keyup','.count',function(){
        let index = $(this).data('index');
        count_per_item(index);
        checkStock(index);
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

    // CHEKC STOCK HIGHLIGHT
    function checkStock(index){
        let row = $('tr.index'+index);
        let stock = parseFloat(row.find('.stock-value').text());

        let qty = parseFloat(
            row.find('input[name="repeater['+index+'][do_qty]"]').val()
        );

        if(isNaN(stock)) stock = 0;
        if(isNaN(qty)) qty = 0;

        let stockCell = row.find('.stock-value');

        stockCell.removeClass('text-danger text-warning fw-bold');

        if(stock === 0){
            stockCell.addClass('text-warning fw-bold');
        }

        if(qty > stock){
            stockCell.addClass('text-danger fw-bold');
        }
    }

    function validateMinus(){
        let valid = true;
        $('#product-list-body tr').each(function(){
            let row = $(this);
            let stock = parseFloat(row.find('.stock-value').text());
            let qty = parseFloat(row.find('.count').val());
            let minusChecked = row.find('.minus-check').is(':checked');

            if(isNaN(stock)) stock = 0;
            if(isNaN(qty)) qty = 0;

            if(qty > stock && !minusChecked){
                valid = false;
                row.addClass('table-danger');
            }else{
                row.removeClass('table-danger');
            }
        });
        return valid;
    }

    function sub_total_item() {

      let total = 0;

      $('#product-list-body tr').each(function(){
          let sub_total = $(this).find('input[name$="[total]"]').val();
          if(sub_total){
              sub_total = parseFloat(sub_total.split('.').join(''));
          }else{
              sub_total = 0;
          }
          if(isNaN(sub_total)) sub_total = 0;
          total += sub_total;
      });

      $('input[name="sub_total_item"]').val(formatRupiah(total));

      hitungDiscAgen();
    }

    // Hitung diskon agen berdasarkan input atau otomatis
    function hitungDiscAgen() {
      let discPercent = parseFloat($('#disc_agen_percent').val());
      let subTotalItem = $('input[name="sub_total_item"]').val();

      if(subTotalItem){
          subTotalItem = parseFloat(subTotalItem.split('.').join(''));
      }else{
          subTotalItem = 0;
      }

      if (isNaN(discPercent)) discPercent = 0;
      if (isNaN(subTotalItem)) subTotalItem = 0;

      let result = (subTotalItem * discPercent) / 100;

      $('#disc_agen_idr').val(formatRupiah(result)); // Format IDR
      subtotal(); // Lanjutkan perhitungan subtotal akhir
    }

    $('#disc_agen_percent').on('keyup change', function () {
      hitungDiscAgen();
    });

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

      // alert(sub_total_before);

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
     

      let grand_total_idr = subtotal_before - disc_tambahan -  voucher_idr + ongkir;

      $('#grand_total_idr').val(formatRupiah(grand_total_idr));
    });
    

    function formatRupiah(money) {
      return new Intl.NumberFormat('id-ID',
        { style: 'currency', currency: 'IDR' }
      ).formatToParts(money).map(
        p => p.type != 'literal' && p.type != 'currency' ? p.value : ''
      ).join('');
    }

    $('tbody tr').each(function(index){
      checkStock(index);
    });

    $('#formSO').on('submit', function(e){
      if(!validateMinus()){

          e.preventDefault();
          e.stopImmediatePropagation();

          Swal.fire({
              icon:'warning',
              title:'Stock Minus',
              text:'Qty melebihi stock. Centang minus product terlebih dahulu.'
          });

          return false;
      }
    });

    $('#btn_add_product').on('click', function() {
      let modal = new bootstrap.Modal(document.getElementById('productCalcModal'));
      modal.show();
      countGetUsd();  
      recalcModalTable();
    });

    // Hitung total per row di modal
    function recalcModalTable(){
        let kurs = parseFloat($('#idr_rate').val());
        if(isNaN(kurs)) kurs = 1;

        $('#modal-product-table tbody tr').each(function(){

            let row = $(this);

            let qty = parseFloat(row.find('.qty').val()) || 0;
            let price = parseFloat(row.find('.price').val()) || 0;
            let disc = row.find('.disc_usd').val();

            if(disc === '' || disc === null){
                disc = row.find('.disc_usd').attr('placeholder');
            }

            disc = parseFloat(disc) || 0;

            let stock = parseFloat(row.find('.stock-value').text()) || 0;
            let minusChecked = row.find('.minus-check').is(':checked');

            let freeProduct = row.find('.free-count').is(':checked');

            if(freeProduct){

                price = 0;
                disc = 0;

                row.find('.price').val(0).prop('readonly', true);
                row.find('.disc_usd').val(0).prop('readonly', true);

            }else{

                row.find('.price').prop('readonly', false);
                row.find('.disc_usd').prop('readonly', false);

            }

            let totalDisc = disc * qty;
            let total = ((qty * price) - totalDisc) * kurs;

            row.find('.total').val(total.toLocaleString('id-ID'));

            if(qty > stock && !minusChecked){
                row.addClass('table-danger');
            }else{
                row.removeClass('table-danger');
            }
        });
    }

    // Bind input changes untuk update total di modal
    $(document).on('input', '#modal-product-table .qty, #modal-product-table .price, #modal-product-table .disc_usd', function() {
        let row = $(this).closest('tr');
        let soQty = parseFloat(row.find('#so_qty').val());
        let qty = parseFloat(row.find('.qty').val());

        if(qty > soQty){
            Swal.fire({
                icon:'warning',
                title:'Qty melebihi order',
                text:'Qty tidak boleh lebih dari '+soQty
            });
            row.find('.qty').val(soQty);
        }
        recalcModalTable();
    });

    // Save modal → update tabel utama
    $('#modal_save').on('click', function() {

        let minusWarning = false;

        $('#modal-product-table tbody tr').each(function(){

            let row = $(this);

            let stock = parseFloat(row.find('.stock-value').text());
            let qty = parseFloat(row.find('.qty').val());
            let minusChecked = row.find('.minus-check').is(':checked');

            if(qty > stock && !minusChecked){
                minusWarning = true;
            }

        });

        if(minusWarning){
            Swal.fire({
                icon: 'warning',
                title: 'Stock Minus',
                text: 'Ada qty melebihi stock. Centang minus product jika ingin melanjutkan.',
            });
            return;
        }

        let tbodyMain = $('#product-list-body');
        tbodyMain.empty();

        $('#modal-product-table tbody tr').each(function(index) {

            let row = $(this);

            let productText = row.find('td:nth-child(3)').text();
            let kemasan = row.find('td:nth-child(9)').text();

            let productPackagingId = row.find('#product_packaging_id').val();
            let soItemID = row.find('#so_item_id').val();
            let kemasanID = row.find('#kemasan_id').val();
            let productCode = row.find('#productKode').val();
            let productName = row.find('#productName').val();
            let packName = row.find('#packName').val();

            let freeProduct = row.find('.free-count').is(':checked') ? 1 : 0;
            let stock = row.find('.stock-value').text();

            let qty = parseFloat(row.find('.qty').val()) || 0;
            let price = parseFloat(row.find('.price').val()) || 0;
            let usdDisc = parseFloat(row.find('.disc_usd').val()) || 0;

            let minusChecked = row.find('.minus-check').is(':checked') ? 1 : 0;

            let kurs = $('#idr_rate').val();
            if(isNaN(kurs)) kurs = 1;

            let total = ((qty * price) - (qty * usdDisc)) * kurs;

            let html = `
            <tr class="index${index}" data-index="${index}">

            <input type="hidden" name="repeater[${index}][product_packaging_id]" value="${productPackagingId}">
            <input type="hidden" name="repeater[${index}][so_qty]" value="${qty}">
            <input type="hidden" name="repeater[${index}][so_item_id]" value="${soItemID}">

            <td>
                <div class="form-check">
                    <input class="form-check-input minus-check"
                    type="checkbox"
                    name="repeater[${index}][minusProduct]"
                    value="1"
                    ${minusChecked ? 'checked' : ''}>
                </div>
            </td>

            <td>${index+1}</td>

            <td>${productCode} - ${productName}</td>

            <td class="stock-value">${stock}</td>

            <td>${qty}</td>

            <td>
                <input type="number"
                name="repeater[${index}][do_qty]"
                class="form-control count"
                data-index="${index}"
                value="${qty}" step="any" readonly>
            </td>

            <td>
                <input type="text"
                name="repeater[${index}][price]"
                class="form-control price text-center"
                value="${price}" readonly>
            </td>

            <td>
                <input class="form-check-input free-count"
                type="checkbox"
                value="1"
                name="repeater[${index}][free_product]"
                ${freeProduct ? 'checked' : ''}
                disabled>
            </td>

            <td>
                <input type="text"
                name="kemasan"
                class="form-control text-center"
                readonly value="${packName}">
                <input type="hidden"
                name="repeater[${index}][packaging]"
                value="${kemasanID}">
            </td>

            <td>
                <input type="text"
                name="repeater[${index}][usd_disc]"
                class="form-control count count-disc text-center"
                data-index="${index}"
                value="${usdDisc}" readonly>
            </td>

            <td>
                <input type="text"
                name="repeater[${index}][total]"
                class="form-control"
                readonly value="${formatRupiah(total)}">
            </td>

            </tr>
            `;

            tbodyMain.append(html);

            checkStock(index);
            count_per_item(index);
        });

        sub_total_item();

        bootstrap.Modal.getInstance(
            document.getElementById('productCalcModal')
        ).hide();
    });

    $(document).on('keyup change','.count',function(){

        let index = $(this).data('index');

        count_per_item(index);

        checkStock(index);

    });

    $('#btn_save_so').on('click', function(e){
      if(!validateMinus()){

          e.preventDefault();

          Swal.fire({
              icon:'warning',
              title:'Stock Minus',
              text:'Qty melebihi stock. Centang minus product terlebih dahulu.'
          });

          return false;
      }
    });
  })
</script>
@endpush