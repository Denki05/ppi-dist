@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_awal') }}">Sales Order PPN {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Update SO PPN</span>
</nav>

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order_ppn.update_awal_ppn', $result->id) }}" data-type="POST" enctype="multipart/form-data">
@csrf
<input type="hidden" name="step" value="{{$step}}">
  <div class="row">
    <div class="col-6">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">#Detail Nota #{{$result->so_code}}</h3>
        </div>
        <div class="block-content">
          

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="so_date">Sales Senior</label>
              <select class="form-control js-select2" name="sales_senior_id">
                <option value="">Pilih Sales Senior</option>
                @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $sales_value)
                  <option value="{{ $sales_value }}" {{ ($result->sales_senior_id == $sales_value) ? 'selected' : '' }}>{{ $sales_senior }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Sales</label>
              <select class="form-control js-select2" name="sales_id">
                <option value="">Pilih Sales</option>
                @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                  <option value="{{ $sales_value }}" {{ ($result->sales_id == $sales_value) ? 'selected' : '' }}>{{ $sales }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="type_transaction">Type Transaksi</label>
              <select class="form-control js-select2" name="type_transaction">
                <option value="">Pilih Transaksi Type </option>
                @foreach(App\Entities\Penjualan\SalesOrder::TYPE_TRANSACTION as $row => $value)
                <option value="{{$value}}" {{ ($result->type_transaction == $value) ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="note">Brand</label>
                <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name" data-placeholder="Plih Brand/Merek">
                    @foreach($brand as $value)
                    <option value="{{ $value->brand_name }}" {{$result->brand_name == $value->brand_name  ? 'selected' : ''}}>{{ $value->brand_name}}</option>
                    @endforeach
                </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="customer_area">No. Dokumen <span class="text-danger">*</span></label>
              <input type="text" name="no_document" id="no_document" value="{{ $result->no_ducument_ppn	 }}"  class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="note">Note</label>
              <br>
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">
                <i class="fa fa-plus"></i> Note
              </button>
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
              <h3 class="block-title">#Customer</h3>
            </div>
            <div class="block-content">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="type_transaction">Customer</label>
                  <select class="form-control js-select2" name="customer_name" id="customer_name">
                    <option value="">Select Customer</option>
                    @foreach($other_address as $row)
                      <option value="{{ $row->id }}" {{ ($result->customer_other_address_id == $row->id) ? 'selected' : '' }}>{{ $row->name }} {{ $row->text_kota }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label for="note">Alamat Kirim</label>
                  <!-- <textarea class="form-control" rows="1" readonly></textarea> -->
                  <input type="text" class="form-control" name="customer_address" id="customer_address" value="{{$result->member->address}}" readonly>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="customer_city">Kota</label>
                  <input type="text" name="customer_city" id="customer_city" class="form-control" value="{{$result->member->text_kota}}" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label for="customer_area">Provinsi</label>
                  <input type="text" name="customer_area" id="customer_area" class="form-control" value="{{$result->member->text_provinsi}}" readonly>
                </div>
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
          <div class="block-header">
            <h2 class="block-title">#Add Product</h2>
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
                  <th class="text-center">Price</th>
                  <th class="text-center">Qty</th>
                  <th class="text-center">Disc</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($result->so_detail as $detail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                          <input type="hidden" name="sku[]" value="{{ $detail->product_packaging_id }}">
                          <input type="hidden" name="packaging[]" value="{{ $detail->packaging_id }}">
                          <span class="name">{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }} - {{ $detail->product_pack->packaging->pack_name }}</span>
                      </td>
                      <td>
                        <span class="name">{{ $detail->product_pack->price }}</span>
                        <input type="hidden" name="price[]" value="{{ $detail->product_pack->price }}">
                      </td>
                      <td><input type="number" class="form-control" name="qty[]" required value="{{ $detail->qty }}" step="any"></td>
                      <td><input type="text" class="form-control" name="disc[]" value="{{ $detail->disc_usd }}"></td>
                      <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
                    </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <br>
        </div>
        <div class="row pt-30 mb-15">
          <div class="col-md-6">
            <a href="{{route('superuser.penjualan.sales_order_ppn.index_ppn_awal')}}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-6 text-right">
            <button class="btn btn-primary btn-md" type="submit"><i class="fa fa-save"></i> Simpan</button>
            
            <!-- <button class="btn btn-primary btn-md btn-simpan-dan-ajukan-ke-lanjutan" type="button"><i class="fa fa-save"></i> Simpan dan ajukan ke Lanjutan</button> -->
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">#Add Note</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" name="note" id="editor" rows="4" col="10">{{ $result->note }}</textarea>
            <br>
            <a class="btn btn-info" id="test" href="javascript:void(0);" title="">click</a>
          </div>
          <div class="modal-footer">
          
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
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#brand_name').val()) {
        $('#submit-table').prop('disabled', false);
        
        makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';
        
        $.map( product_data, function( val, i ) {
          makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-packname="'+ val['packName'] +'" data-price="'+ val['price'] +'" data-packid="'+ val['packID']+'">'+ val['code'] + ' - ' + val['name'] + ' - ' + val['packName'] + ' - '+ val['warehouseName'] +'</option>';
        });

        makeselect += '</select>';

        table.row.add([
                    counter,
                    makeselect,
                    '<span class="price"></span><input type="hidden" class="form-control packaging" name="packaging[]">',
                    '<input type="number" class="form-control" name="qty[]" required>',
                    '<input type="number" class="form-control" name="disc[]">',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
                  
                  initailizeSelect2();
        counter++;
      }
      
    });

    function initailizeSelect2(){
      $(".js-ajax").select2();

      $('.js-ajax').on('select2:select', function (e) {
        var price = $(this).find(':selected').data('price');
        $(this).parents('tr').find('.price').text('$'+price);

        var pack = $(this).find(':selected').data('packid');
        $(this).parents('tr').find('input[name="packaging[]"]').val(pack);
      });

    };

    $('#datatables tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      if(typeof $('input[name="id[]"]').val() == 'undefined') {
        $('#submit-table').prop('disabled', true);
      }
    });

    $('#datatables tbody').on( 'click', '.input-gift', function (e) {
      if($(this).is(':checked')){
        $(this).parents('tr').find('.input-free').val(1);
      }else{
        $(this).parents('tr').find('.input-free').val(0);
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

    $("#test").on("click",function(e){
      e.preventDefault();
      addListItem();
    });

    function addListItem() {
      var text = document.getElementById('editor').value;
      var listNumberRegex = /^[0-9]+(?=\.)/gm;
      var existingNums = [];
      var num;
     
      while ((num = listNumberRegex.exec(text)) !== null) {
        existingNums.push(num);
      }
      
      
      existingNums.sort();

      
    
      var addListItemNum;
      if (existingNums.length > 0) {
       
        addListItemNum = parseInt(existingNums[existingNums.length - 1], 10) + 1;
      } else {
      
        addListItemNum = 1;
      } 

      var exp = '\n' + addListItemNum + '.\xa0';
      text = text.concat(exp);
      document.getElementById('editor').value = text;
    }
  })
</script>
@endpush