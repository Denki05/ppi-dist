@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sale_return.index') }}">Nota Kredit</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<form class="ajax" data-action="{{ route('superuser.penjualan.sale_return.update', $sale_return->id) }}" data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PATCH">
    <input type="hidden" name="ids_delete" value="">
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Retur</h3>
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
          <input type="hidden" id="delivery_order" value="{{ $sale_return->invoice->id }}">
        <div class="form-control-plaintext">{{ $sale_return->invoice->do_code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="warehouse_reparation">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->warehouse->name ?? '-' }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="return_date">Return Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->retur_date ? date('d/m/Y', strtotime($sale_return->retur_date)) : '-' }}</div>
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
        <a href="{{ route('superuser.penjualan.sale_return.index') }}">
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
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">Counter</th>
          <th class="text-center">Product</th> <!-- gabungan code - name -->
          <th class="text-center">Packaging</th> <!-- kemasan -->
          <th class="text-center">Quantity</th>
          <th class="text-center">Note</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sale_return->sale_return_details as $detail)
            @php
                $max = 0;
            @endphp
           @foreach ( $sale_return->invoice->do_detail as $item)
                @if($item->product_packaging_id == $detail->product_packaging_id)
                    @php
                        $max = $item->qty;
                    @endphp
                @endif
           @endforeach
          <tr id="list-body">
            <td>{{ $loop->iteration }}</td>
            <td>
              <input type="hidden" name="sku[]" value="{{ $detail->product_packaging_id }}">
              <span class="name">{{ $detail->product->code }} - {{ $detail->product->name }}</span>
            </td>
            <td><span class="name">{{ $detail->product->packaging->pack_name }}</span></td>
            <td><input type="number" class="form-control text-center" name="quantity[]" min="1" max="{{ $max }}" required value="{{ $detail->qty }}"></td>
            <td><input type="text" class="form-control" name="description[]" value="{{ $detail->note }}"></td>
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
          {name: 'product', orderable: false, width: "Auto"},
          {name: 'packaging', orderable: false, width: "Auto"},
          {name: 'quantity', orderable: false, width: "Auto"},
          {name: 'note', orderable: false, width: "Auto"},
          {name: 'action', orderable: false, width: "Auto"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = {{ count($sale_return->sale_return_details) + 1 }};

    $.ajax({
        url: '{{ route('superuser.penjualan.sale_return.get_product') }}',
        data: {id:$('#delivery_order').val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_data = json.data;

            $.each( product_data, function( key, value ) {
                var makeselect;
                $.map(product_data, function(val, i) {
                  makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-sku="'+ val['sku'] +'" data-quantity="'+ val['quantity'] + '" data-kemasan="'+ val['kemasan'] +'">'+ val['sku'] +' - '+ val['name'] +'</option>';
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

          makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';

          $.map(product_data, function(val, i) {
            makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-sku="'+ val['sku'] +'" data-quantity="'+ val['quantity'] + '" data-kemasan="'+ val['kemasan'] +'">'+ val['sku'] +' - '+ val['name'] +'</option>';
          });

          makeselect += '</select>';

          table.row.add([
            counter,
            makeselect, // Select product
            '<span class="packaging"></span>', // Packaging info (sku)
            '<input type="number" class="form-control text-center" name="quantity[]" min="0.01" step="0.01" required>',
            '<input type="text" class="form-control text-center" name="description[]" placeholder="Note">',
            '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>',
          ]).draw(false);

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
        var sku = $(this).find(':selected').data('sku');
        var quantity = $(this).find(':selected').data('quantity');
        var kemasan = $(this).find(':selected').data('kemasan');

        $(this).parents('tr').find('.packaging').text(kemasan);
        $(this).parents('tr').find('input[name="quantity[]"]').prop('max', quantity);
        $(this).parents('tr').find('input[name="quantity[]"]').prop('placeholder', quantity);
      });

  };
</script>
@endpush
