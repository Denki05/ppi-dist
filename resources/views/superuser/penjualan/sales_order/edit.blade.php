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
<div class="block">
  <div class="block-content">
    <form id="frmEditSOMaster" method="post" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="id" value="{{$result->id}}">
      <input type="hidden" name="step" value="{{$step}}">
      <div class="row">
        <div class="col-4">
          <div class="card">
            <div class="card-body">
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Sales Senior<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_senior_id">
                        <option value="">Pilih Sales Senior</option>
                        @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $sales_value)
                          <option value="{{ $sales_value }}" {{ ($result->sales_senior_id == $sales_value) ? 'selected' : '' }}>{{ $sales_senior }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Sales<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_id">
                        <option value="">Pilih Sales</option>
                        @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                          <option value="{{ $sales_value }}" {{ ($result->sales_id == $sales_value) ? 'selected' : '' }}>{{ $sales }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
            </div>
          </div>
        </div>

        <div class="col-8">
          <div class="card">
            <div class="card-body">
              <div class="row">
              <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Brand</label>
                      <div class="col-md-6">
                        <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name" data-placeholder="Plih Brand/Merek">
                        </select>
                      </div>
                    </div>
                    @endif
                </div>
                {{--<div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
                      <div class="col-md-6">
                        @if ($result->customer->has_tempo == 0)
                          <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="CASH" readonly>
                          <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="CASH">
                        @elseif($result->customer->has_tempo == 1)
                          <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="TEMPO" readonly>
                          <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="TEMPO">
                        @endif
                      </div>
                    </div>
                    @endif
                </div>--}}
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Note</label>
                      <div class="col-8">
                        <textarea class="form-control" name="note" rows="1">{{ $result->note }}</textarea>
                      </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Kurs</label>
                      <div class="col-md-6">
                        <input type="text" name="idr_rate" class="form-control" value="{{ $result->idr_rate }}">
                      </div>
                    </div>
                    @endif
                </div>
                
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <!-- table product list -->
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
                      <span class="name">{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }} - {{ $detail->product_pack->kemasan()->pack_name }}</span>
                  </td>
                  <td><span class="name">{{ $detail->product_pack->price }}</span></td>
                  <td><input type="number" class="form-control" name="qty[]" required value="{{ $detail->qty }}"></td>
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

      <br>

      <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.penjualan.sales_order.index_' . strtolower($step_txt))}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
          <button class="btn btn-primary btn-md" type="submit"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
    </form>
    
  </div>
</div>
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
        'order' : [[0,'desc']]
    })

    var counter = {{ count($result->so_detail) + 1 }};

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#brand_name').val()) {
        $('#submit-table').prop('disabled', false);
        
        makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';
        
        $.map( product_data, function( val, i ) {
          makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-packname="'+ val['packName'] +'" data-price="'+ val['price'] +'" data-packid="'+ val['packID']+'">'+ val['code'] + ' - ' + val['name'] + ' - ' + val['packName'] +'</option>';
        });

        makeselect += '</select>';

        table.row.add([
                    counter,
                    makeselect,
                    '<span class="price"></span><input type="hidden" class="form-control packaging" name="packaging[]">',
                    '<input type="text" class="form-control" name="qty[]" required>',
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
  })
</script>
@endpush