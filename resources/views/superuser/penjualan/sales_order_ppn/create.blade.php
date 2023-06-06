@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index') }}">Sales Order PPN</a>
  <span class="breadcrumb-item active">Create</span>
</nav>

<form class="ajax" data-action="#" data-type="POST" enctype="multipart/form-data">
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
                    <option value="{{ $row->id }}">{{ $row->name }}</option>
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
                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="warehouse_id">Gudang <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <select class="form-control js-select2" id="warehouse_id" name="warehouse_id" data-placeholder="Pilih Gudang">
                    <option></option>
                    @foreach($warehouse as $row)
                    <option value="{{ $row->id }}">{{ $row->name }}</option>
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
                    <option value="{{ $row->id }}">{{ $row->name }}</option>
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
                    <option value="1">CASH</option>
                    <option value="2">TEMPO</option>
                    <option value="3">MARKETPLACE</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="idr_rate">Kurs <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input type="text" class="form-control idr_rate" name="idr_rate" id="idr_rate" value="1">
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
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
                </select>
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
                  <input style="font-size: 9pt;" class="form-control customer_recipient" type="text" name="customer_recipient" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="text_provinsi">Provinsi</label>
                <div class="col-md-7">
                  <input class="form-control text_provinsi" type="text" name="text_provinsi" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="text_kota">Kota</label>
                <div class="col-md-7">
                  <input class="form-control text_kota" type="text" name="text_kota" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="zipcode">Kode Pos</label>
                <div class="col-md-7">
                  <input class="form-control zipcode" type="text" name="zipcode" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="customer_address">Alamat Kirim</label>
                <div class="col-md-7">
                  <input class="form-control customer_address" type="text" name="customer_address" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="note">No. Dokumen <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input class="form-control note" type="text" name="note">
                </div>
              </div>
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
            <th class="text-center">Packaging</th>
            <th class="text-center">Disch (Cash)</th>
            <th class="text-center">Price</th>
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
          <label class="col-md-3 col-form-label text-right" for="subtotal">SubTotal</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="subtotal" name="subtotal" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="disc_percent">Disc%</label>
          <div class="col-md-1">
            <input type="text" class="form-control" id="disc_percent" name="disc_percent">
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="disc_percent_idr" name="disc_percent_idr" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="disc_pack">Disc Kemasan</label>
          <div class="col-md-1">
            <input type="text" class="form-control" id="disc_pack" name="disc_pack">
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="disc_pack_idr" name="disc_pack_idr" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="tax_ammount">Pajak</label>
          <div class="col-md-1">
            <input type="text" class="form-control" id="tax_ammount" name="tax_ammount">
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="tax_ammount_idr" name="tax_ammount_idr" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="voucher_idr">Voucher</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="voucher_idr" name="voucher_idr">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="delivery_cost">Ongkir</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="delivery_cost" name="delivery_cost">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="grand_total_idr">Grand Total</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="grand_total_idr" name="grand_total_idr">
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

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'product_id', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false, width: "15%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'packaging_id', orderable: false, searcable: false, width: "15%"},
          {name: 'disc_cash', orderable: false, searcable: false, width: "5%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'total', orderable: false, searcable: false, width: "25%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="product_id['+counter+']" name="product_id[]" data-placeholder="Select Product" style="width:100%" required></select>',
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
      
      table.row( $(this).parents('tr') ).remove().draw();
      

    });

    $('#datatable tbody').on( 'keyup', 'input[name="qty[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var disc = $(this).parents('tr').find('input[name="disc_cash[]"]').val();
      var kurs = $('#idr_rate').val();
      
      var total = ((price - disc) * $(this).val()) * kurs;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

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
            $('input[name="text_provinsi"]').val(resp.Data.text_provinsi);
            $('input[name="text_kota"]').val(resp.Data.text_kota);
            $('input[name="zipcode"]').val(resp.Data.zipcode);
            $('input[name="customer_address"]').val(resp.Data.address);
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
