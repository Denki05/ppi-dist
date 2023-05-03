@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sale_return.index') }}">Sale Return</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sale_return.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Create Sale Return</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="delivery_order">Delivery Order <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control js-select2-do" id="delivery_order" name="delivery_order" data-placeholder="Type DO/SO/AWB/STORE">
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_reparation">Warehouse Reparation <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="warehouse_reparation" name="warehouse_reparation" data-placeholder="Select Warehouse">
            <option></option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="return_date">Return Date</label>
        <div class="col-md-4">
          <input type="date" class="form-control" id="return_date" name="return_date">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="javascript:history.back()">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table" disabled>
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
            <th class="text-center">Counter</th>
            <th class="text-center">Select SKU</th>
            <th class="text-center">Product</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Description</th>
            <th class="text-center">Ref</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
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
  $(document).ready(function () {
    $('.js-select2').select2()

    $(".js-select2-do").select2({
      ajax: {
        url: '{{ route('superuser.penjualan.sale_return.search_do') }}',
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

    var product_data = new Object();

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
  
    var counter = 1;
  
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


    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      if(typeof $('input[name="id[]"]').val() == 'undefined') {
        $('#submit-table').prop('disabled', true);
      }
    });

    $('#delivery_order').on('select2:select', function (e) {
      table.clear().draw();

      $.ajax({
        url: '{{ route('superuser.penjualan.sale_return.get_product') }}',
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

  })
</script>
@endpush
