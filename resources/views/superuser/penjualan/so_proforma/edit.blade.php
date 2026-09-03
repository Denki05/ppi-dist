@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.so_proforma.index') }}">Sales Order Proforma</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.so_proforma.update', $results->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="ids_delete" value="">
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
                      <input type="date" name="so_date" class="form-control"
                      value="{{ $results->so_date ? \Carbon\Carbon::parse($results->so_date)->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="type_transaction">Type Transaksi</label>
                      <input type="text" name="type_transaction" class="form-control" 
                        value="{{ $results->getStatusTypeAttribute() }}" readonly>
                    </div>
                  </div>

                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="so_date">Warehouse</label>
                        <select class="form-control js-select2" name="warehouse" required>
                            <option value="">Pilih Gudang</option>    
                            @foreach($warehouse AS $row)
                            <option value="{{$row->id}}" {{ $row->id == $results->warehouse_id ? 'selected' : '' }}>
                                {{ $row->name }}
                            </option>
                            @endforeach
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="so_date">Ekspedisi</label>
                        <select class="form-control js-select2" name="vendor" required>
                            <option value="">Pilih vendor</option>    
                            @foreach($vendor AS $row)
                            <option value="{{$row->id}}" {{ $row->id == $results->vendor_id ? 'selected' : '' }}>
                                {{ $row->name }}
                            </option>
                            @endforeach
                        </select>
                      </div>
                  </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                          <label for="note">Note</label>
                          <input type="text" name="note" class="form-control" value="{{ $results->note }}">
                        </div>
                      </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                  <div class="block">
                    <div class="block-content">
                    <div class="form-row">
                       
                        <div class="form-group col-md-4">
                        <label for="note">Brand <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="so_brand_name" id="so_brand_name">
                            <option value="">Pilih Brand</option>
                            @foreach($brand AS $row)
                            <option value="{{ $row->brand_name }}" 
                                {{ $row->brand_name == $results->so_brand_name ? 'selected' : '' }}>
                                {{ $row->brand_name }}
                            </option>
                            @endforeach
                        </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="note">Rekening <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="rekening" required>
                              <option value="">Pilih Rekening</option>
                              @foreach($rekening as $key)
                              <option value="{{$key->id}}" 
                                  {{ $key->id == $results->rekening_id ? 'selected' : '' }}>
                                  {{$key->name}} - {{$key->number_card}}
                              </option>
                              @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="idr_rate_display">Kurs <span class="text-danger">*</span></label>
                          <input type="text" id="idr_rate_display" class="form-control"
                            value="{{ number_format((float) $results->so_idr_rate, 2, ',', '.') }}" placeholder="cth: 18.050">
                          <input type="hidden" name="idr_rate" id="idr_rate" value="{{ $results->so_idr_rate }}">
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                    <h3 class="block-title">#Detail Customer</h3>
                </div>
                <div class="block-content">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                          <label for="inputField">Customer</label>
                          @if($results->exsisting_customer == 0)
                                <input type="text" class="form-control" name="customer" value="{{ $results->customer_name ?? '-' }}">
                          @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->name }} {{ optional($results->member)->text_kota }}" readonly>
                              <input type="hidden" class="form-control" name="customer" value="{{ $results->customer_other_address_id }}">
                          @endif
                      </div>


                        <div class="form-group col-md-6">
                            <label for="note">Alamat Kirim</label>
                            @if($results->exsisting_customer == 0)
                              <input type="text" class="form-control" name="customer_address" value="{{ $results->customer_address }}" readonly>
                            @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->address }}" readonly>
                            @endif
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="customer_region">Provinsi</label>
                            @if($results->exsisting_customer == 0)
                              <select class="form-control js-select2" name="customer_region" id="customer_region">
                                  <option value="">Pilih Provinsi</option>
                                  @foreach($provinsi AS $row)
                                  <option value="{{ $row->prov_id }}" {{ ($row->prov_id == $results->customer_region ) ? 'selected' : '' }}>{{ $row->prov_name }}</option>
                                  @endforeach
                              </select>
                              @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->text_provinsi }}" readonly>
                              @endif
                        </div>
                        <div class="form-group col-md-6">
                            <label for="customer_city">Kota</label>
                            @php
                                  $city = DB::table('kabupaten')->where('city_id', $results->customer_city)->first();
                              @endphp
                              <label for="customer_city">Kota</label>
                              @if($results->exsisting_customer == 0)
                              <select class="form-control js-select2" name="customer_city" id="customer_city">
                                  <option value="{{ $results->customer_city }}">{{ $city->city_name }}</option>
                              </select>
                              @else
                              <input type="text" class="form-control" value="{{ optional($results->member)->text_kota }}" readonly>
                              @endif
                        </div>
                    </div>

                    <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="customer_phone">Phone</label>
                              <input type="number" class="form-control" name="customer_phone" value="{{ optional($results->member)->phone }}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="customer_owner">Contact Person</label>
                              <input type="text" class="form-control" name="customer_owner" value="{{ optional($results->member)->contact_person }}" readonly>
                          </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                <div class="block">
                    <div class="block-content">
                    <div class="form-row">
                       
                        <div class="form-group col-md-6">
                          <label for="note">Sales Senior <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="sales_senior_id" required>
                                <option value="">Pilih Sales Senior</option>
                                @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $senior_value)
                                <option value="{{ $senior_value }}" {{ ($senior_value == $results->sales_senior_id ) ? 'selected' : '' }}>{{ $sales_senior }}</option>
                                @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="note">Sales <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="sales_id" required>
                                <option value="">Pilih Sales</option>
                                @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                                <option value="{{ $sales_value }}" {{ ($sales_value == $results->sales_id ) ? 'selected' : '' }}>{{ $sales }}</option>
                                @endforeach
                          </select>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
        </div>
    </div>

    <div class="row">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Add Product</h3>
          <a href="#" class="row-add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              <i class="fa fa-plus mr-10"></i> Row
            </button>
          </a>
        </div>
        <div class="block-content">
          <table id="datatable" class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">Free</th>
                <th class="text-center">Select Product</th>
                <th class="text-center">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Disc</th>
                <th class="text-center">Total</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($results->items as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <input type="checkbox" class="form-check-input input-gift" id="free_product" 
                    value="{{$item->free_product}}" name="free_product[]" 
                    {{ $item->free_product ? 'checked' : '' }}>
                  </td>
                  <td>
                    <select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" name="sku[]" data-placeholder="Select SKU" style="width:100%" required>
                      <option value="{{ $item->product_packaging_id }}">{{ $item->productPack->code }} - {{ $item->productPack->name }} / {{ $item->packaging->pack_name }}</option>
                    </select>
                  </td>
                  <td><input type="number" class="form-control text-center" name="price[]" value="{{ $item->free_product ? 0 : $item->price }}" readonly required></td>
                  <td><input type="number" class="form-control text-center" name="qty[]" value="{{ $item->qty }}" required step="0.01" min="0"><input type="hidden" name="packaging[]" value="{{ $item->packaging_id }}"><input type="hidden" class="form-control" name="edit[]" value="{{ $item->id }}"></td>
                  <td><input type="number" class="form-control text-center" name="disc_usd[]" value="{{ $item->disc_usd }}" required></td>
                  <td><input type="text" class="form-control text-center" name="total[]" readonly value="{{ number_format((float) $item->total_item, 2, ',', '.') }}"></td>
                  <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="block-header block-header-default">
          <div class="container">
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="subtotal">IDR Sub Total</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="subtotal" name="subtotal" readonly value="{{ number_format((float) ($detailsCost->purchase_total_idr ?? 0), 2, ',', '.') }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-1 col-form-label">Disc %</label>
              <div class="col-md-1">
                <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" value="{{ $detailsCost->discount_1_percent ?? $results->salesOrder->catatan }}">
              </div>
              <div class="col-sm-2">
                <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr" value="{{ number_format((float) ($detailsCost->discount_1 ?? 0), 2, ',', '.') }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-1 col-form-label">Disc Kemasan</label>
              <div class="col-md-1">
                <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent" value="{{ $detailsCost->discount_2_percent ?? 0 }}">
              </div>
              <div class="col-sm-2">
                <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr" value="{{ number_format((float) ($detailsCost->discount_2 ?? 0), 2, ',', '.') }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="disc_tambahan_idr">Disc IDR</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="disc_tambahan_idr" name="disc_tambahan_idr" value="{{ number_format((float) ($detailsCost->discount_idr ?? 0), 2, ',', '.') }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="voucher_idr">Voucher</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="voucher_idr" name="voucher_idr" value="{{ number_format((float) ($detailsCost->voucher_idr ?? 0), 2, ',', '.') }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="voucher_idr">Ongkir</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="delivery_cost_idr" name="delivery_cost_idr" value="{{ number_format((float) ($detailsCost->delivery_cost_idr ?? 0), 2, ',', '.') }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="grand_total" name="grand_total" readonly value="{{ number_format((float) ($detailsCost->grand_total_idr ?? 0), 2, ',', '.') }}">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row pt-30 mb-15">
          <div class="col-md-6">
            <a href="{{ route('superuser.penjualan.so_proforma.index') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-6 text-right">
            <button type="button" class="btn btn-warning mb-2" id="btn_call">
              <i class="fas fa-calculator pr-2" aria-hidden="true"></i> Calculated
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save  pr-2" aria-hidden="true" ></i> Save
            </button>
          </div>
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
    $('.js-select2').select2()

    var userInteracted = false;

    $(document).on('focus keydown paste', '#idr_rate_display, input[name="qty[]"], input[name="disc_usd[]"], #disc_agen_percent, #disc_kemasan_percent, #disc_tambahan_idr, #voucher_idr, #delivery_cost_idr', function () {
      userInteracted = true;
    });

    $('#customer_region').on('change', function(){
        let prov_id = $('#customer_region').val();
          
        $.ajax({
            type : 'POST',
            url : '{{route('superuser.master.customer.getkabupaten')}}',
            data : {prov_id:prov_id},
            cache : false,

            success: function(msg){
              $('#customer_city').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
        })
    })

    $('#customer_city').on('change', function(){
      let city_id = $('#customer_city').val();
        
      $.ajax({
        type : 'POST',
        url : '{{route('superuser.master.customer.getkecamatan')}}',
        data : {city_id:city_id},
        cache : false,

        success: function(msg){
          $('#kecamatan').html(msg);
        },
        error : function(data){
          console.log('error:',data)
        },
      });
    });

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'free', orderable: false, width: "5%"},
          {name: 'sku', orderable: false, width: "30%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'disc_usd', orderable: false, searcable: false, width: "10%"},
          {name: 'total', orderable: false, searcable: false, width: "20%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1000;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#so_brand_name').val()) {
        table.row.add([
                      counter,
                      '<input type="checkbox" class="form-check-input input-gift" id="gift" name="gift"><input class="form-control input-free" type="hidden" id="free_product" value="0" name="free_product[]">',
                      '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
                      '<input type="number" style="text-align: center;" class="form-control" name="price[]" readonly required>',
                      '<input type="number" style="text-align: center;" class="form-control" name="qty[]" readonly required step="0.01" min="0"><input type="hidden" class="form-control packaging" name="packaging[]"><input type="hidden" class="form-control" name="edit[]" value="">',
                      '<input type="number" style="text-align: center;" class="form-control" name="disc_usd[]" required>',
                      '<input type="text" style="text-align: center;" class="form-control" name="total[]" readonly>',
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );
                    initailizeSelect2();
        counter++;
      }
    });

    function initailizeSelect2(){
      $(".js-ajax").select2({
        ajax: {
          url: '{{ route('superuser.penjualan.so_proforma.search_sku') }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
                q: params.term,
                id: $('#so_brand_name').val(),
                _token: "{{csrf_token()}}"
            };
          },
          cache: true
        },
      });

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.name;
        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('input[name="qty[]"]').removeAttr('readonly');

        var $row = $(this).parents('tr');
        var isFree = $row.find('.input-gift').is(':checked');

        var price = isFree ? 0 : e.params.data.product_price;
        $row.find('input[name="price[]"]').val(price);

        var kemasan = e.params.data.IdKemasan;
        $row.find('input[name="packaging[]"]').val(kemasan);
      });

    };

    // ============================================================
    // Helper format - dicontek langsung dari create_lanjutan.blade.php
    // supaya kelakuannya sama persis di seluruh app.
    // ============================================================
    function formatNumber(angka) {
      var num = parseFloat(String(angka));
      if (isNaN(num)) return '';
      return num.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function formatInputKurs(inputValue) {
      // pisahkan bagian desimal (setelah koma terakhir) dari bagian bulat
      var parts = String(inputValue).split(',');
      var integerPart = parts[0].replace(/[^\d]/g, '');
      var decimalPart = parts.length > 1 ? parts[1].replace(/[^\d]/g, '').substring(0, 2) : '';

      if (!integerPart) return '';

      integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

      return parts.length > 1 ? integerPart + ',' + decimalPart : integerPart;
    }

    // Ambil angka bersih dari field yang sudah diformat titik ribuan
    function clean(val) {
      if (val === null || val === undefined || val === '') return 0;
      var s = String(val).replace(/\./g, '').replace(',', '.');
      var n = parseFloat(s);
      return isNaN(n) ? 0 : n;
    }

    $(document).on('input', '#idr_rate_display', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      this.setSelectionRange(newPos, newPos);

      // Sinkron ke hidden field (angka bersih tanpa titik)
      $('#idr_rate').val(this.value.replace(/\./g, ''));

      // Trigger ulang kalkulasi semua baris supaya total ikut update pakai kurs baru
      $('input[name="qty[]"]').each(function () {
        var $row = $(this).parents('tr');
        recalcRow($row);
      });
    });

    function recalcRow($row) {
      if (!userInteracted) return;

      var price = parseFloat($row.find('input[name="price[]"]').val()) || 0;
      var qty = parseFloat($row.find('input[name="qty[]"]').val()) || 0;
      var discUsd = parseFloat($row.find('input[name="disc_usd[]"]').val()) || 0;
      var kurs = clean($('#idr_rate').val());

      var total = ((price - discUsd) * qty) * kurs;

      $row.find('input[name="total[]"]').val(formatNumber(total));
      $row.find('input[name="total[]"]').change();
    }

    $('#datatable tbody').on('keyup', 'input[name="qty[]"]', function (e) {
      recalcRow($(this).parents('tr'));
    });

    $('#datatable tbody').on('keyup', 'input[name="disc_usd[]"]', function (e) {
      recalcRow($(this).parents('tr'));
    });

    $('#datatable tbody').on('change', 'input[name="total[]"]', function (e) {
      var subtotal = 0;
      $('input[name="total[]"]').each(function () {
        subtotal += clean($(this).val());
      });
      $('#subtotal').val(formatNumber(subtotal));

      grandtotal();
    });

    $('#datatable tbody').on('click', '.row-delete', function (e) {
      e.preventDefault();

      userInteracted = true;

      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit[]"]').val();
      if(edit) {
        ids_delete = $('input[name="ids_delete"]').val();
        $('input[name="ids_delete"]').val(edit+','+ids_delete);
      }

      table.row( $(this).parents('tr') ).remove().draw();

      var subtotal = 0;
      $('input[name="total[]"]').each(function () {
        subtotal += clean($(this).val());
      });
      $('#subtotal').val(formatNumber(subtotal));

      grandtotal();

    });

    $('#datatable tbody').on( 'click', '.input-gift', function (e) {
      userInteracted = true;
      var $row = $(this).parents('tr');

      if($(this).is(':checked')){
        $row.find('.input-free').val(1);
        $row.find('input[name="price[]"]').val(0);
        $row.find('input[name="disc_usd[]"]').val(0);
      }else{
        $row.find('.input-free').val(0);
      }

      recalcRow($row);
    });

    // ==========================================
    // STEP 1: HITUNG DISC AGEN
    // ==========================================
    function hitungDiscAgen() {
      var discPercent = parseFloat($('#disc_agen_percent').val()) || 0;
      var subtotal = clean($('#subtotal').val());
      var result = (subtotal * discPercent) / 100;
      $('#disc_agen_idr').val(formatNumber(result));
      // Chain: setelah disc agen, hitung disc kemasan
      hitungDiscKemasan();
    }

    // ==========================================
    // STEP 2: HITUNG DISC KEMASAN
    // ==========================================
    function hitungDiscKemasan() {
      var percentVal = $('#disc_kemasan_percent').val();
      if (percentVal !== '' && percentVal !== '0') {
        var subtotal = clean($('#subtotal').val());
        var discAgen = clean($('#disc_agen_idr').val());
        var subAfterDiscAgen = subtotal - discAgen;
        var amount = (subAfterDiscAgen * parseFloat(percentVal)) / 100;
        $('#disc_kemasan_idr').val(formatNumber(amount));
      } else {
        $('#disc_kemasan_idr').val(formatNumber(0));
      }
      // Chain: setelah disc kemasan, hitung grand total
      hitungGrandTotal();
    }

    // ==========================================
    // STEP 3: HITUNG GRAND TOTAL
    // ==========================================
    function hitungGrandTotal() {
      var subtotal = clean($('#subtotal').val());
      var discAgen = clean($('#disc_agen_idr').val());
      var discKemasan = clean($('#disc_kemasan_idr').val());
      var discIdr = clean($('#disc_tambahan_idr').val());
      var voucher = clean($('#voucher_idr').val());
      var ongkir = clean($('#delivery_cost_idr').val());
      var grandTotal = subtotal - discAgen - discKemasan - discIdr - voucher + ongkir;
      $('#grand_total').val(formatNumber(grandTotal));
    }

    // ==========================================
    // EVENT LISTENERS - Live Update
    // ==========================================
    $('#disc_agen_percent').on('keyup change', function() {
      hitungDiscAgen();
    });

    $('#disc_kemasan_percent').on('keyup change input', function() {
      hitungDiscKemasan();
    });

    $('#disc_tambahan_idr').on('keyup', function() {
      hitungGrandTotal();
    });

    $('#voucher_idr').on('keyup', function() {
      hitungGrandTotal();
    });

    $('#delivery_cost_idr').on('keyup', function() {
      hitungGrandTotal();
    });

    // Format input currency otomatis
    $(document).on('input', '#disc_tambahan_idr, #voucher_idr, #delivery_cost_idr', function() {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      if (this.selectionStart) {
        this.setSelectionRange(newPos, newPos);
      }
      hitungGrandTotal();
    });

    // ==========================================
    // TOMBOL CALCULATED (Manual Trigger)
    // ==========================================
    $(document).on('click', '#btn_call', function(e) {
      e.preventDefault();
      hitungDiscAgen();
    });

    // Legacy function name - panggil hitungDiscAgen
    function grandtotal() {
      hitungDiscAgen();
    }
  });
</script>
@endpush