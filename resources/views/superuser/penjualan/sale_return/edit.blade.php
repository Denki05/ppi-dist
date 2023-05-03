@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.sale.sale_return.index') }}">Sale Return</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<form class="ajax" data-action="{{ route('superuser.sale.sale_return.update', $sale_return->id) }}" data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PATCH">
    <input type="hidden" name="ids_delete" value="">
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Sale Return</h3>
  </div>

  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        {{-- <div class="form-control-plaintext">{{ $sale_return->code }}</div> --}}
        <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $sale_return->code }}">
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="delivery_order" >Delivery Order</label>
      <div class="col-md-7">
          <input type="hidden" id="delivery_order" value="{{ $sale_return->delivery_order->id }}">
        <div class="form-control-plaintext">{{ $sale_return->delivery_order->code }} / <a href="{{ route('superuser.sale.sales_order.show', $sale_return->delivery_order->sales_order_id) }}" target="_blank"> {{ $sale_return->delivery_order->sales_order->code }} </a></div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="warehouse_reparation">Warehouse Reparation</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->warehouse->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="return_date">Return Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->return_date ? date('d/m/Y', strtotime($sale_return->return_date)) : '-' }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="status">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->status() }}</div>
      </div>
    </div>
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.sale.sale_return.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      <div class="col-md-6 text-right">
        <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
          Submit <i class="fa fa-arrow-right ml-10"></i>
        </button>
      </div>
    </div>
  </div>
</div>
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
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">SKU</th>
          <th class="text-center">Product</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Description</th>
          <th class="text-center">Ref</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sale_return->sale_return_details as $detail)
            @php
                $max = 0;
            @endphp
           @foreach ( $sale_return->delivery_order->sales_order->sales_order_details as $item)
                @if($item->product_id == $detail->product_id)
                    @php
                        $max = $item->quantity;
                    @endphp
                @endif
           @endforeach
          <tr id="list-body">
            <td>{{ $loop->iteration }}</td>
            <td><input type="hidden" name="hpp[]" value="{{ $detail->hpp }}">
                <input type="hidden" name="price[]" value="{{ $detail->price }}">
                <input type="hidden" name="sku[]" value="{{ $detail->product_id }}">
                {{--  <select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" name="sku[]" data-placeholder="Select SKU" style="width:100%" required>
                    <option></option>
                </select>  --}}
                <span class="name">{{ $detail->product->code }}</span>
            </td>
            <td><span class="name">{{ $detail->product->name }}</span></td>
            <td><input type="number" class="form-control" name="quantity[]" min="1" max="{{ $max }}" required value="{{ $detail->quantity }}"></td>
            <td><input type="text" class="form-control" name="description[]" value="{{ $detail->description }}"></td>
            <td><span class="ref">Selling Price : {{ $detail->price }}</span></td>
            <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
</form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  var product_data = new Object();
  $(document).ready(function () {
    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'sku', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false},
          {name: 'quantity', orderable: false, searcable: false, width: "5%"},
          {name: 'description', orderable: false, searcable: false},
          {name: 'ref', orderable: false, searcable: false},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = {{ count($sale_return->sale_return_details) + 1 }};

    $.ajax({
        url: '{{ route('superuser.sale.sale_return.get_product') }}',
        data: {id:$('#delivery_order').val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_data = json.data;

            $.each( product_data, function( key, value ) {
                var makeselect;
                $.map( product_data, function( val, i ) {
                    makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-hpp="'+ val['hpp'] +'" data-price="'+ val['price'] +'" data-quantity="'+ val['quantity'] +'">'+ val['sku'] +'</option>';
                });


                $('.js-ajax').append(makeselect);
                initailizeSelect2();

                $('#list-body').find('tr').each(function() {
                    var maxQty = 0;

                    var elem = $(this);
                    $.map( product_data, function( val, i ) {
                        var id = parseInt(elem.find('.sku_select').val());

                        if(parseInt(val['id']) == id){
                            maxQty = val['quantity'];
                        }
                    });

                    $(this).find('.js-ajax').val($(this).find('.sku_select').val()).trigger('change');
                    $(this).find('input[name="quantity[]"]').prop('max', maxQty);
                });
            });
          }
        }
      });

      $('a.row-add').on( 'click', function (e) {
        e.preventDefault();
        if($('#delivery_order').val()) {
          $('#submit-table').prop('disabled', false);

          makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required><option></option>';

          $.map( product_data, function( val, i ) {
            makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-hpp="'+ val['hpp'] +'" data-price="'+ val['price'] +'" data-quantity="'+ val['quantity'] +'">'+ val['sku'] +'</option>';
          });

          makeselect += '</select>';

          table.row.add([
                      counter,
                      makeselect,
                      '<span class="name"></span>',
                      '<input type="number" class="form-control" name="quantity[]" min="1" required><input type="hidden" class="form-control" name="hpp[]"><input type="hidden" class="form-control" name="price[]">',
                      '<input type="text" class="form-control" name="description[]">',
                      '<span class="ref"></span>',
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );

                    initailizeSelect2();
          counter++;
        }

      });

      $('#datatable tbody').on( 'click', '.row-delete', function (e) {
        e.preventDefault();
        table.row( $(this).parents('tr') ).remove().draw();

        if(typeof $('input[name="id[]"]').val() == 'undefined') {
            if($('#datatable').dataTable().fnGetData().length < 1){
                $('#submit-table').prop('disabled', true);
            }
        }
      });
  });

  function initailizeSelect2(){
    $(".js-ajax").select2();

    $('.js-ajax').on('select2:select', function (e) {
      var name = $(this).find(':selected').data('name');
      $(this).parents('tr').find('.name').text(name);

      var hpp = $(this).find(':selected').data('hpp');
      $(this).parents('tr').find('input[name="hpp[]"]').val(hpp);

      var price = $(this).find(':selected').data('price');
      $(this).parents('tr').find('input[name="price[]"]').val(price);

      $(this).parents('tr').find('.ref').text('Selling price : '+price);

      var quantity = $(this).find(':selected').data('quantity');
      $(this).parents('tr').find('input[name="quantity[]"]').prop('max', quantity);
      $(this).parents('tr').find('input[name="quantity[]"]').prop('placeholder', quantity);
    });

  };
</script>
@endpush
