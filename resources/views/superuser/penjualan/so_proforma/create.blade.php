@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.so_proforma.index') }}">Sales Order Proforma</a>
  <span class="breadcrumb-item active">Create</span>
</nav>

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.so_proforma.store' ) }}" data-type="POST" enctype="multipart/form-data">
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
                      <input type="date" name="so_date" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="type_transaction">Type Transaksi</label>
                      <select class="form-control js-select2" name="type_transaction">
                        <option value="">Pilih Type Transaksi</option>
                        @foreach(\App\Entities\Penjualan\SalesOrderProforma::TYPE_TRANSACTION AS $type => $transaction)
                        <option value="{{ $type }}">{{ $transaction }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="so_date">Warehouse</label>
                        <select class="form-control js-select2" name="warehouse">
                            <option value="">Pilih Gudang</option>    
                            @foreach($warehouse AS $row)
                            <option value="{{$row->id}}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="so_date">Ekspedisi</label>
                        <select class="form-control js-select2" name="vendor">
                            <option value="">Pilih Ekspedisi</option>    
                            @foreach($vendor AS $row)
                            <option value="{{$row->id}}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                      </div>
                  </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                          <label for="type_transaction">Note</label>
                          <input type="text" name="note" class="form-control">
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
                            <option value="{{ $row->brand_name }}">{{ $row->brand_name }}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="note">Rekening <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="rekening">
                            <option value="">Pilih Rekening</option>
                            @foreach($rekening as $key)
                            <option value="{{$key->id}}">{{$key->name}} - {{$key->number_card}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label for="idr_rate">Kurs <span class="text-danger">*</span></label>
                          <input type="text" name="idr_rate" id="idr_rate"  class="form-control">
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
                          <input type="text" name="customer_name" id="inputField" class="form-control">

                          <div id="selectContainer" style="display: none;">
                              <label for="selectField"></label>
                              <select id="selectField" name="customer_exisiting" class="form-control js-select2" data-placeholder="Pilih Customer">
                                  
                              </select>
                          </div>
                      </div>


                        <div class="form-group col-md-6">
                            <label for="note">Alamat Kirim</label>
                            <input type="text" name="customer_address" id="customer_address" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="customer_region">Provinsi</label>
                            <select class="form-control js-select2" name="customer_region" id="customer_region">
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinsi AS $row)
                                <option value="{{ $row->prov_id }}">{{ $row->prov_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="customer_city">Kota</label>
                            <select class="form-control js-select2" name="customer_city" id="customer_city">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="customer_phone">Phone</label>
                            <input type="number" class="form-control" name="customer_phone" id="customer_phone">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="customer_owner">Contact Person</label>
                            <input type="text" class="form-control" name="customer_owner" id="customer_owner">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-3 col-form-label required"><b>Existing Customer</b></div>
                        <div class="col">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="existing_customer" id="existing_customer" value="1">
                            </label>
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
                          <select class="form-control js-select2" name="sales_senior_id">
                            <option value="">Pilih Sales Senior</option>
                            @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $senior_value)
                            <option value="{{ $senior_value }}">{{ $sales_senior }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="note">Sales <span class="text-danger">*</span></label>
                          <select class="form-control js-select2" name="sales_id">
                            <option value="">Pilih Sales</option>
                            @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                            <option value="{{ $sales_value }}">{{ $sales }}</option>
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
                <th class="text-center">Qty</th>
                <th class="text-center">Price</th>
                <th class="text-center">Disc</th>
                <th class="text-center">Total</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="block-header block-header-default">
          <div class="container">
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="subtotal">IDR Sub Total</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="subtotal" name="subtotal" readonly>
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-1 col-form-label">Disc %</label>
              <div class="col-md-1">
                <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent">
              </div>
              <div class="col-sm-2">
                <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-1 col-form-label">Disc Kemasan</label>
              <div class="col-md-1">
                <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent">
              </div>
              <div class="col-sm-2">
                <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="disc_tambahan_idr">Disc IDR</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="disc_tambahan_idr" name="disc_tambahan_idr">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="voucher_idr">Voucher</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="voucher_idr" name="voucher_idr">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="voucher_idr">Ongkir</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="delivery_cost_idr" name="delivery_cost_idr">
              </div>
            </div>
            <div class="form-group row justify-content-end">
              <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
              <div class="col-md-2">
                <input type="text" class="form-control" id="grand_total" name="grand_total" readonly>
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

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#so_brand_name').val()) {
        table.row.add([
                      counter,
                      '<input type="checkbox" class="form-check-input input-gift" id="gift" name="gift"><input class="form-control input-free" type="hidden" id="free_product" value="0" name="free_product[]">',
                      '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
                      '<input type="number" style="text-align: center;" class="form-control" name="qty[]" readonly required step="any"><input type="hidden" class="form-control packaging" name="packaging[]">',
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
      var priceInput = $(this).parents('tr').find('input[name="price[]"]');
      var disc_usd = $(this).parents('tr').find('input[name="disc_usd[]"]').val();
      var kurs = $("#idr_rate").val();
      var isFree = $(this).parents('tr').find('.input-free').val(); // Check if product is free
      
      var price = isFree == 1 ? 0 : priceInput.val(); // Set price to 0 if free

      var total = parseFloat(((price - disc_usd) * $(this).val()) * kurs);

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();
    });


    $('#datatable tbody').on( 'keyup', 'input[name="disc_usd[]"]', function (e) {
      var priceInput = $(this).parents('tr').find('input[name="price[]"]');
      var qty = $(this).parents('tr').find('input[name="qty[]"]').val();
      var kurs = $("#idr_rate").val();
      var isFree = $(this).parents('tr').find('.input-free').val(); // Check if product is free
      
      var price = isFree == 1 ? 0 : priceInput.val(); // Set price to 0 if free

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
      
      table.row( $(this).parents('tr') ).remove().draw();
    });

    $('#datatable tbody').on( 'click', '.input-gift', function (e) {
      if($(this).is(':checked')){
        $(this).parents('tr').find('.input-free').val(1);

        $(this).parents('tr').find('input[name="price[]"]').val(0);
      }else{
        $(this).parents('tr').find('.input-free').val(0);
      }
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

    $('#existing_customer').change(function() {
        if ($(this).is(':checked')) {
            $('#inputField').hide(); // Hide the input fields
            $('#selectContainer').show(); // Show the Select2 container

            $('#selectField').select2({
                ajax: {
                    url: '{{ route('superuser.penjualan.so_proforma.getCustomer') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results // Use 'results' as per your method response
                        };
                    },
                    cache: true
                }
            });

            $('#customer_address').attr('readonly', true);
            $('#customer_phone').attr('readonly', true);
            $('#customer_owner').attr('readonly', true);
            $('#customer_region').attr('disabled', true);
            $('#customer_city').attr('disabled', true);

            // Handle selection
            $('#selectField').on('select2:select', function (e) {
                var data = e.params.data; // Get selected data
                $('#customer_address').val(data.address).change();
                $('#customer_phone').val(data.phone).change();
                $('#customer_owner').val(data.owner).change();
                $('#customer_region').val(data.provinsi).change(); // Assuming `provinsi` is for region
                $('#customer_city').val(data.kota).change(); // Assuming `kota` is for city
            });

        } else {
            $('#inputField').show(); // Show the input fields
            $('#selectContainer').hide(); // Hide the Select2 container
            $('#selectField').select2('destroy'); // Destroy Select2

            // Remove readonly and disabled attributes
            $('#customer_address').removeAttr('readonly');
            $('#customer_phone').removeAttr('readonly');
            $('#customer_owner').removeAttr('readonly');
            $('#customer_region').removeAttr('disabled');
            $('#customer_city').removeAttr('disabled');
        }
    });


  });
</script>
@endpush