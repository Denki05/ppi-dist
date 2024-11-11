@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.so_proforma.index') }}">Sales Order Proforma</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

<div id="alert-block"></div>
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
                        <input type="date" name="so_date" class="form-control" value="{{ $results->so_date }}" readonly>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="type_transaction">Type Transaksi</label>
                        <input type="text" name="type_transaction" class="form-control" value="{{ \App\Entities\Penjualan\SalesOrderProforma::TYPE_TRANSACTION['0'] }}" readonly>
                      </div>
                  </div>

                  <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="so_date">Warehouse</label>
                        <select class="form-control js-select2" name="warehouse" disabled>
                            <option value="">Pilih Gudang</option>    
                            @foreach($warehouse AS $row)
                            <option value="{{$row->id}}" {{ ($row->id == $results->warehouse_id ) ? 'selected' : '' }}>{{ $row->name }}</option>
                            @endforeach
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="so_date">Vendor</label>
                        <select class="form-control js-select2" name="vendor" disabled>
                            <option value="">Pilih vendor</option>    
                            @foreach($vendor AS $row)
                            <option value="{{$row->id}}" {{ ($row->id == $results->vendor_id ) ? 'selected' : '' }}>{{ $row->name }}</option>
                            @endforeach
                        </select>
                      </div>
                  </div>

                  <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="note">Note</label>
                        <input type="text" class="form-control" name="note" value="{{ $results->note }}" readonly>
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
                        <select class="form-control js-select2" name="so_brand_name" id="so_brand_name" disabled>
                            <option value="">Pilih Brand</option>
                            @foreach($brand AS $row)
                            <option value="{{ $row->brand_name }}" {{ ($row->brand_name == $results->so_brand_name ) ? 'selected' : '' }}>{{ $row->brand_name }}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="note">Rekening <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="rekening" disabled>
                            <option value="">Pilih Rekening</option>
                            @foreach($rekening as $key)
                            <option value="{{$key->id}}" {{ ($key->id == $results->rekening_id ) ? 'selected' : '' }}>{{$key->name}} - {{$key->number_card}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="customer_area">Kurs <span class="text-danger">*</span></label>
                            <input type="text" name="idr_rate" id="idr_rate" class="form-control" readonly value="{{ $results->so_idr_rate }}">
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
                            <label for="customer_name">Customer</label>
                            <input type="text" name="customer_name" class="form-control" readonly value="{{ $results->customer_name }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="customer_address">Alamat Kirim</label>
                            <input type="text" class="form-control" name="customer_address" readonly value="{{ $results->customer_address }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="customer_region">Provinsi</label>
                            <select class="form-control js-select2" name="customer_region" id="customer_region" disabled>
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinsi AS $row)
                                <option value="{{ $row->prov_id }}" {{ ($row->prov_id == $results->customer_region ) ? 'selected' : '' }}>{{ $row->prov_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            @php
                                $city = DB::table('kabupaten')->where('city_id', $results->customer_city)->first();
                            @endphp
                            <label for="customer_city">Kota</label>
                            <select class="form-control js-select2" name="customer_city" id="customer_city" disabled>
                                <option value="{{ $results->customer_city }}">{{ $city->city_name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="customer_phone">Phone</label>
                            <input type="number" class="form-control" name="customer_phone" readonly value="{{ $results->customer_phone }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="customer_owner">Contact Person</label>
                            <input type="text" class="form-control" name="customer_owner" readonly value="{{ $results->customer_owner }}">
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
                <th class="text-center">Select Product</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Price</th>
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
                    <select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" name="sku[]" disabled data-placeholder="Select SKU" style="width:100%" required>
                      <option value="{{ $item->product_packaging_id }}">{{ $item->productPack->code }} - {{ $item->productPack->name }} / {{ $item->packaging->pack_name }}</option>
                    </select>
                  </td>
                  <td><input type="number" class="form-control" name="qty[]" readonly value="{{ $item->qty }}" required><input type="hidden" name="packaging[]" value="{{ $item->packaging_id }}"><input type="hidden" class="form-control" name="edit[]" value="{{ $item->id }}"></td>
                  <td><input type="number" class="form-control" name="price[]" readonly value="{{ $item->price }}" required></td>
                  <td><input type="number" class="form-control" name="disc_usd[]" readonly value="{{ $item->disc_usd }}" required></td>
                  <td><input type="number" class="form-control" name="total[]" readonly value="{{ $item->total_item }}"></td>
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
                <input type="text" class="form-control" id="subtotal" name="subtotal" readonly value="{{ $results->details_cost[0]->purchase_total_idr }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-1 col-form-label">Disc %</label>
              <div class="col-md-1">
                <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" readonly value="{{ $results->details_cost[0]->discount_1_percent }}">
              </div>
              <div class="col-sm-2">
                <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr" readonly value="{{ $results->details_cost[0]->discount_1 }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-1 col-form-label">Disc Kemasan</label>
              <div class="col-md-1">
                <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent" readonly value="{{ $results->details_cost[0]->discount_2_percent }}">
              </div>
              <div class="col-sm-2">
                <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr" readonly value="{{ $results->details_cost[0]->discount_2 }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="disc_tambahan_idr">Disc IDR</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="disc_tambahan_idr" name="disc_tambahan_idr" readonly value="{{ $results->details_cost[0]->discount_idr }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="voucher_idr">Voucher</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="voucher_idr" name="voucher_idr" readonly value="{{ $results->details_cost[0]->voucher_idr }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="voucher_idr">Ongkir</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="delivery_cost_idr" name="delivery_cost_idr" readonly value="{{ $results->details_cost[0]->delivery_cost_idr }}">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="grand_total" name="grand_total" readonly value="{{ $results->details_cost[0]->grand_total_idr }}">
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
        </div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2()

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
                      '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
                      '<input type="number" style="text-align: center;" class="form-control" name="qty[]" readonly required><input type="hidden" class="form-control packaging" name="packaging[]"><input type="hidden" class="form-control" name="edit[]" value="">',
                      '<input type="number" style="text-align: center;" class="form-control" name="price[]" readonly required>',
                      '<input type="number" style="text-align: center;" class="form-control" name="disc_usd[]" required>',
                      '<input type="number" style="text-align: center;" class="form-control" name="total[]" readonly>',
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

        var price = e.params.data.product_price;
        $(this).parents('tr').find('input[name="price[]"]').val(price);

        var kemasan = e.params.data.IdKemasan;
        $(this).parents('tr').find('input[name="packaging[]"]').val(kemasan);
      });

    };

    $('#datatable tbody').on( 'keyup', 'input[name="qty[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var disc_usd = $(this).parents('tr').find('input[name="disc_usd[]"]').val();
      var kurs = $("#idr_rate").val();
      
      var total = parseFloat(((price - disc_usd) * $(this).val()) * kurs);

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="disc_usd[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var qty = $(this).parents('tr').find('input[name="qty[]"]').val();
      var kurs = $("#idr_rate").val();

      var total = parseFloat(((price - $(this).val()) * qty) * kurs);

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

    $('#datatable tbody').on( 'change', 'input[name="total[]"]', function (e) {
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      grandtotal();
    });

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();

      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit[]"]').val();
      if(edit) {
        ids_delete = $('input[name="ids_delete"]').val();
        $('input[name="ids_delete"]').val(edit+','+ids_delete);
      }

      table.row( $(this).parents('tr') ).remove().draw();
      
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      grandtotal();

    });

    $('#disc_agen_percent').on('keyup', function(e) {
      if($(this).val() != ''){
        let subtotal = $("#subtotal").val()

        let amount = subtotal * $(this).val() / 100;

        $('input[name="disc_agen_idr"]').val(amount);
        grandtotal();
      }else{
        $('input[name="disc_agen_idr').val(0);
        grandtotal();
      }
    });

    $('#disc_kemasan_percent').on('input', function(e){
      if($(this).val() != ''){
        let subtotal = $("#subtotal").val()
        let disc_percent = $('input[name="disc_agen_idr"]').val();

        let subAfterDiscPercent = subtotal - disc_percent;

        var amount = subAfterDiscPercent * $(this).val() / 100;
        $('#disc_kemasan_idr').val(amount);
        grandtotal();
      }else{
        $('#disc_kemasan_idr').val(0);
        grandtotal();
      }
    });

    $("#disc_tambahan_idr").on('keyup', function() {
      grandtotal();
    });

    $("#voucher_idr").on('keyup', function() {
      grandtotal();
    });

    $("#delivery_cost_idr").on('keyup', function() {
      grandtotal();
    });

    function grandtotal() {
      var subtotal = Number($('#subtotal').val());
      var disc_percent = Number($('#disc_agen_idr').val());
      var disc_kemasan = Number($('#disc_kemasan_idr').val());
      var disc_idr = Number($('#disc_tambahan_idr').val());
      var voucher = Number($('#voucher_idr').val());
      var ongkir = Number($('#delivery_cost_idr').val());
      var grandtotal = subtotal - disc_percent - disc_kemasan - disc_idr - voucher + ongkir;

      $('#grand_total').val(grandtotal);
    }
  });
</script>
@endpush