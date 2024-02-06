@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">SO Khusus(PPN)</a>
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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order_ppn.store') }}" data-type="POST" enctype="multipart/form-data">
@csrf
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
                <option value="">Pilih Transaksi Type </option>
                <option value="CASH">CASH </option>
                <option value="TEMPO">TEMPO </option>
                <option value="MARKETPLACE">MARKETPLACE </option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="so_date">Sales Senior</label>
              <select class="form-control js-select2" name="sales_senior_id">
                <option value="">Pilih Sales Senior</option>
                @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $senior_value)
                <option value="{{ $senior_value }}">{{ $sales_senior }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Sales</label>
              <select class="form-control js-select2" name="sales_id">
                <option value="">Pilih Sales</option>
                @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                  <option value="{{ $sales_value }}">{{ $sales }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
              <select class="form-control js-select2" style="font-size: 9pt;" name="origin_warehouse_id">
                <option value="">Pilih Gudang</option>
                @foreach($warehouse as $index => $row)
                <option style="font-size: 10pt;" value="{{ $row->id }}">{{$row->name}}</option>
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

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="note">Brand</label>
              <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name" data-placeholder="Plih Brand/Merek">
              </select>
            </div>

            <div class="form-check-inline col-md-4">
              <label class="form-check-label">
                <input type="checkbox" class="form-check-input" value="1" id="invoice_ppn" name="invoice_ppn">PPN
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
                  <select class="form-control js-select2" name="customer_name" id="customer_name">
                    <option value="">Select Customer</option>
                    @foreach($member as $row)
                      <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label for="note">Alamat Kirim</label>
                  <!-- <textarea class="form-control" rows="1" readonly></textarea> -->
                  <input type="text" class="form-control" name="customer_address" id="customer_address" readonly>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="customer_city">Kota</label>
                  <input type="text" name="customer_city" id="customer_city" class="form-control" value="" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label for="customer_area">Provinsi</label>
                  <input type="text" name="customer_area" id="customer_area" class="form-control" value="" readonly>
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
                <div class="form-group col-md-4">
                  <label for="customer_area">No. Dokumen <span class="text-danger">*</span></label>
                  <input type="text" name="no_document" id="no_document"  class="form-control">
                </div>
                <div class="form-group col-md-4">
                  <label for="note">Rekening <span class="text-danger">*</span></label>
                  <select class="form-control js-select2" name="rekening">
                    <option value="">Pilih Rekening</option>
                    @foreach(\App\Entities\Penjualan\SalesOrder::REKENING as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-4">
                  <label for="customer_area">Kurs <span class="text-danger">*</span></label>
                  <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
      <aside class="col-lg-9">
        <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#Add Product</h3>
            <a href="#" class="row-add">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                <i class="fa fa-plus mr-10"></i> Row
              </button>
            </a>
          </div>
          <div class="block-content">
            <table id="datatables" class="table table-striped">
              <thead>
                <tr>
                  <th class="text-center">Counter</th>
                  <th class="text-center">Produk</th>
                  <th class="text-center">Harga</th>
                  <th class="text-center">Qty</th>
                  <th class="text-center">Disc</th>
                  <th class="text-center">Subtotal</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
                <tr class="row-footer-subtotal">
                  <td colspan="5" class="text-right">
                    <b>Subtotal</b>
                  </td>
                  <td class="text-right">
                    <input type="text" name="sub_total_item" id="sub_total_item" class="form-control " readonly step="any">
                  </td>
                </tr>
              </tfoot>
            </table>
            <br>
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
              <label class="col-sm-4 col-form-label">Pajak</label>
              <div class="col-sm-3">
                <input type="text" class="form-control" id="ppn_percent" name="ppn_percent">
              </div>
              <div class="col-sm-5">
                <input type="text" class="form-control" id="ppn_idr" name="ppn_idr" readonly>
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
            <button type="button" class="btn btn-warning" id="btn_call"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button>
            <button type="submit" class="btn btn-primary" id="save_form"><i class="fa fa-save  pr-2" aria-hidden="true" ></i> Save</button>
          </div>
        </div>
    </aside>
  </div>

  <div class="row pt-30 mb-15">
    <div class="col-md-6">
      <a href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">
        <button type="button" class="btn bg-gd-cherry border-0 text-white">
          <i class="fa fa-arrow-left mr-10"></i> Back
        </button>
      </a>
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
    $('.js-select2').select2();

    $(document).on('change','#customer_name',function(){
      let val = $(this).val();
      if(val != ""){
        customer_address(val);
      }else{
        $('$customer_address').val("");
        $('$customer_city').val("");
        $('$customer_area').val("");
      }
    })

    function formatRupiah(money) {
      return new Intl.NumberFormat('id-ID',
        { style: 'currency', currency: 'IDR' }
      ).formatToParts(money).map(
        p => p.type != 'literal' && p.type != 'currency' ? p.value : ''
      ).join('');
    }

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
            // $('textarea[name="address"]').val(resp.Data.address);
            $('#customer_address').val(resp.Data.address);
            $('#customer_city').val(resp.Data.text_kota);
            $('#customer_area').val(resp.Data.text_provinsi);
          }
        },
        error : function(){
          alert('Cek Koneksi Internet');
        },
      })
    }

    $(".js-select2-brand").select2({
      ajax: {
        url: '{{ route('superuser.penjualan.sales_order_ppn.get_brand') }}',
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
    });

    var product_data = new Object();

    var table = $('#datatables').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'sku', orderable: false, width: "35%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'disc', orderable: false, searcable: false, width: "10%"},
          {name: 'subtotal', orderable: false, searcable: false, width: "20%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#brand_name').val()) {
        makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';

        $.map( product_data, function( val, i ) {
          makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-packname="'+ val['packName'] +'" data-price="'+ val['price'] +'" data-packid="'+ val['packID']+'">'+ val['code'] + ' - ' + val['name'] + ' - ' + val['packName'] +'</option>';
        });

        makeselect += '</select>';

        table.row.add([
                    counter,
                    makeselect,
                    '<input type="number" class="form-control" name="price[]" readonly required><input type="hidden" class="form-control packaging" name="packaging[]">',
                    '<input type="number" class="form-control" name="qty[]" required>',
                    '<input type="number" class="form-control" name="disc[]">',
                    '<input type="number" class="form-control" name="subtotal[]">',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
                  initailizeSelect2();
        counter++;
      }
    });

    $('#brand_name').on('select2:select', function (e) {
      table.clear().draw();

      $.ajax({
        url: '{{ route('superuser.penjualan.sales_order_ppn.get_product_pack') }}',
        data: {id:$(this).val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_data = json.data;
          }
        }
      });
    });

    function initailizeSelect2(){
      $(".js-ajax").select2();

      $('.js-ajax').on('select2:select', function (e) {
        var price = $(this).find(':selected').data('price');
        var pack = $(this).find(':selected').data('packid');
        // alert(pack);


        $(this).parents('tr').find('input[name="price[]"]').val(price);
        $(this).parents('tr').find('input[name="packaging[]"]').val(pack);
      });

    };

    $('#datatables tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      
      table.row( $(this).parents('tr') ).remove().draw();
    });

    $('#datatables tbody').on( 'keyup', 'input[name="qty[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var disc_usd = $(this).parents('tr').find('input[name="disc[]"]').val();
      var kurs = $('#idr_rate').val();
      var total = ((price - disc_usd) * $(this).val()) * kurs;


      $(this).parents('tr').find('input[name="subtotal[]"]').val(total);
      $(this).parents('tr').find('input[name="subtotal[]"]').change();

    });

    $('#datatables tbody').on( 'keyup', 'input[name="disc[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var qty = $(this).parents('tr').find('input[name="qty[]"]').val();
      var kurs = $('#idr_rate').val();
      var total = ((price - $(this).val()) * qty) * kurs;

      $(this).parents('tr').find('input[name="subtotal[]"]').val(total);
      $(this).parents('tr').find('input[name="subtotal[]"]').change();

    })

    $('#datatables tbody').on( 'change', 'input[name="subtotal[]"]', function (e) {
      var subtotal = 0;
      $('input[name="subtotal[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      
      $('#sub_total_item').val(formatRupiah(subtotal));
    })

    $('#disc_agen_percent').on('keyup', function(e) {
      if($(this).val() != ''){
        let sub_total_item = $('input[name="sub_total_item"]').val();

        sub_total_item = parseFloat(sub_total_item.split('.').join(''));

        let amount = sub_total_item * $(this).val() / 100;

        $('input[name="disc_agen_idr"]').val(formatRupiah(amount));
      }else{
        $('input[name="disc_agen_idr').val(0);
      }
      // subtotal();
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
          // subtotal();
    });

    $('#ppn_percent').on('keyup', function(e) {
      if($(this).val() != ''){
        
        let sub_total_item = $('input[name="sub_total_item"]').val();
        let disc_percent = $('input[name="disc_agen_idr"]').val();
        let disc_kemasan = $('input[name="disc_kemasan_idr"]').val();

        sub_total_item = parseFloat(sub_total_item.split('.').join(''));
        disc_percent = parseFloat(disc_percent.split('.').join(''));
        disc_kemasan = parseFloat(disc_kemasan.split('.').join(''));

        let subAfterDiscPercent = sub_total_item - disc_percent - disc_kemasan;

        var amount = subAfterDiscPercent * $(this).val() / 100;
        $('#ppn_idr').val(formatRupiah(amount));
      }else{
        $('#ppn_idr').val(0);
      }
      // subtotal();
    })

    // function subtotal(){
    //   let sub_total = $('#sub_total_item').val();
    //   let disc_agen = $('#disc_agen_idr').val();
    //   let dics_kemasan = $('#disc_kemasan_idr').val();

    //   sub_total = parseFloat(sub_total.split('.').join(''));
    //   disc_agen = parseFloat(disc_agen.split('.').join(''));
    //   dics_kemasan = parseFloat(dics_kemasan.split('.').join(''));

    //   if(isNaN(sub_total)){
    //     sub_total = 0;
    //   }

    //   if(isNaN(disc_agen)){
    //     disc_agen = 0;
    //   }

    //   if(isNaN(dics_kemasan)){
    //     dics_kemasan = 0;
    //   }

    //   let sub_total_before = sub_total - disc_agen - dics_kemasan;

    //   $('#subtotal_2').val(formatRupiah(sub_total_before));
    // };

    $('#btn_call').on('click', function() {
      let subtotal_item = $('#sub_total_item').val();
      let disc_agen_idr = $('#disc_agen_idr').val();
      let disc_kemasan_idr = $('#disc_kemasan_idr').val();
      let dis_tambahan_idr = $('#disc_tambahan_idr').val();
      let voucher_idr = $('#voucher_idr').val();
      let tax = $('#ppn_idr').val();
      let ongkir = $('#delivery_cost_idr').val();

      subtotal_item       = parseFloat(subtotal_item.split('.').join(''));
      disc_agen_idr       = parseFloat(disc_agen_idr.split('.').join(''));
      disc_kemasan_idr    = parseFloat(disc_kemasan_idr.split('.').join(''));
      dis_tambahan_idr    = parseFloat(dis_tambahan_idr);
      voucher_idr         = parseFloat(voucher_idr);
      tax                 = parseFloat(tax.split('.').join(''));
      ongkir              = parseFloat(ongkir);

      if(isNaN(disc_agen_idr)){
        disc_agen_idr = 0;
      }

      if(isNaN(disc_kemasan_idr)){
        disc_kemasan_idr = 0;
      }

      if(isNaN(dis_tambahan_idr)){
        dis_tambahan_idr = 0;
      }

      if(isNaN(voucher_idr)){
        voucher_idr = 0;
      }

      if(isNaN(tax)){
        tax = 0;
      }

      if(isNaN(ongkir)){
        ongkir = 0;
      }

      let grand_total_idr = subtotal_item - disc_agen_idr - disc_kemasan_idr - voucher_idr + tax + ongkir;

      $('#grand_total_idr').val(formatRupiah(grand_total_idr));
    })
  })
</script>
@endpush