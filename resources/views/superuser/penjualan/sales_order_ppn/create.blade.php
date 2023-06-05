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
                  <input type="text" class="form-control" name="idr_rate" id="idr_rate">
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
                  <input class="form-control customer_recipient" type="text" name="customer_recipient" readonly>
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
                <label class="col-md-4 col-form-label text-right" for="note">Nomer Dokumen <span class="text-danger">*</span></label>
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
          {name: 'sku', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false},
          {name: 'quantity', orderable: false, searcable: false, width: "5%"},
          {name: 'price', orderable: false, searcable: false},
          {name: 'total', orderable: false, searcable: false},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
                    '<span class="name"></span>',
                    '<input type="number" class="form-control" name="quantity[]" readonly required>',
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
          cache: true
        },
        minimumInputLength: 3,
      });

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.productName;
        var price = e.params.data.productPrice;
        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('input[name="quantity[]"]').removeAttr('readonly');
        $(this).parents('tr').find('input[name="price[]"]').val(price);
      });

    };
    
  
    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      
      table.row( $(this).parents('tr') ).remove().draw();
      
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      $("#tax_checked").change();
      grandtotal();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="quantity[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var idr_rate = $('input[name="idr_rate"]').val();
      var total = ($(this).val() * price) * idr_rate;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="price[]"]', function (e) {
      var quantity = $(this).parents('tr').find('input[name="quantity[]"]').val();
      var total = $(this).val() * quantity;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();
    });
    
    $('#datatable tbody').on( 'change', 'input[name="total[]"]', function (e) {
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      $("#tax_checked").change();
      grandtotal();
    });

    $("#tax_checked").change(function() {
        if(this.checked) {
          var tax = ($('#subtotal').val() * 11) / 100;

          $('#tax').val(tax);
        } else {
          $('#tax').val('');
        }
        grandtotal();
    });

    $("#discount").on('keyup', function() {
        grandtotal();
    });

    $("#shipping_fee").on('keyup', function() {
        grandtotal();
    });

    function grandtotal() {
      var subtotal = Number($('#subtotal').val());
      var tax = Number($('#tax').val());
      var discount = Number($('#discount').val());
      var shipping_fee = Number($('#shipping_fee').val());
      var grandtotal = subtotal + tax - discount + shipping_fee;

      $('#grand_total').val(grandtotal);
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
            $('input[name="customer_recipient"]').val(resp.Data.contact_person);
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
