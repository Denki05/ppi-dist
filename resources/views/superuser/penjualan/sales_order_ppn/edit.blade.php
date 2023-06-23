@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index') }}">Sales Order PPN</a>
  <span class="breadcrumb-item active">Create</span>
</nav>

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
<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order_ppn.update', $sales_order->id) }}" data-type="POST" enctype="multipart/form-data">
<input type="hidden" name="_method" value="PUT">
<input type="hidden" name="ids_delete" value="">
@csrf
  <input type="hidden" name="ajukankelanjutan" value="0">
  <div class="row">
    <div class="col-8">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="sales_senior_id">Sales Senior <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <select class="form-control js-select2" id="sales_senior_id" name="sales_senior_id" data-placeholder="Select Sales Senior">
                    <option></option>
                    @foreach($sales as $row)
                    <option value="{{ $row->id }}" {{ ($row->id == $sales_order->sales_senior_id ) ? 'selected' : '' }}>{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="sales_id">Sales <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <select class="form-control js-select2" id="sales_id" name="sales_id" data-placeholder="Select Sales">
                    <option></option>
                    @foreach($sales as $row)
                    <option value="{{ $row->id }}" {{ ($row->id == $sales_order->sales_id ) ? 'selected' : '' }}>{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="origin_warehouse_id">Gudang <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <select class="form-control js-select2" id="origin_warehouse_id" name="origin_warehouse_id" data-placeholder="Pilih Gudang">
                    <option></option>
                    @foreach($warehouse as $row)
                    <option value="{{ $row->id }}" {{ ($row->id == $sales_order->origin_warehouse_id ) ? 'selected' : '' }}>{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="ekspedisi_id">Ekspedisi <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <select class="form-control js-select2" id="ekspedisi_id" name="ekspedisi_id" data-placeholder="Pilih Ekspedisi">
                    <option></option>
                    @foreach($ekspedisi as $row)
                    <option value="{{ $row->id }}" {{ ($row->id == $sales_order->vendor_id ) ? 'selected' : '' }}>{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="type_transaction">Transaksi <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <select class="form-control js-select2" id="type_transaction" name="type_transaction" data-placeholder="Select Transaksi">
                    <option value="">Pilih Transaksi</option>
                    @foreach($type_transaction as $row)
                    <option value="{{$row}}" {{ ($row == $sales_order->type_transaction ) ? 'selected' : '' }}>{{$row}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="idr_rate">Kurs <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input type="text" class="form-control idr_rate" name="idr_rate" id="idr_rate" value="{{$sales_order->idr_rate}}">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="form-group row">
              <label class="col-md-4 col-form-label text-right" for="customer_other_address_id">Customer <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <select class="form-control js-select2 select-customer" name="customer_other_address_id" id="customer_other_address_id" data-placeholder="Select Customer">
                <option value=""></option>
                @foreach($member as $key => $row)
                <option value="{{$row->id}}" {{ ($row->id == $sales_order->customer_other_address_id ) ? 'selected' : '' }}>{{$row->name}}</option>
                @endforeach
                </select>
                <input type="hidden" class="form-control" name="customer_id" id="customer_id">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <br>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="customer_recipient">Penerima</label>
                <div class="col-md-7">
                  <input style="font-size: 9pt;" class="form-control customer_recipient" type="text" name="customer_recipient" value="{{$sales_order->member->name}}-{{$sales_order->member->contacts_person}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="text_provinsi">Provinsi</label>
                <div class="col-md-7">
                  <input class="form-control text_provinsi" type="text" name="text_provinsi" value="{{$sales_order->member->text_provinsi}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="text_kota">Kota</label>
                <div class="col-md-7">
                  <input class="form-control text_kota" type="text" name="text_kota" value="{{$sales_order->member->text_kota}}" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="zipcode">Kode Pos</label>
                <div class="col-md-7">
                  <input class="form-control zipcode" type="text" name="zipcode" value="{{$sales_order->member->zipcode}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="customer_address">Alamat Kirim</label>
                <div class="col-md-7">
                  <input class="form-control customer_address" type="text" name="customer_address" value="{{$sales_order->member->address}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="note">No. Dokumen <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input class="form-control note" type="text" name="note" value="{{$sales_order->note}}">
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row pt-30">
            <div class="col-md-6">
              <a href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">
                <button type="button" class="btn bg-gd-cherry border-0 text-white">
                  <i class="fa fa-arrow-left mr-10"></i> Back
                </button>
              </a>
            </div>
            <div class="col-md-6 text-right">
            <button type="submit" class="btn bg-gd-corporate border-0 text-white">
              Submit <i class="fa fa-arrow-right ml-10"></i>
            </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr>

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
      <table id="datatable" class="table table-striped table-vcenter">
        <thead>
          <tr>
            <th class="text-center">Counter</th>
            <th class="text-center">Select Product</th>
            <th class="text-center">Product</th>
            <th class="text-center">Qty</th>
            <th class="text-center">packaging</th>
            <th class="text-center">Disc(Cash)</th>
            <th class="text-center">Price</th>
            <th class="text-center">Total</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($sales_order->so_detail as $detail)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><select class="js-select2 form-control js-ajax" id="product[{{ $loop->iteration }}]" name="product[]" data-placeholder="Select Product" style="width:100%" required><option value="{{ $detail->product_id }}">{{ $detail->product->code }}</option></select></td>
              <td><span class="name">{{ $detail->product->name }}</span><input type="hidden" class="form-control" name="edit[]" value="{{ $detail->id }}"></td>
              <td><input type="number" class="form-control" name="qty[]" value="{{ $detail->qty }}" required></td>
              <td><input type="text" class="form-control" value="{{ $detail->product->category->packaging->pack_name }}" required><input type="hidden" class="form-control" name="packaging_id[]" value="{{$detail->product->category->packaging->id}}"></td>
              <td><input type="number" class="form-control" name="disc_cash[]" value="{{ $detail->doItem[0]->usd_disc }}" required></td>
              <td><input type="number" class="form-control" name="price[]" value="{{ $detail->product->selling_price }}" required></td>
              <td><input type="number" class="form-control" name="total[]" readonly value="{{$detail->doItem[0]->total * $sales_order->idr_rate}}"></td>
              <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="block-header block-header-default">
      <div class="container">
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="subtotal">SubTotal</label>
          <div class="col-md-2">
            <input type="text" class="form-control subtotal" id="subtotal" name="subtotal" readonly value="{{$do->do_cost[0]->purchase_total_idr}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="disc_percent">Disc%</label>
          <div class="col-md-1">
            <input type="text" class="form-control disc_percent" id="disc_percent" name="disc_percent" value="{{$do->do_cost[0]->discount_1}}">
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="disc_percent_idr" name="disc_percent_idr" readonly value="{{$do->do_cost[0]->discount_1_idr}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="disc_pack">Disc Kemasan</label>
          <div class="col-md-1">
            <input type="text" class="form-control" id="disc_pack" name="disc_pack" value="{{$do->do_cost[0]->discount_2}}">
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="disc_pack_idr" name="disc_pack_idr" readonly value="{{$do->do_cost[0]->discount_2_idr}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="discount_idr">Disc IDR</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="discount_idr" name="discount_idr" value="{{$do->do_cost[0]->discount_idr}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="tax_ammount">Pajak</label>
          <div class="col-md-1">
            <input class="form-check-input" type="checkbox" value="" id="tax_ammount" name="tax_ammount" checked>
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="tax_ammount_idr" name="tax_ammount_idr" readonly value="{{$do->do_cost[0]->ppn}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="voucher_idr">Voucher</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="voucher_idr" name="voucher_idr" value="{{$do->do_cost[0]->voucher_idr}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="delivery_cost">Ongkir</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="delivery_cost" name="delivery_cost" value="{{$do->do_cost[0]->delivery_cost_idr}}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="grand_total_idr">Grand Total</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="grand_total_idr" name="grand_total_idr" readonly value="{{$do->do_cost[0]->grand_total_idr}}">
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()
    initailizeSelect2();

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'product', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false, width: "15%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'packaging', orderable: false, searcable: false, width: "15%"},
          {name: 'disc_cash', orderable: false, searcable: false, width: "5%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'total', orderable: false, searcable: false, width: "25%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1000;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="product['+counter+']" name="product[]" data-placeholder="Select Product" style="width:100%" required></select>',
                    '<span class="name"></span>',
                    '<input type="number" class="form-control" name="qty[]" required>',
                    '<input type="hidden" class="form-control" name="packaging_id[]" readonly required><input type="text" class="form-control" name="pack_name" readonly>',
                    '<input type="number" class="form-control" name="disc_cash[]" required>',
                    '<input type="number" class="form-control" name="price[]" readonly required>',
                    '<input type="number" class="form-control" name="total[]" readonly>',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
                  // $('.js-select2').select2()
                  initailizeSelect2();
      counter++;
    });

    function initailizeSelect2(){
      $(".js-ajax").select2({
        ajax: {
          url: '{{ route('superuser.penjualan.sales_order.search_sku') }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term,
              _token: "{{csrf_token()}}"
            };
          },
          results: function (data, params) {
            return {
              results: data
            };
          },
          cache: true
        },
        minimumInputLength: 3,
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatData,
        placeholder: "Select Product",
      });

      function formatData (data) {
        if (data.loading) return data.productName;
        markup = data.text + '&nbsp - &nbsp' + data.productName + '&nbsp - &nbsp' + data.packagingName;
        return markup;
      };

      function formatDataSelection (data) {
        return data.productName;
      };

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.productName;
        var packagingName = e.params.data.packagingName;
        var packagingId = e.params.data.packId;
        var productPrice = e.params.data.productPrice;

        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('input[name="packaging_id[]"]').val(packagingId);
        $(this).parents('tr').find('input[name="pack_name"]').val(packagingName);
        $(this).parents('tr').find('input[name="quantity[]"]').removeAttr('readonly');
        $(this).parents('tr').find('input[name="price[]"]').val(productPrice);
      });

    };

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

      $("#tax_ammount").change();
      grandtotal();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="qty[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var disc = $(this).parents('tr').find('input[name="disc_cash[]"]').val();
      var kurs = $('#idr_rate').val();
      
      var total = ((price - disc) * $(this).val()) * kurs;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="disc_cash[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var qty = $(this).parents('tr').find('input[name="qty[]"]').val();
      var kurs = $('#idr_rate').val();
      
      var total = ((price - $(this).val()) * qty) * kurs;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

    $('#datatable tbody').on( 'change', 'input[name="total[]"]', function (e) {
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      $("#tax_ammount").change();
      grandtotal();
    });

    $("#disc_percent").on('keyup', function() {
      let ammount = ($("#subtotal").val() * $(this).val()) / 100;

      $("#disc_percent_idr").val(ammount);
      grandtotal();
    });

    $("#disc_pack").on('keyup', function(){
      let subtotal = $("#subtotal").val();
      let disc_percent = $("#disc_percent_idr").val();

      let disc_after_percent = subtotal - disc_percent;
      let ammount = (disc_after_percent * $(this).val()) / 100;
      $("#disc_pack_idr").val(ammount);
      grandtotal();
    });

    $("#discount_idr").on('keyup', function() {
        grandtotal();
    });

    $("#voucher_idr").on('keyup', function() {
        grandtotal();
    });

    $("#delivery_cost").on('keyup', function() {
        grandtotal();
    });

    $("#tax_ammount").change(function() {
        if(this.checked) {
          var tax = ($('#subtotal').val() * 11) / 100;

          $('#tax_ammount_idr').val(tax);
        } else {
          $('#tax_ammount_idr').val('');
        }
        grandtotal();
    });

    function grandtotal() {
      var subtotal = Number($('#subtotal').val());
      var tax = Number($('#tax_ammount_idr').val());
      var discPercent = Number($('#disc_percent_idr').val());
      var discPack = Number($('#disc_pack_idr').val());
      var disc_idr = Number($('#discount_idr').val());
      var voucher = Number($('#voucher_idr').val());
      var ongkir = Number($('#delivery_cost').val());
      
      var grandtotal = subtotal - discPercent - discPack - disc_idr + tax - voucher + ongkir;

      $('#grand_total_idr').val(grandtotal);
    }

    function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }

    $(document).on('change','.select-customer',function(){
      let val = $(this).val();
      if(val != ""){
        customer_address(val);
      }else{
        $('input[name="customer_recipient"]').val("");
        $('input[name="text_provinsi"]').val("");
        $('input[name="text_kota"]').val("");
        $('input[name="zipcode"]').val("");
        $('input[name="customer_address"]').val("");
      }
    })

    function customer_address(id){
        ajaxcsrfscript();
        $.ajax({
        url : '{{route('superuser.penjualan.sales_order_ppn.ajax_customer_detail')}}',
        method : "POST",
        data : {id:id},
        dataType : "JSON",
        success : function(resp){
            if(resp.IsError == true){
            showToast('danger',resp.Message);
            }
            else{
              $('input[name="customer_recipient"]').val(resp.Data.contact_person + ' ' + '-' + ' ' + resp.Data.name);
              // $('input[name="text_provinsi"]').val(resp.Data.text_provinsi);
              $('input[name="text_provinsi"]').val(resp.Data.text_provinsi);
              $('input[name="text_kota"]').val(resp.Data.text_kota);
              $('input[name="zipcode"]').val(resp.Data.zipcode);
              $('input[name="customer_address"]').val(resp.Data.address);
              $('#customer_id').val(resp.Data.customer_id);
            }
        },
        error : function(){
            alert('Cek Koneksi Internet');
        },
        })
    }
  
  });
</script>
@endpush