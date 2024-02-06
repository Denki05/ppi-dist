@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Edit Sales Order</span>
</nav>
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if($step == 1)
  <form id="frmEditSOMaster" method="post" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="id" value="{{$result->id}}">
  <input type="hidden" name="step" value="{{$step}}">
    <div class="row">
      <div class="col-4">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <span class="form-label"><b>Sales Senior</b> <span class="text-danger">*</span></span>
              <div class="col-10">
                <select class="form-control js-select2" name="sales_senior_id">
                  <option value="">Pilih Sales Senior</option>
                  @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $sales_value)
                  <option value="{{ $sales_value }}" {{ ($result->sales_senior_id == $sales_value) ? 'selected' : '' }}>{{ $sales_senior }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <span class="form-label"><b>Sales</b> <span class="text-danger">*</span></span>
              <div class="col-10">
                <select class="form-control js-select2" name="sales_id">
                  <option value="">Pilih Sales</option>
                  @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                  <option value="{{ $sales_value }}" {{ ($result->sales_id == $sales_value) ? 'selected' : '' }}>{{ $sales }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-8">
        <div class="block">
          <div class="block-content">
            <div class="row">
              <div class="col">
                <div class="form-group row">
                  <span class="form-label"><b>Type Transaksi </b> <span class="text-danger">*</span></span>
                  <div class="col-10">
                    <select class="form-control js-select2" name="type_transaction">
                      <option value="">Pilih Transaksi Type </option>
                      @foreach(App\Entities\Penjualan\SalesOrder::TYPE_TRANSACTION as $row => $value)
                      <option value="{{$value}}" {{ ($result->type_transaction == $value) ? 'selected' : '' }}>{{ $value }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="form-group row">
                  <span class="form-label"><b>Indent</b> <span class="text-danger">*</span></span>
                  <div class="col-10">
                    <select class="form-control js-select2" name="so_indent">
                      <option value="">Pilih status indent</option>
                      @foreach(App\Entities\Penjualan\SalesOrder::INDENT as $row => $value)
                      <option value="{{$value}}" {{ ($result->type_transaction == $value) ? 'selected' : '' }}>{{ $row }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="form-group row">
                  <span class="form-label"><b>Brand</b> <span class="text-danger">*</span></span>
                  <div class="col-10">
                    <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name" data-placeholder="Plih Brand/Merek">
                    @foreach($brand as $value)
                    <option value="{{$value->brand_name}}" {{ ($result->brand_name == $value->brand_name) ? 'selected' : '' }}>{{ $value->brand_name }}</option>
                    @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div class="form-group row">
                  <span class="form-label"><b>Disc (%)</b></span>
                  <div class="col">
                    <input class="form-control" type="number" name="catatan" value="{{ $result->catatan }}">
                  </div>
                </div>
              </div>
              <div class="col-2">
                <div class="form-group row">
                  <span class="form-label"><b>Note</b></span>
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">
                    <i class="fa fa-plus"></i> Note
                  </button>
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
                    <th class="text-center">#</th>
                    <th class="text-center">Product</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Disc</th>
                    <th class="text-center">Free</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($result->so_detail as $detail)
                    <tr id="list-body">
                      <td>{{ $loop->iteration }}</td>
                      <td>
                          <input type="hidden" name="sku[]" value="{{ $detail->product_packaging_id }}">
                          <input type="hidden" name="packaging[]" value="{{ $detail->packaging_id }}">
                          <input type="hidden" name="free_product[]" value="{{ $detail->free_product }}">
                          <span class="name">{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }} - {{ $detail->product_pack->packaging->pack_name }}</span>
                      </td>
                      <td><span class="name">{{ $detail->product_pack->price }}</span></td>
                      <td><input type="number" class="form-control" name="qty[]" required value="{{ $detail->qty }}" step="any"></td>
                      <td><input type="text" class="form-control" name="disc[]" value="{{ $detail->disc_usd }}"></td>
                      <td>
                        @if($detail->free_product == 0)
                          <span>NO</span>
                        @else
                          <span>YES</span>
                        @endif
                      </td>
                      <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="row pt-30 mb-15">
            <div class="col-md-6">
              <a href="{{route('superuser.penjualan.sales_order.index_' . strtolower($step_txt))}}">
                <button type="button" class="btn bg-gd-cherry border-0 text-white">
                  <i class="fa fa-arrow-left mr-10"></i> Back
                </button>
              </a>
            </div>
            <div class="col-md-6 text-right">
              <button class="btn btn-primary btn-md" type="submit"><i class="fa fa-save"></i> Save</button>
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
            <textarea class="form-control" name="note"  rows="4">{{ $result->note }}</textarea>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
  </form>
@endif

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2();

    $(".js-select2-brand").select2({
      ajax: {
        url: '{{ route('superuser.penjualan.sales_order.get_brand') }}',
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

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": true, width: "5%"},
          {name: 'sku', orderable: false, width: "35%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'qty', orderable: false, searcable: false, width: "10%"},
          {name: 'disc', orderable: false, searcable: false, width: "10%"},
          {name: 'free', orderable: false, searcable: false, width: "5%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'asc']]
    })

    var counter = {{ count($result->so_detail) + 1 }};

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
                    '<input type="text" class="form-control" name="qty[]" required step="any">',
                    '<input type="number" class="form-control" name="disc[]">',
                    '<input type="checkbox" class="form-check-input input-gift" id="gift" name="gift"><input class="form-control input-free" type="hidden" id="free_product" value="0" name="free_product[]">',
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

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      if(typeof $('input[name="id[]"]').val() == 'undefined') {
        $('#submit-table').prop('disabled', true);
      }
    });

    $('#datatable tbody').on( 'click', '.input-gift', function (e) {
      if($(this).is(':checked')){
        $(this).parents('tr').find('.input-free').val(1);
      }else{
        $(this).parents('tr').find('.input-free').val(0);
      }
    });

    $('#brand_name').on('select2:select', function (e) {
      $.ajax({
        url: '{{ route('superuser.penjualan.sales_order.get_product_pack') }}',
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

    $(document).on('submit','#frmEditSOMaster',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah sales order ini ? ?")){
        let _form = $('#frmEditSOMaster');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.update')}}',
          method : "POST",
          data : $('#frmEditSOMaster').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmEditSOMaster').find('button[type="submit"]').html('Loading...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire(
                'Success!',
                resp.Message,
                'success'
              ).then((result) => {
                  location.reload();
              })
              
            }
          },
          complete : function(){
            $('#frmEditSOMaster').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
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